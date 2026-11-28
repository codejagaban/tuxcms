<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Setting;
use App\Services\Site\SiteBuilder;
use App\Support\Preview;
use App\Support\SectionTypes;
use App\Support\Site;
use App\Support\ThemeManifest;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

/**
 * The editor edits only what a template marks, so a theme that forgets a
 * marker silently loses that field from the page. This renders every section
 * type, with the defaults a new section starts with, through every installed
 * theme and through the base views, and checks that each field shown on the
 * page carries its marker.
 *
 * The built-in types start from the editor's own defaults,
 * resources/sections/base.json. A theme's own types (its theme.json) start
 * from the manifest's defaults, with every empty list given one sample item
 * built from the schema, so that the list's fields are on the page too. For
 * those types the schema says which fields are text, which must carry
 * @field, and which are pictures, which must carry @image.
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

        $this->assertEqualsCanonicalizing(config('tuxcms.pages.section_types'), array_keys(SectionTypes::baseSchema()),
            'Every built-in section type needs defaults in resources/sections/base.json, and only those types.');

        $missing = [];
        foreach ([null, ...Site::themes()] as $theme) {
            $missing = [...$missing, ...$this->unmarkedFields($theme)];
        }

        $this->assertSame([], $missing, "Shown on the page without a marker, so not editable there:\n".implode("\n", $missing));
    }

    public function test_a_themes_own_types_are_held_to_the_same_rule(): void
    {
        $this->seedSiteSettings();
        Setting::set('web3forms_access_key', 'contract-test-key', 'forms');
        View::addLocation(base_path('tests/Fixtures/views'));

        $this->assertSame([], $this->unmarkedFields('schematest'));
    }

    public function test_an_unmarked_field_of_a_theme_type_is_caught(): void
    {
        $this->seedSiteSettings();
        Setting::set('web3forms_access_key', 'contract-test-key', 'forms');

        // The fixture theme, with the offer's price and photo left unmarked.
        $root = storage_path('framework/testing/views-'.uniqid());
        File::copyDirectory(base_path('tests/Fixtures/views/site/themes/schematest'), "{$root}/site/themes/schematest");
        $view = "{$root}/site/themes/schematest/sections/offers.blade.php";
        File::put($view, str_replace(
            [' @field($section, "data.items.{$i}.price")', ' @image($section, "data.items.{$i}.image")'], '', File::get($view)));
        View::addLocation($root);

        try {
            $this->assertSame([
                '  schematest / offers (site.themes.schematest.sections.offers): data.items.0.price',
                '  schematest / offers (site.themes.schematest.sections.offers): data.items.0.image (picture)',
            ], $this->unmarkedFields('schematest'));
        } finally {
            File::deleteDirectory($root);
        }
    }

    /** @return array<int, string> one line per field shown without its marker */
    private function unmarkedFields(?string $theme): array
    {
        $defaults = array_map(fn ($type) => $type['defaults'], SectionTypes::baseSchema());
        $declared = ThemeManifest::sections($theme);
        foreach ($declared as $type => $definition) {
            $defaults[$type] = $this->withSampleItems($definition['defaults'], $definition['fields']);
        }

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
        preg_match_all('/data-tuxcms-image="([^"]+)"/', $html, $matches);
        $pictured = array_flip(array_map('html_entity_decode', $matches[1]));

        $label = $theme ?? 'base views';
        $lines = [];
        foreach ($page->sections()->orderBy('order')->get() as $section) {
            $view = collect($rendered)->last(fn ($name) => str_ends_with($name, '.sections.'.str_replace('_', '-', $section->type)))
                ?? 'no view of its own';
            $allowed = array_map(fn ($path) => '/^'.str_replace('\*', '\d+', preg_quote($path, '/')).'$/',
                self::SHOWN_BUT_UNMARKED[$view] ?? []);

            if (isset($declared[$section->type])) {
                foreach ($this->schemaFields($section, $declared[$section->type]['fields']) as [$path, $kind]) {
                    $markers = $kind === 'image' ? $pictured : $marked;
                    if (! isset($markers["{$section->id}:{$path}"])) {
                        $lines[] = "  {$label} / {$section->type} ({$view}): {$path}".($kind === 'image' ? ' (picture)' : '');
                    }
                }

                continue;
            }

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

    /**
     * A theme type's defaults, with each empty list given one sample item
     * built from its schema.
     */
    private function withSampleItems(array $defaults, array $fields): array
    {
        foreach ($fields as $path => $field) {
            if ($field['type'] === 'list' && empty(data_get($defaults, $path))) {
                data_set($defaults, $path, $this->sample($field));
            }
        }

        return $defaults;
    }

    private function sample(array|string $field): mixed
    {
        $field = is_string($field) ? ['type' => $field, 'label' => 'item'] : $field;

        return match ($field['type']) {
            'text', 'textarea', 'richtext' => "Sample {$field['label']}",
            'url' => 'https://example.com/',
            'image' => '/storage/sample.jpg',
            'number' => 1,
            'boolean' => true,
            'select' => $field['options'][0]['value'],
            'list' => [is_string($field['item'])
                ? $this->sample($field['item'])
                : array_map(fn ($child) => $this->sample($child), $field['item'])],
        };
    }

    /**
     * The text and picture fields of a theme-type section that hold a value,
     * found by walking the section along its schema.
     *
     * @return array<int, array{0: string, 1: string}> [path, "text" or "image"]
     */
    private function schemaFields($section, array $fields): array
    {
        $found = [];
        $check = function (string $type, mixed $value, string $path) use (&$found) {
            if (in_array($type, ['text', 'textarea', 'richtext', 'image'], true) && is_string($value) && trim($value) !== '') {
                $found[] = [$path, $type === 'image' ? 'image' : 'text'];
            }
        };
        $walk = function (array $fields, mixed $values, string $within) use (&$walk, $check) {
            foreach ($fields as $path => $field) {
                $key = $within === '' ? $path : "{$within}.{$path}";
                $value = data_get($values, $path);

                if ($field['type'] !== 'list') {
                    $check($field['type'], $value, $key);

                    continue;
                }
                foreach (is_array($value) ? $value : [] as $index => $item) {
                    is_string($field['item'])
                        ? $check($field['item'], $item, "{$key}.{$index}")
                        : $walk($field['item'], $item, "{$key}.{$index}");
                }
            }
        };
        $walk($fields, ['title' => $section->title, 'content' => $section->content, 'data' => $section->data ?? []], '');

        return $found;
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
