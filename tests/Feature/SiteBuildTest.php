<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Services\Site\SiteBuilder;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * The builder writes into a live document root, so these tests cover both what
 * it produces and — just as importantly — what it must never delete.
 */
class SiteBuildTest extends TestCase
{
    private string $output;

    protected function setUp(): void
    {
        parent::setUp();

        $this->output = storage_path('framework/testing/site-' . uniqid());
        File::ensureDirectoryExists($this->output);
        config(['site.output_path' => $this->output, 'site.url' => 'https://example.test']);

        $this->seedSiteSettings();
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->output);

        parent::tearDown();
    }

    private function build(): array
    {
        return app(SiteBuilder::class)->build();
    }

    private function contents(string $relative): string
    {
        $path = $this->output . '/' . $relative;
        $this->assertFileExists($path);

        return File::get($path);
    }

    public function test_homepage_is_written_to_index_html(): void
    {
        Page::factory()->homepage()->create(['title' => 'Welcome']);

        $this->build();

        $this->assertStringContainsString('Welcome', $this->contents('index.html'));
    }

    public function test_nested_pages_are_written_as_directory_indexes(): void
    {
        $parent = Page::factory()->create(['slug' => 'services']);
        Page::factory()->create(['slug' => 'web-design', 'parent_id' => $parent->id]);

        $this->build();

        $this->assertFileExists($this->output . '/services/index.html');
        $this->assertFileExists($this->output . '/services/web-design/index.html');
    }

    public function test_drafts_and_scheduled_pages_are_not_written(): void
    {
        Page::factory()->homepage()->create();
        Page::factory()->draft()->create(['slug' => 'careers']);
        Page::factory()->scheduled()->create(['slug' => 'launch']);

        $this->build();

        $this->assertFileDoesNotExist($this->output . '/careers/index.html');
        $this->assertFileDoesNotExist($this->output . '/launch/index.html');
    }

    public function test_sitemap_uses_full_paths_and_excludes_noindex_pages(): void
    {
        $this->asProduction();
        Page::factory()->homepage()->create();
        $parent = Page::factory()->create(['slug' => 'services']);
        Page::factory()->create(['slug' => 'web-design', 'parent_id' => $parent->id]);

        $private = Page::factory()->create(['slug' => 'private']);
        $private->seo()->create(['robots' => 'noindex, follow']);

        $this->build();
        $sitemap = $this->contents('sitemap.xml');

        $this->assertStringContainsString('https://example.test/', $sitemap);
        $this->assertStringContainsString('https://example.test/services/web-design/', $sitemap);
        $this->assertStringNotContainsString('/private/', $sitemap);
    }

    public function test_robots_disallows_everything_outside_production(): void
    {
        Page::factory()->homepage()->create();

        $this->build();

        $this->assertStringContainsString('Disallow: /', $this->contents('robots.txt'));
    }

    public function test_robots_allows_indexing_in_production(): void
    {
        $this->asProduction();
        Page::factory()->homepage()->create();

        $this->build();
        $robots = $this->contents('robots.txt');

        $this->assertStringContainsString('Allow: /', $robots);
        $this->assertStringContainsString('Sitemap: https://example.test/sitemap.xml', $robots);
    }

    public function test_404_page_is_generated(): void
    {
        Page::factory()->homepage()->create();

        $this->build();

        $this->assertFileExists($this->output . '/404.html');
    }

    public function test_unpublishing_a_page_prunes_its_file(): void
    {
        Page::factory()->homepage()->create();
        $page = Page::factory()->create(['slug' => 'temporary']);

        $this->build();
        $this->assertFileExists($this->output . '/temporary/index.html');

        $page->update(['status' => 'draft']);
        $result = $this->build();

        $this->assertFileDoesNotExist($this->output . '/temporary/index.html');
        $this->assertSame(1, $result['pruned']);
    }

    /**
     * The builder shares a directory with Laravel's front controller, so a
     * pruning bug could take the whole application offline.
     */
    public function test_pruning_never_touches_files_it_did_not_create(): void
    {
        File::put($this->output . '/index.php', '<?php // front controller');
        File::put($this->output . '/.htaccess', 'DirectoryIndex index.html index.php');
        File::ensureDirectoryExists($this->output . '/build/assets');
        File::put($this->output . '/build/assets/app.css', 'body{}');

        Page::factory()->homepage()->create();
        $page = Page::factory()->create(['slug' => 'temporary']);

        $this->build();
        $page->update(['status' => 'draft']);
        $this->build();

        $this->assertFileExists($this->output . '/index.php');
        $this->assertFileExists($this->output . '/.htaccess');
        $this->assertFileExists($this->output . '/build/assets/app.css');
    }

    public function test_build_reports_what_it_wrote(): void
    {
        Page::factory()->homepage()->create();
        Page::factory()->create();

        $result = $this->build();

        $this->assertSame(2, $result['pages']);
        // Two pages plus 404, sitemap and robots.
        $this->assertSame(5, $result['files']);
    }
}
