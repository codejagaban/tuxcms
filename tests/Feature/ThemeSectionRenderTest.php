<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Services\Site\SiteBuilder;
use App\Support\Preview;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

/** A theme's own section types are drawn by its views, marked in the preview only. */
class ThemeSectionRenderTest extends TestCase
{
    private string $output;

    protected function setUp(): void
    {
        parent::setUp();

        View::addLocation(base_path('tests/Fixtures/views'));
        $this->output = storage_path('framework/testing/site-'.uniqid());
        File::ensureDirectoryExists($this->output);
        config(['site.output_path' => $this->output, 'site.url' => 'https://example.test']);
        $this->seedSiteSettings();
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->output);

        parent::tearDown();
    }

    private function offersPage(): Page
    {
        $page = Page::create(['title' => 'Prices', 'template' => 'schematest', 'status' => 'published', 'is_homepage' => true]);
        $page->sections()->create([
            'type' => 'offers', 'title' => 'Our offers', 'order' => 0, 'is_visible' => true,
            'data' => ['intro' => 'Pick one.', 'items' => [['name' => 'Starter', 'price' => '£40', 'features' => ['One call']]]],
        ]);

        return $page;
    }

    public function test_the_preview_marks_a_theme_types_fields(): void
    {
        $page = $this->offersPage();
        $id = $page->sections()->first()->id;

        $html = Preview::during(fn () => app(SiteBuilder::class)->renderPage($page));

        $this->assertStringContainsString("data-tuxcms-field=\"{$id}:data.items.0.name\">Starter", $html);
        $this->assertStringContainsString("data-tuxcms-field=\"{$id}:data.items.0.features.0\">One call", $html);
    }

    public function test_the_built_site_carries_no_markers_for_theme_types(): void
    {
        $this->offersPage();

        app(SiteBuilder::class)->build();
        $html = File::get($this->output.'/index.html');

        $this->assertStringContainsString('Starter', $html);
        $this->assertStringNotContainsString('data-tuxcms-', $html);
    }

    public function test_a_declared_type_with_no_view_falls_back_and_warns_in_the_preview(): void
    {
        $root = storage_path('framework/testing/views-'.uniqid());
        File::ensureDirectoryExists("{$root}/site/themes/viewless");
        File::put("{$root}/site/themes/viewless/theme.json", json_encode(['sections' => ['notes' => [
            'label' => 'Notes', 'fields' => ['title' => ['type' => 'text', 'label' => 'Heading']], 'defaults' => ['title' => 'Notes'],
        ]]]));
        File::put("{$root}/site/themes/viewless/page.blade.php",
            "@foreach (\$page->sections as \$section) @includeFirst(['site.themes.viewless.sections.'.\$section->type, 'site.sections.fallback'], ['section' => \$section]) @endforeach");
        View::addLocation($root);

        try {
            $page = Page::create(['title' => 'Notes', 'template' => 'viewless', 'status' => 'draft']);
            $page->sections()->create(['type' => 'notes', 'title' => 'Jotted down', 'order' => 0, 'is_visible' => true]);

            Log::spy();
            $html = Preview::during(fn () => app(SiteBuilder::class)->renderPage($page));

            $this->assertStringContainsString('Jotted down', $html);
            Log::shouldHaveReceived('warning')->once()->withArgs(fn ($message) => str_contains($message, 'Theme "viewless" declares the section type "notes"'));
        } finally {
            File::deleteDirectory($root);
        }
    }
}
