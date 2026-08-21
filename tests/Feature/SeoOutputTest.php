<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Services\Site\SiteBuilder;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * The whole point of pre-rendering is that crawlers get real content and real
 * metadata in the HTML, so this asserts against the generated markup rather
 * than against the model layer.
 */
class SeoOutputTest extends TestCase
{
    private string $output;

    protected function setUp(): void
    {
        parent::setUp();

        $this->output = storage_path('framework/testing/seo-' . uniqid());
        File::ensureDirectoryExists($this->output);
        config(['site.output_path' => $this->output, 'site.url' => 'https://example.test']);

        $this->seedSiteSettings();
        $this->asProduction();
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->output);

        parent::tearDown();
    }

    private function render(string $relative = 'index.html'): string
    {
        app(SiteBuilder::class)->build();

        return File::get($this->output . '/' . $relative);
    }

    private function schemaTypes(string $html): array
    {
        preg_match('#<script type="application/ld\+json">(.*?)</script>#s', $html, $m);
        $graph = json_decode($m[1] ?? '{}', true);

        return array_column($graph['@graph'] ?? [], '@type');
    }

    public function test_page_content_is_present_in_the_html(): void
    {
        $page = Page::factory()->homepage()->create();
        $page->sections()->create([
            'key' => 'hero', 'type' => 'hero', 'title' => 'Distinctive Headline',
            'content' => 'Supporting copy.', 'data' => [], 'order' => 0, 'is_visible' => true,
        ]);

        $html = $this->render();

        $this->assertStringContainsString('<h1', $html);
        $this->assertStringContainsString('Distinctive Headline', $html);
        $this->assertStringContainsString('Supporting copy.', $html);
    }

    public function test_hidden_sections_are_not_rendered(): void
    {
        $page = Page::factory()->homepage()->create();
        $page->sections()->create([
            'key' => 'hero', 'type' => 'hero', 'title' => 'Visible', 'order' => 0, 'is_visible' => true,
        ]);
        $page->sections()->create([
            'key' => 'cta', 'type' => 'cta', 'title' => 'Concealed', 'order' => 1, 'is_visible' => false,
        ]);

        $html = $this->render();

        $this->assertStringContainsString('Visible', $html);
        $this->assertStringNotContainsString('Concealed', $html);
    }

    public function test_meta_and_canonical_come_from_the_seo_record(): void
    {
        $page = Page::factory()->homepage()->create();
        $page->seo()->create([
            'meta_title' => 'Explicit Title',
            'meta_description' => 'Explicit description.',
        ]);

        $html = $this->render();

        // An explicit meta_title is used verbatim, with no site-name suffix.
        $this->assertStringContainsString('<title>Explicit Title</title>', $html);
        $this->assertStringContainsString('content="Explicit description."', $html);
        $this->assertStringContainsString('<link rel="canonical" href="https://example.test/"', $html);
    }

    public function test_title_falls_back_to_page_title_with_site_name(): void
    {
        Page::factory()->create(['title' => 'About', 'slug' => 'about']);
        Page::factory()->homepage()->create();

        $html = $this->render('about/index.html');

        $this->assertStringContainsString('<title>About — Test Site</title>', $html);
    }

    public function test_open_graph_and_twitter_tags_are_emitted(): void
    {
        Page::factory()->homepage()->create(['title' => 'Home', 'excerpt' => 'A summary.']);

        $html = $this->render();

        $this->assertStringContainsString('property="og:title"', $html);
        $this->assertStringContainsString('property="og:url"', $html);
        $this->assertStringContainsString('name="twitter:card"', $html);
    }

    public function test_default_share_image_is_used_as_a_fallback(): void
    {
        $this->seedSiteSettings(['default_og_image' => '/media/share.jpg']);
        Page::factory()->homepage()->create();

        $html = $this->render();

        $this->assertStringContainsString('content="https://example.test/media/share.jpg"', $html);
    }

    public function test_schema_graph_includes_core_types(): void
    {
        Page::factory()->homepage()->create();

        $types = $this->schemaTypes($this->render());

        $this->assertContains('Organization', $types);
        $this->assertContains('WebSite', $types);
        $this->assertContains('WebPage', $types);
    }

    public function test_nested_page_emits_breadcrumbs(): void
    {
        Page::factory()->homepage()->create();
        $parent = Page::factory()->create(['slug' => 'services']);
        Page::factory()->create(['slug' => 'web-design', 'parent_id' => $parent->id]);

        $types = $this->schemaTypes($this->render('services/web-design/index.html'));

        $this->assertContains('BreadcrumbList', $types);
    }

    public function test_homepage_does_not_emit_breadcrumbs(): void
    {
        Page::factory()->homepage()->create();

        $this->assertNotContains('BreadcrumbList', $this->schemaTypes($this->render()));
    }

    public function test_faq_section_emits_faq_schema(): void
    {
        $page = Page::factory()->homepage()->create();
        $page->sections()->create([
            'key' => 'faq', 'type' => 'faq', 'title' => 'FAQ', 'order' => 0, 'is_visible' => true,
            'data' => ['items' => [['question' => 'Is it fast?', 'answer' => 'Yes.']]],
        ]);

        $html = $this->render();

        $this->assertContains('FAQPage', $this->schemaTypes($html));
        // The answer must also be in the markup, not only the structured data.
        $this->assertStringContainsString('Yes.', $html);
    }

    public function test_custom_head_is_sanitised(): void
    {
        $page = Page::factory()->homepage()->create();
        $page->seo()->create([
            'custom_head' => '<meta name="verify" content="ok"><script src="https://evil.test/x.js"></script>',
        ]);

        $html = $this->render();

        $this->assertStringContainsString('name="verify"', $html);
        $this->assertStringNotContainsString('evil.test', $html);
    }

    public function test_assets_are_root_relative_not_host_bound(): void
    {
        Page::factory()->homepage()->create();

        $html = $this->render();

        if (!str_contains($html, 'rel="stylesheet"')) {
            $this->markTestSkipped('No Vite manifest present — run `npm run build` first.');
        }

        // Baking the host into asset URLs breaks the moment the site moves domain.
        $this->assertStringContainsString('href="/build/', $html);
        $this->assertStringNotContainsString('href="https://example.test/build/', $html);
    }
}
