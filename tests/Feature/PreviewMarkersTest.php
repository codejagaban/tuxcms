<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\PageSection;
use App\Models\User;
use App\Services\Site\SiteBuilder;
use App\Support\Preview;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Laravel\Sanctum\Sanctum;
use RuntimeException;
use Tests\TestCase;

/**
 * The editor finds editable elements by markers the templates write, not by
 * matching text. The markers must appear in the editor's preview and never
 * in the published site.
 */
class PreviewMarkersTest extends TestCase
{
    private string $output;

    protected function setUp(): void
    {
        parent::setUp();

        // A one-file theme, so the preview can be tested without depending
        // on how far the real themes have been marked up.
        View::addLocation(base_path('tests/Fixtures/views'));

        $this->output = storage_path('framework/testing/site-'.uniqid());
        File::ensureDirectoryExists($this->output);
        config([
            'site.theme' => 'markertest',
            'site.output_path' => $this->output,
            'site.url' => 'https://example.test',
        ]);

        $this->seedSiteSettings();
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->output);

        parent::tearDown();
    }

    private function pageWithSection(): array
    {
        $page = Page::create([
            'title' => 'Home', 'template' => 'landing', 'status' => 'published', 'is_homepage' => true,
        ]);
        $section = $page->sections()->create([
            'key' => 'hero', 'type' => 'hero', 'title' => 'Made to measure',
            'data' => ['image' => '/storage/1/workshop.jpg'], 'order' => 0, 'is_visible' => true,
        ]);

        return [$page, $section];
    }

    public function test_the_directives_print_nothing_outside_a_preview(): void
    {
        [, $section] = $this->pageWithSection();

        $html = Blade::render(
            '<section @tuxsection($section)><h2 @field($section, "title")>x</h2><img @image($section, "data.image")></section>',
            ['section' => $section]
        );

        // A space is left where each marker was; the published page is
        // otherwise exactly as it was.
        $this->assertSame('<section ><h2 >x</h2><img ></section>', $html);
        $this->assertFalse(Preview::active());
    }

    public function test_the_directives_name_the_section_and_field_inside_a_preview(): void
    {
        [, $section] = $this->pageWithSection();

        $html = Preview::during(fn () => Blade::render(
            '<section @tuxsection($section)><h2 @field($section, "data.items.0.question")>x</h2><img @image($section, "data.image")></section>',
            ['section' => $section]
        ));

        $id = $section->id;
        $this->assertSame(
            "<section data-tuxcms-section=\"{$id}\"><h2 data-tuxcms-field=\"{$id}:data.items.0.question\">x</h2><img data-tuxcms-image=\"{$id}:data.image\"></section>",
            $html
        );
        $this->assertFalse(Preview::active());
    }

    public function test_a_path_cannot_break_out_of_its_attribute(): void
    {
        [, $section] = $this->pageWithSection();

        $marker = Preview::during(fn () => Preview::field($section, 'title" onclick="x'));

        $this->assertSame("data-tuxcms-field=\"{$section->id}:title&quot; onclick=&quot;x\"", $marker);
    }

    public function test_a_missing_or_unsaved_section_gets_no_marker(): void
    {
        $markers = Preview::during(fn () => [
            Preview::field(null, 'title'),
            Preview::image(new PageSection, 'data.image'),
            Preview::section(null),
        ]);

        $this->assertSame(['', '', ''], $markers);
    }

    public function test_the_markers_switch_off_again_when_a_render_fails(): void
    {
        try {
            Preview::during(fn () => throw new RuntimeException('Render failed'));
        } catch (RuntimeException) {
            // Expected.
        }

        $this->assertFalse(Preview::active());
    }

    public function test_the_editors_preview_carries_the_markers(): void
    {
        [$page, $section] = $this->pageWithSection();
        Sanctum::actingAs(User::factory()->create());

        $html = $this->get("/api/v1/pages/{$page->id}/preview")->assertOk()->getContent();

        $this->assertStringContainsString("data-tuxcms-section=\"{$section->id}\"", $html);
        $this->assertStringContainsString("data-tuxcms-field=\"{$section->id}:title\"", $html);
        $this->assertStringContainsString("data-tuxcms-image=\"{$section->id}:data.image\"", $html);
        $this->assertFalse(Preview::active());
    }

    public function test_the_published_site_carries_no_markers(): void
    {
        $this->pageWithSection();

        app(SiteBuilder::class)->build();
        $html = File::get($this->output.'/index.html');

        $this->assertStringContainsString('Made to measure', $html);
        $this->assertStringNotContainsString('data-tuxcms-', $html);
    }
}
