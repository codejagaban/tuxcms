<?php

namespace Tests\Feature;

use App\Models\JobPost;
use App\Models\Page;
use App\Services\Site\SiteBuilder;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Shopfront is the theme a fresh install starts with, so it has to render
 * every view on its own: no section type, careers page or error page may fall
 * through to another site's templates.
 */
class ShopfrontThemeTest extends TestCase
{
    private string $output;

    protected function setUp(): void
    {
        parent::setUp();

        $this->output = storage_path('framework/testing/site-' . uniqid());
        File::ensureDirectoryExists($this->output);
        config([
            'site.output_path' => $this->output,
            'site.url' => 'https://example.test',
            'site.theme' => 'shopfront',
        ]);

        $this->seedSiteSettings();
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->output);

        parent::tearDown();
    }

    private function contents(string $relative): string
    {
        $path = $this->output . '/' . $relative;
        $this->assertFileExists($path);

        return File::get($path);
    }

    private function homepageWithEverySection(): Page
    {
        $page = Page::create([
            'title' => 'Home', 'template' => 'landing', 'status' => 'published', 'is_homepage' => true,
        ]);

        $sections = [
            ['hero', 'Harrow and Finch', 'Made to measure.', ['subheading' => 'Joiners', 'primary_cta' => ['label' => 'Get a quote', 'url' => '/contact/']]],
            ['text', 'Our story', "First paragraph.\n\nSecond paragraph.", []],
            ['features', 'What we make', null, ['items' => [['icon' => 'box', 'title' => 'Staircases', 'description' => 'Oak and ash.']]]],
            ['stats', 'In numbers', null, ['items' => [['value' => '38', 'label' => 'Years trading']]]],
            ['testimonials', 'Kind words', null, ['items' => [['quote' => 'Flawless work.', 'author' => 'Ada Okafor', 'role' => 'Homeowner']]]],
            ['team', 'The workshop', null, ['members' => [['name' => 'Tom Reyes', 'role' => 'Foreman', 'bio' => 'Thirty years at the bench.']]]],
            ['faq', 'Questions', null, ['items' => [['question' => 'Do you fit?', 'answer' => 'Yes, always.']]]],
            ['cta', 'Ready when you are', 'Tell us what you need.', ['primary_cta' => ['label' => 'Start', 'url' => '/contact/']]],
            ['contact', 'Find the workshop', null, ['phone' => '01234 567890', 'address' => '4 Mill Lane']],
            ['contact_form', 'Write to us', null, ['fields' => [['name' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true]]]],
            ['map', 'On the map', null, ['lat' => 53.8, 'lng' => -1.55, 'label' => 'The workshop']],
        ];

        foreach ($sections as $order => [$type, $title, $content, $data]) {
            $page->sections()->create([
                'key' => $type, 'type' => $type, 'title' => $title, 'content' => $content,
                'data' => $data, 'order' => $order, 'is_visible' => true,
            ]);
        }

        return $page;
    }

    public function test_every_section_type_is_rendered_by_the_theme(): void
    {
        $this->homepageWithEverySection();

        app(SiteBuilder::class)->build();
        $html = $this->contents('index.html');

        $this->assertStringContainsString('/themes/shopfront/css/site', $html);

        foreach ([
            'Harrow and Finch', 'Joiners', 'Get a quote', 'First paragraph.', 'Second paragraph.',
            'Staircases', 'Years trading', 'Flawless work.', 'Tom Reyes', 'Do you fit?',
            'Ready when you are', '01234 567890', 'Write to us', 'On the map',
        ] as $text) {
            $this->assertStringContainsString($text, $html);
        }

        // Nothing from the unthemed stock views, which are styled with Tailwind.
        $this->assertStringNotContainsString('bg-blue-600', $html);
    }

    public function test_each_paragraph_of_a_text_section_is_its_own_element(): void
    {
        $this->homepageWithEverySection();

        app(SiteBuilder::class)->build();

        // The editor makes one element editable per paragraph.
        $this->assertMatchesRegularExpression(
            '#<p[^>]*>First paragraph\.</p>\s*<p[^>]*>Second paragraph\.</p>#',
            $this->contents('index.html')
        );
    }

    public function test_careers_and_the_error_page_use_the_theme(): void
    {
        $this->homepageWithEverySection();
        JobPost::create([
            'title' => 'Bench joiner', 'slug' => 'bench-joiner', 'summary' => 'Make fine things.',
            'status' => 'published', 'published_at' => now()->subDay(),
        ]);

        app(SiteBuilder::class)->build();

        foreach (['careers/index.html', 'careers/bench-joiner/index.html', '404.html'] as $file) {
            $html = $this->contents($file);
            $this->assertStringContainsString('/themes/shopfront/css/site', $html, $file);
            $this->assertStringNotContainsString('/themes/crystal/', $html, $file);
        }

        $this->assertStringContainsString('Bench joiner', $this->contents('careers/index.html'));
        $this->assertStringContainsString('name="form_token"', $this->contents('careers/bench-joiner/index.html'));
    }

    public function test_the_contact_form_goes_to_the_cms_inbox_unless_web3forms_is_set(): void
    {
        $this->homepageWithEverySection();

        app(SiteBuilder::class)->build();
        $html = $this->contents('index.html');

        $this->assertStringContainsString('action="/api/v1/enquiries"', $html);
        $this->assertStringContainsString('name="form_token"', $html);
        $this->assertStringContainsString('name="consent"', $html);
        $this->assertStringNotContainsString('web3forms.com', $html);

        \App\Models\Setting::set('web3forms_access_key', 'demo-key', 'forms');

        app(SiteBuilder::class)->build();
        $html = $this->contents('index.html');

        $this->assertStringContainsString('action="https://api.web3forms.com/submit"', $html);
        $this->assertStringNotContainsString('action="/api/v1/enquiries"', $html);
    }

    public function test_the_demo_content_uses_every_part_of_the_cms(): void
    {
        \Illuminate\Support\Facades\Storage::fake('local');
        \Illuminate\Support\Facades\Storage::fake('public');
        $this->seed(\Database\Seeders\DemoContentSeeder::class);

        $this->assertSame(
            ['closed' => 1, 'draft' => 1, 'published' => 2],
            JobPost::all()->countBy('status')->sortKeys()->all()
        );
        $this->assertTrue(\App\Models\Enquiry::where('delivery_status', 'spam')->exists());
        $this->assertTrue(\App\Models\JobApplication::where('delivery_status', 'spam')->exists());
        $this->assertTrue(Page::where('status', 'draft')->exists());
        $this->assertTrue(Page::where('published_at', '>', now())->exists());

        $used = \App\Models\PageSection::distinct()->pluck('type')->sort()->values()->all();
        $every = collect(config('tuxcms.pages.section_types'))->sort()->values()->all();
        $this->assertSame($every, $used);

        $this->assertSame(7, \App\Models\MediaLibrary::singleton()->getMedia('library')->count());

        app(SiteBuilder::class)->build();

        // The photographs reach the page, on the wall of work.
        $home = $this->contents('index.html');
        $this->assertStringContainsString('desk-from-above.jpg', $home);
        $this->assertStringContainsString('class="wall"', $home);

        $this->assertFileExists($this->output . '/careers/senior-product-designer/index.html');
        $this->assertFileDoesNotExist($this->output . '/careers/support-lead/index.html');
        $this->assertFileDoesNotExist($this->output . '/tuxcms-20/index.html');
    }

    public function test_careers_fall_back_to_the_theme_when_no_site_theme_is_set(): void
    {
        config(['site.theme' => null]);
        Page::create(['title' => 'Home', 'status' => 'published', 'is_homepage' => true]);
        JobPost::create([
            'title' => 'Bench joiner', 'slug' => 'bench-joiner', 'summary' => 'Make fine things.',
            'status' => 'published', 'published_at' => now()->subDay(),
        ]);

        app(SiteBuilder::class)->build();

        foreach (['careers/index.html', 'careers/bench-joiner/index.html'] as $file) {
            $html = $this->contents($file);
            $this->assertStringContainsString('/themes/shopfront/css/site', $html, $file);
            $this->assertStringNotContainsString('/themes/crystal/', $html, $file);
        }
    }
}
