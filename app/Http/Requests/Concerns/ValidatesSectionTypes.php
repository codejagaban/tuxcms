<?php

namespace App\Http\Requests\Concerns;

use App\Models\Page;
use App\Support\SectionTypes;
use App\Support\Site;
use App\Support\ThemeManifest;
use Illuminate\Validation\Validator;

/**
 * A page may hold the built-in section types and the ones declared by the
 * theme that renders it (its template's theme, else SITE_THEME). A section of
 * a theme's own type is also checked against that theme's field schema.
 */
trait ValidatesSectionTypes
{
    /** The template the page will have once this request is applied. */
    abstract protected function targetTemplate(): ?string;

    protected function sectionTheme(): ?string
    {
        return Site::pageTheme($this->targetTemplate());
    }

    /** @return array<string, array> */
    protected function sectionRules(): array
    {
        $theme = $this->sectionTheme();
        $allowed = SectionTypes::allowed($theme);
        $declared = ThemeManifest::sections($theme);

        $rules = [
            'sections.*.type' => ['required_with:sections', 'string', function (string $attribute, mixed $value, \Closure $fail) use ($allowed, $theme) {
                if (! is_string($value) || in_array($value, $allowed, true)) {
                    return;
                }
                $section = $this->input(substr($attribute, 0, -strlen('.type')));
                $fail(sprintf('%s the section type "%s", so %s cannot be shown on this page.',
                    self::renderer($theme), $value, self::describe((array) $section)));
            }],
        ];

        foreach ((array) $this->input('sections', []) as $index => $section) {
            $type = is_array($section) ? ($section['type'] ?? null) : null;
            if (is_string($type) && isset($declared[$type])) {
                $rules = [...$rules, ...SectionTypes::rules($declared[$type]['fields'], "sections.{$index}")];
            }
        }

        return $rules;
    }

    public function attributes(): array
    {
        $declared = ThemeManifest::sections($this->sectionTheme());
        $names = [];
        foreach ((array) $this->input('sections', []) as $index => $section) {
            $type = is_array($section) ? ($section['type'] ?? null) : null;
            if (is_string($type) && isset($declared[$type])) {
                $names = [...$names, ...SectionTypes::attributes($declared[$type]['fields'], "sections.{$index}")];
            }
        }

        return $names;
    }

    /**
     * A template change sent without the sections would leave the page's
     * stored sections behind on a theme that cannot draw them. Refuse it and
     * say which, rather than dropping them.
     */
    protected function failOnStrandedSections(Validator $validator, ?Page $page): void
    {
        if (! $page || ! $this->has('template') || $this->has('sections')) {
            return;
        }

        $theme = $this->sectionTheme();
        if ($theme === Site::pageTheme($page->template)) {
            return;
        }

        $allowed = SectionTypes::allowed($theme);
        $stranded = $page->sections()->orderBy('order')->get()
            ->reject(fn ($section) => in_array($section->type, $allowed, true))
            ->map(fn ($section) => self::describe($section->only(['title', 'type'])));

        if ($stranded->isNotEmpty()) {
            $validator->errors()->add('template', sprintf(
                '%s every section this page holds. Remove or replace %s first.',
                self::renderer($theme), $stranded->join(', ', ' and '),
            ));
        }
    }

    private static function renderer(?string $theme): string
    {
        return $theme ? "The {$theme} theme does not provide" : 'The base site views do not provide';
    }

    private static function describe(array $section): string
    {
        $title = trim((string) ($section['title'] ?? ''));
        $type = (string) ($section['type'] ?? '');

        return $title !== '' ? "the section \"{$title}\" ({$type})" : "the {$type} section";
    }
}
