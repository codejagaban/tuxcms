<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Setting;
use App\Services\Site\SiteBuilder;
use App\Support\Preview;
use App\Support\Site;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

/**
 * The editor edits only what a template marks, so a theme that forgets a
 * marker silently loses that field from the page. This renders every section
 * type, with the defaults a new section starts with, through every installed
 * theme and through the base views, and checks that each field shown on the
 * page carries its marker.
 *
 * The defaults are the editor's own: dashboard/src/lib/sectionDefaults.json.
 */
class ThemeMarkerContractTest extends TestCase
{
    /**
     * Fields a view shows but deliberately leaves unmarked, because the text
     * shares its element with something else and is edited from the panel.
     * Keyed by the view that renders the section.
     */
    private const SHOWN_BUT_UNMARKED = [
        'site.sections.faq' => ['data.items.*.question'],               // beside the "+" mark
        'site.sections.contact-form' => ['data.fields.*.label'],        // beside the "*"
        'site.sections.map' => ['data.label'],                          // beside "Open in Maps"
        'site.themes.shopfront.sections.contact-form' => ['data.fields.*.label'], // beside "(optional)"
    ];

    /** Settings, links and choices: data a section holds but never shows as text. */
    private const NOT_TEXT = '/(^|\.)(url|href|icon|alignment|columns|access_key|subject|success_message|type|name|required|options|placeholder|query|image|image_url|background_image|src|[a-z_]+_alt|alt)$/';

    public function test_every_theme_marks_every_field_it_shows(): void
    {
        $this->seedSiteSettings();
        // A contact form shows its button only once it can send.
        Setting::set('web3forms_access_key', 'contract-test-key', 'forms');

        $defaults = json_decode(file_get_contents(base_path('dashboard/src/lib/sectionDefaults.json')), true);
        $this->assertEqualsCanonicalizing(config('tuxcms.pages.section_types'), array_keys($defaults),
            'Every section type needs defaults in sectionDefaults.json, and only those types.');

        $missing = [];
        foreach ([null, ...Site::themes()] as $theme) {
            $missing = [...$missing, ...$this->unmarkedFields($theme, $defaults)];
        }

        $this->assertSame([], $missing, "Shown on the page without a marker, so not editable there:\n".implode("\n", $missing));
    }

    /** @return array<int, string> one line per field shown without its marker */
    private function unmarkedFields(?string $theme, array $defaults): array
    {
        config(['site.theme' => $theme]);
        $page = Page::create([
            // Not a homepage and not one of a theme's hand-built pages, so the
            // page renders section by section.
            'title' => 'Contract', 'slug' => 'contract-'.($theme ?? 'base'),
            'template' => $theme ?? 'landing', 'status' => 'draft', 'is_homepage' => false,
        ]);
        foreach (array_keys($defaults) as $order => $type) {
            $page->sections()->create([
                'key' => $type, 'type' => $type, 'order' => $order, 'is_visible' => true,
                ...$defaults[$type],
            ]);
        }

        // Which view actually draws each section: a theme without one of its
        // own falls back to another's, and that is the view to hold to account.
        $rendered = [];
        View::composer('*', function ($view) use (&$rendered) {
            $rendered[] = $view->name();
        });
        $html = Preview::during(fn () => app(SiteBuilder::class)->renderPage($page->fresh()));
        preg_match_all('/data-tuxcms-field="([^"]+)"/', $html, $matches);
        $marked = array_flip(array_map('html_entity_decode', $matches[1]));

        $label = $theme ?? 'base views';
        $lines = [];
        foreach ($page->sections()->orderBy('order')->get() as $section) {
            $view = collect($rendered)->last(fn ($name) => str_ends_with($name, '.sections.'.str_replace('_', '-', $section->type)))
                ?? 'no view of its own';
            $allowed = array_map(fn ($path) => '/^'.str_replace('\*', '\d+', preg_quote($path, '/')).'$/',
                self::SHOWN_BUT_UNMARKED[$view] ?? []);

            foreach ($this->textFields($section) as $path) {
                if (isset($marked["{$section->id}:{$path}"])) {
                    continue;
                }
                foreach ($allowed as $pattern) {
                    if (preg_match($pattern, $path)) {
                        continue 2;
                    }
                }
                $lines[] = "  {$label} / {$section->type} ({$view}): {$path}";
            }
        }

        return $lines;
    }

    /** @return array<int, string> the path of every non-empty text field */
    private function textFields($section): array
    {
        $paths = [];
        $walk = function ($value, string $path) use (&$walk, &$paths) {
            if (is_array($value)) {
                foreach ($value as $key => $child) {
                    $walk($child, "{$path}.{$key}");
                }

                return;
            }
            if (is_string($value) && trim($value) !== '' && ! preg_match(self::NOT_TEXT, $path)) {
                $paths[] = $path;
            }
        };
        $walk($section->title, 'title');
        $walk($section->content, 'content');
        $walk($section->data ?? [], 'data');

        return $paths;
    }
}
