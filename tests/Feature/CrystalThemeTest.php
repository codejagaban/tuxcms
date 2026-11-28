<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Setting;
use App\Models\User;
use App\Services\Site\SiteBuilder;
use App\Support\Preview;
use Database\Seeders\Clients\Crystal\CrystalContentSeeder;
use Database\Seeders\Clients\Crystal\MoveOverridesIntoPageCopy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class CrystalThemeTest extends TestCase
{
    use RefreshDatabase;

    private string $output;

    protected function setUp(): void
    {
        parent::setUp();

        $this->output = storage_path('framework/testing/crystal-'.uniqid());
        File::ensureDirectoryExists($this->output);
        config(['site.output_path' => $this->output, 'site.url' => 'https://crystal.test']);

        User::factory()->create();
        $this->seed(CrystalContentSeeder::class);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->output);
        parent::tearDown();
    }

    public function test_crystal_import_creates_the_expected_page_tree(): void
    {
        $this->assertSame(5, Page::count());
        $this->assertSame(['Home', 'About', 'Services', 'Salon', 'Contact'], Page::orderBy('order')->pluck('title')->all());
        $this->assertTrue(Page::where('is_homepage', true)->sole()->template === 'crystal');
        $this->assertTrue(Page::query()->where('template', '!=', 'crystal')->doesntExist());
    }

    public function test_crystal_theme_builds_static_pages_with_namespaced_assets_and_seo(): void
    {
        $result = app(SiteBuilder::class)->build();

        $this->assertSame(5, $result['pages']);
        $this->assertFileExists($this->output.'/index.html');
        $this->assertFileExists($this->output.'/salon/index.html');
        $this->assertFileExists($this->output.'/contact/index.html');

        $home = File::get($this->output.'/index.html');
        $this->assertStringContainsString('crystal', $home);
        $this->assertStringContainsString('cleaning you can trust', $home);
        $this->assertStringContainsString('/themes/crystal/css/style.css?v=3', $home);
        $this->assertStringContainsString('https://crystal.test/', $home);
        $this->assertStringContainsString('Crystal Services Limited', $home);

        $contact = File::get($this->output.'/contact/index.html');
        $this->assertStringContainsString('name="access_key"', $contact);
        $this->assertStringContainsString('id="contact_form"', $contact);
        $this->assertStringContainsString('56 Northfield Street', $contact);
    }

    public function test_crystal_theme_uses_edited_cms_content_in_the_generated_site(): void
    {
        $home = Page::where('is_homepage', true)->sole();
        $hero = $home->sections()->where('key', 'hero')->sole();
        $hero->update([
            'title' => 'A locally edited Crystal headline',
            'content' => 'This sentence came from the dashboard.',
        ]);

        app(SiteBuilder::class)->build();

        $html = File::get($this->output.'/index.html');
        $this->assertStringContainsString('A locally edited Crystal headline', preg_replace('/\s+/', ' ', strip_tags($html)));
        $this->assertStringContainsString('This sentence came from the dashboard.', $html);
    }

    public function test_copy_written_into_the_templates_is_edited_through_the_page_copy(): void
    {
        $copy = Page::where('slug', 'services')->sole()->sections()->where('key', 'copy')->sole();
        $copy->update(['data' => [
            'now_offering_professional_hairdressing' => 'Edited written-in copy',
            'deep_cleaning_service_photo' => '/storage/edited-service.jpg',
            'deep_cleaning_service_photo_alt' => 'Edited service image',
        ]]);

        app(SiteBuilder::class)->build();

        $html = File::get($this->output.'/services/index.html');
        $this->assertStringContainsString('Edited written-in copy', $html);
        $this->assertStringNotContainsString('Now offering professional hairdressing', $html);
        $this->assertStringContainsString('src="/storage/edited-service.jpg"', $html);
        $this->assertStringContainsString('alt="Edited service image"', $html);
        // Untouched pieces keep the wording written into the template.
        $this->assertStringContainsString('Salon &amp; Hairdressing', $html);
    }

    public function test_stored_overrides_move_into_the_page_copy(): void
    {
        // As an editor left them before the copy had fields of its own, by
        // position on the published page. Position 999 holds no piece.
        $services = Page::where('slug', 'services')->sole();
        $services->update(['content' => json_encode(['crystal_overrides' => [
            'text' => [21 => 'Moved from an override', 999 => 'Nowhere to go'],
            'images' => [4 => ['url' => '/storage/moved.jpg', 'alt' => 'Moved image']],
        ]])]);

        $this->seed(MoveOverridesIntoPageCopy::class);
        $this->seed(MoveOverridesIntoPageCopy::class); // Running it again changes nothing.

        $copy = $services->sections()->where('key', 'copy')->sole();
        $this->assertSame([
            'now_offering_professional_hairdressing' => 'Moved from an override',
            'deep_cleaning_service_photo' => '/storage/moved.jpg',
            'deep_cleaning_service_photo_alt' => 'Moved image',
        ], $copy->data);
        $this->assertSame(['unmoved_overrides' => ['text' => [999 => 'Nowhere to go']]], json_decode($services->fresh()->content, true));

        app(SiteBuilder::class)->build();
        $html = File::get($this->output.'/services/index.html');
        $this->assertStringContainsString('Moved from an override', $html);
        $this->assertStringContainsString('src="/storage/moved.jpg"', $html);
        $this->assertStringNotContainsString('data-crystal-overrides', $html);
    }

    public function test_crystal_footer_uses_configured_contact_and_social_settings(): void
    {
        Setting::set('contact_email', 'hello@crystal.test', 'contact');
        Setting::set('contact_phone', '+44 1905 000 000', 'contact');
        Setting::set('contact_address', '10 Test Street, Worcester', 'contact');
        Setting::set('social_instagram', 'https://instagram.com/crystal-test', 'social');

        app(SiteBuilder::class)->build();

        $html = File::get($this->output.'/index.html');
        $this->assertStringContainsString('mailto:hello@crystal.test', $html);
        $this->assertStringContainsString('tel:+441905000000', $html);
        $this->assertStringContainsString('10 Test Street, Worcester', $html);
        $this->assertStringContainsString('https://instagram.com/crystal-test', $html);
        $this->assertStringNotContainsString('href="#" rel="noopener nofollow"', $html);
    }

    public function test_crystal_footer_hides_social_directory_when_no_profiles_are_configured(): void
    {
        app(SiteBuilder::class)->build();

        $html = File::get($this->output.'/index.html');
        $this->assertStringNotContainsString('crystal-footer-social-title', $html);
        $this->assertStringNotContainsString('href="#" rel="noopener nofollow"', $html);
    }

    public function test_crystal_footer_uses_editable_service_directories(): void
    {
        Setting::set('footer_intro', 'An editable footer introduction.', 'footer');
        Setting::set('footer_support_email', 'help@crystal.test', 'footer');
        Setting::set('footer_cleaning_links', json_encode([
            ['label' => 'Office care', 'url' => '/contact/?service=Office'],
            ['label' => 'Unsafe', 'url' => 'javascript:alert(1)'],
        ]), 'footer');
        Setting::set('footer_salon_links', json_encode([
            ['label' => 'Protective styling', 'url' => '/salon/'],
        ]), 'footer');

        app(SiteBuilder::class)->build();

        $html = File::get($this->output.'/index.html');
        $this->assertStringContainsString('An editable footer introduction.', $html);
        $this->assertStringContainsString('help@crystal.test', $html);
        $this->assertStringContainsString('Office care', $html);
        $this->assertStringContainsString('Protective styling', $html);
        $this->assertStringNotContainsString('javascript:alert(1)', $html);
        $this->assertStringNotContainsString('Commercial cleaning', $html);
    }

    public function test_crystal_footer_only_shows_valid_configured_legal_links(): void
    {
        Setting::set('privacy_url', '/privacy/', 'legal');
        Setting::set('terms_url', 'javascript:alert(1)', 'legal');

        app(SiteBuilder::class)->build();

        $html = File::get($this->output.'/index.html');
        $this->assertStringContainsString('aria-label="Legal"', $html);
        $this->assertStringContainsString('href="/privacy/"', $html);
        $this->assertStringNotContainsString('javascript:alert(1)', $html);
        $this->assertStringNotContainsString('>Terms</a>', $html);
    }

    public function test_copy_on_the_hand_built_pages_comes_from_the_section_data(): void
    {
        // Each of these used to be written into the template, so an edit to
        // the section data saved but never reached the published page.
        $home = Page::where('is_homepage', true)->sole();
        $testimonials = $home->sections()->where('key', 'testimonials')->sole();
        $data = $testimonials->data;
        $data['items'][0]['quote'] = 'Edited quote from the dashboard.';
        $data['items'][0]['author'] = 'Edited Author';
        $testimonials->update(['title' => 'Edited testimonials heading', 'data' => $data]);

        $services = Page::where('slug', 'services')->sole()->sections()->where('key', 'services')->sole();
        $data = $services->data;
        $data['items'][2]['link_label'] = 'Edited third button';
        $services->update(['data' => $data]);

        app(SiteBuilder::class)->build();

        $built = File::get($this->output.'/index.html');
        $this->assertStringContainsString('Edited testimonials heading', $built);
        $this->assertStringContainsString('Edited quote from the dashboard.', $built);
        $this->assertStringContainsString('Edited Author', $built);

        // Each card's button belongs to that card: only the third changed.
        $servicesPage = File::get($this->output.'/services/index.html');
        $this->assertSame(1, substr_count($servicesPage, 'Edited third button'));
        $this->assertSame(5, substr_count($servicesPage, 'Book this service'));
    }

    public function test_no_section_text_is_shown_on_a_hand_built_page_without_a_marker(): void
    {
        // Text a template writes in by hand, with an unused copy in the
        // section data, is what lost edits. Anything a page shows from its
        // data must be marked, so the editor saves it where it is printed.
        // Two kinds are left alone on purpose: a testimonial's author shares
        // its element with the role, and the contact form's service options
        // live inside a drop-down.
        $allowed = '/^(data\.items\.\d+\.author|data\.services\.\d+)$/';
        $ignored = '/(^|\.)(url|href|icon|alignment|columns|type|name|query|image|background_image|[a-z_]+_alt|alt)$/';
        $builder = app(SiteBuilder::class);
        $unmarked = [];

        foreach (Page::with('sections')->get() as $page) {
            $html = Preview::during(fn () => $builder->renderPage($page));
            preg_match_all('/data-tuxcms-field="([^"]+)"/', $html, $matches);
            $marked = array_flip($matches[1]);
            // The complete text of every element in the page's content, as the
            // editor reads it, so a piece inside a longer phrase is not mistaken
            // for this one. The menu and footer are the site's, not the page's.
            $texts = [];
            $dom = new \DOMDocument;
            @$dom->loadHTML('<?xml encoding="utf-8"?>'.$html);
            foreach ($dom->getElementsByTagName('main')->item(0)?->getElementsByTagName('*') ?? [] as $element) {
                $texts[trim(preg_replace('/\s+/u', ' ', $element->textContent))] = true;
            }

            foreach ($page->sections as $section) {
                $walk = function ($value, string $path) use (&$walk, &$unmarked, $page, $section, $marked, $texts, $allowed, $ignored) {
                    if (is_array($value)) {
                        foreach ($value as $key => $child) {
                            $walk($child, "{$path}.{$key}");
                        }

                        return;
                    }
                    $value = is_string($value) ? trim(preg_replace('/\s+/', ' ', $value)) : '';
                    if (mb_strlen($value) < 4 || preg_match($ignored, $path) || preg_match($allowed, $path)
                        || isset($marked["{$section->id}:{$path}"])) {
                        return;
                    }
                    if (isset($texts[$value])) {
                        $unmarked[] = "{$page->slug}: {$section->key}/{$path}";
                    }
                };
                $walk($section->title, 'title');
                $walk($section->content, 'content');
                $walk($section->data ?? [], 'data');
            }
        }

        $this->assertSame([], $unmarked, 'Shown on the page but not marked: '.implode(', ', $unmarked));
    }
}
