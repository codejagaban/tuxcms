<?php

namespace App\Support;

use Illuminate\Validation\Rule;

/**
 * The section types a page may hold: the built-in ones, plus the ones
 * declared in theme.json by the theme that renders the page.
 */
class SectionTypes
{
    /** @return array<int, string> */
    public static function base(): array
    {
        return config('tuxcms.pages.section_types', []);
    }

    /**
     * The built-in types described the way theme.json describes a theme's:
     * label, blurb, fields and defaults. They live in
     * resources/sections/base.json, which is also where a new section's
     * defaults come from, so the editor and the tests read one source.
     *
     * @return array<string, array{label: string, blurb: string, fields: array, defaults: array}>
     */
    public static function baseSchema(): array
    {
        static $schema;

        return $schema ??= collect(json_decode((string) file_get_contents(resource_path('sections/base.json')), true, flags: JSON_THROW_ON_ERROR))
            ->map(fn ($definition, $type) => ThemeManifest::type('(built-in)', $type, $definition))
            ->all();
    }

    /**
     * Every type a page drawn by this theme may hold, built-in first, each
     * with its schema and where it comes from.
     *
     * @return array<int, array{type: string, source: string, label: string, blurb: string, fields: array, defaults: array}>
     */
    public static function schema(?string $theme): array
    {
        $types = [];
        foreach (self::baseSchema() as $type => $definition) {
            $types[] = ['type' => $type, 'source' => 'base', ...$definition];
        }
        foreach (ThemeManifest::sections($theme) as $type => $definition) {
            $types[] = ['type' => $type, 'source' => 'theme', ...$definition];
        }

        return $types;
    }

    /** @return array<int, string> every type a page drawn by this theme (or by the base views) may hold */
    public static function allowed(?string $theme): array
    {
        return [...self::base(), ...array_keys(ThemeManifest::sections($theme))];
    }

    /**
     * Validation rules for one section's fields, from a theme's schema.
     *
     * @param  array<string, array>  $fields  path => field, as ThemeManifest returns them
     * @param  string  $prefix  where the section sits in the request, e.g. "sections.2"
     * @return array<string, array>
     */
    public static function rules(array $fields, string $prefix): array
    {
        $rules = [];
        foreach ($fields as $path => $field) {
            $key = "{$prefix}.{$path}";
            $rules[$key] = [($field['required'] ?? false) ? 'required' : 'nullable', ...self::typeRules($field)];

            if ($field['type'] !== 'list') {
                continue;
            }
            if (is_string($field['item'])) {
                $rules["{$key}.*"] = ['nullable', ...self::typeRules(['type' => $field['item']])];
            } else {
                $rules = [...$rules, ...self::rules($field['item'], "{$key}.*")];
            }
        }

        return $rules;
    }

    /**
     * Readable names for the fields in validation messages: "Name" rather
     * than "sections.2.data.items.0.name".
     *
     * @return array<string, string>
     */
    public static function attributes(array $fields, string $prefix, string $within = ''): array
    {
        $names = [];
        foreach ($fields as $path => $field) {
            $key = "{$prefix}.{$path}";
            $names[$key] = $within.$field['label'];

            if ($field['type'] === 'list' && is_array($field['item'])) {
                $names = [...$names, ...self::attributes($field['item'], "{$key}.*", "{$within}{$field['label']} :position ")];
            } elseif ($field['type'] === 'list') {
                $names["{$key}.*"] = "{$within}{$field['label']} :position";
            }
        }

        return $names;
    }

    private static function typeRules(array $field): array
    {
        return match ($field['type']) {
            'text', 'textarea', 'richtext', 'image' => ['string'],
            'url' => ['string', self::urlRule()],
            'number' => ['numeric'],
            'boolean' => ['boolean'],
            'select' => [Rule::in(array_column($field['options'], 'value'))],
            'list' => ['array', self::listRule()],
        };
    }

    /** A web address, or a link within the site: /path, #anchor, mailto: or tel:. */
    private static function urlRule(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) {
            if (! is_string($value) || $value === '' || preg_match('~^(/|#|mailto:|tel:)~i', $value)) {
                return;
            }
            if (! preg_match('~^https?://~i', $value) || filter_var($value, FILTER_VALIDATE_URL) === false) {
                $fail('The :attribute must be a web address (https://…) or a path on the site (/…).');
            }
        };
    }

    /** A list, not an object with named keys. */
    private static function listRule(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) {
            if (is_array($value) && ! array_is_list($value)) {
                $fail('The :attribute must be a list.');
            }
        };
    }
}
