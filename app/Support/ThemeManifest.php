<?php

namespace App\Support;

use Illuminate\Support\Facades\View;

/**
 * A theme's own section types, read from the optional theme.json beside its
 * views (resources/views/site/themes/<name>/theme.json).
 *
 *     {
 *       "sections": {
 *         "<type>": {
 *           "label": "Shown in the editor's menu",
 *           "blurb": "One line under the label",
 *           "fields": { "<path>": { "type": "text", "label": "Heading" }, ... },
 *           "defaults": { "title": "...", "content": "...", "data": { ... } }
 *         }
 *       },
 *       "enquiries": {
 *         "<kind>": { "label": "Client review", "plural": "Client reviews" }
 *       }
 *     }
 *
 * `enquiries` names the kinds of message the site's forms send to the
 * inbox, as each form's hidden `type`. See App\Support\EnquiryKinds.
 *
 * A field's path is `title`, `content` or `data.<key>[.<key>...]`, the same
 * paths the @field and @image markers name. A field is:
 *
 *     type         one of FIELD_TYPES
 *     label        what the editor calls it
 *     required     true when a page cannot be saved without it
 *     options      select only: the choices, as strings or {value, label}
 *                  (a value may be a number)
 *     item         list only: an object of fields, or the name of a scalar type
 *     placeholder  optional hint in the editor's input
 *     default      optional value a new list item starts with
 *     inspector    false to keep the field out of the side panel, because it
 *                  is edited on the preview (used by the base types)
 *     preview      false when the page never shows the field as plain text
 *                  an editor could type over, such as an image's description
 *                  or formatted rich text, so it is edited in the side panel
 *                  and carries no marker
 *
 * The manifest is checked as it is read, and a bad one throws, naming the
 * theme, the type and the problem, rather than letting a half-understood
 * schema reach the API or the editor.
 */
class ThemeManifest
{
    public const FIELD_TYPES = ['text', 'textarea', 'richtext', 'url', 'image', 'number', 'boolean', 'select', 'list'];

    /** The types a list may hold directly, as `"item": "<type>"`. */
    public const SCALAR_TYPES = ['text', 'textarea', 'richtext', 'url', 'image', 'number', 'boolean'];

    private const TYPE_NAME = '/^[a-z][a-z0-9_]*$/';

    private const KEY = '/^[a-z_][a-z0-9_]*$/';

    private const PATH = '/^(title|content|data(\.[a-z_][a-z0-9_]*)+)$/';

    private const FIELD_KEYS = ['type', 'label', 'required', 'options', 'item', 'placeholder', 'default', 'inspector', 'preview'];

    /** @var array<string, array<string, array>> parsed manifests, keyed by file, size and time */
    private static array $cache = [];

    /**
     * The section types a theme declares, keyed by type. A theme with no
     * theme.json, and no theme at all, declares none.
     *
     * @return array<string, array{label: string, blurb: string, fields: array, defaults: array}>
     */
    public static function sections(?string $theme): array
    {
        if ($theme === null || $theme === '' || ! ($path = self::path($theme))) {
            return [];
        }

        $key = $path.'|'.filesize($path).'|'.filemtime($path);

        return self::$cache[$key] ??= self::parse($theme, (string) file_get_contents($path));
    }

    /**
     * The kinds of enquiry a theme's forms send, keyed by kind, each with a
     * label for one and for many. A theme with no theme.json declares none.
     *
     * @return array<string, array{label: string, plural: string}>
     */
    public static function enquiries(?string $theme): array
    {
        if ($theme === null || $theme === '' || ! ($path = self::path($theme))) {
            return [];
        }

        // Checks the file as a whole first, so a broken one fails the same way.
        self::sections($theme);
        $key = 'enquiries|'.$path.'|'.filesize($path).'|'.filemtime($path);

        return self::$cache[$key] ??= self::parseEnquiries($theme, json_decode((string) file_get_contents($path), true)['enquiries'] ?? []);
    }

    /** @return array<string, array{label: string, plural: string}> */
    public static function parseEnquiries(string $theme, mixed $kinds): array
    {
        if (! is_array($kinds) || array_is_list($kinds) && $kinds !== []) {
            throw InvalidThemeManifest::for($theme, null, '"enquiries" must be an object keyed by kind.');
        }

        $checked = [];
        foreach ($kinds as $kind => $definition) {
            $kind = (string) $kind;
            $where = "enquiry kind \"{$kind}\"";
            if (! preg_match(self::TYPE_NAME, $kind) || strlen($kind) > 40) {
                throw InvalidThemeManifest::for($theme, null, "{$where}: a kind must be lower case letters, digits and underscores, starting with a letter.");
            }
            if (! is_array($definition) || ! is_string($definition['label'] ?? null) || trim($definition['label']) === '') {
                throw InvalidThemeManifest::for($theme, null, "{$where}: \"label\" is required.");
            }
            if (isset($definition['plural']) && (! is_string($definition['plural']) || trim($definition['plural']) === '')) {
                throw InvalidThemeManifest::for($theme, null, "{$where}: \"plural\" must be text.");
            }
            if ($unknown = array_diff(array_keys($definition), ['label', 'plural'])) {
                throw InvalidThemeManifest::for($theme, null, "{$where} has unknown keys: ".implode(', ', $unknown).'.');
            }

            $checked[$kind] = [
                'label' => trim($definition['label']),
                'plural' => trim($definition['plural'] ?? $definition['label'].'s'),
            ];
        }

        return $checked;
    }

    /** The theme's theme.json, looked for in every view location so a test can add a theme of its own. */
    public static function path(string $theme): ?string
    {
        foreach (View::getFinder()->getPaths() as $root) {
            if (is_file($file = "{$root}/site/themes/{$theme}/theme.json")) {
                return $file;
            }
        }

        return null;
    }

    /** Read and check a manifest's text. */
    public static function parse(string $theme, string $json): array
    {
        $manifest = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw InvalidThemeManifest::for($theme, null, 'theme.json is not valid JSON ('.json_last_error_msg().').');
        }
        if (! is_array($manifest) || array_is_list($manifest) && $manifest !== []) {
            throw InvalidThemeManifest::for($theme, null, 'theme.json must hold an object.');
        }

        $sections = $manifest['sections'] ?? [];
        if (! is_array($sections) || array_is_list($sections) && $sections !== []) {
            throw InvalidThemeManifest::for($theme, null, '"sections" must be an object keyed by section type.');
        }

        $base = config('tuxcms.pages.section_types', []);
        $types = [];
        foreach ($sections as $type => $definition) {
            $type = (string) $type;
            if (! preg_match(self::TYPE_NAME, $type)) {
                throw InvalidThemeManifest::for($theme, $type, 'a type name must be lower case letters, digits and underscores, starting with a letter.');
            }
            if (in_array($type, $base, true)) {
                throw InvalidThemeManifest::for($theme, $type, 'this is a built-in section type; a theme may add types but not redeclare one.');
            }

            $types[$type] = self::type($theme, $type, $definition);
        }

        return $types;
    }

    /** Check one section type's definition. */
    public static function type(string $theme, string $type, mixed $definition): array
    {
        $fail = fn (string $problem) => InvalidThemeManifest::for($theme, $type, $problem);

        if (! is_array($definition)) {
            throw $fail('the definition must be an object.');
        }
        if (! is_string($definition['label'] ?? null) || trim($definition['label']) === '') {
            throw $fail('"label" is required.');
        }
        if (isset($definition['blurb']) && ! is_string($definition['blurb'])) {
            throw $fail('"blurb" must be a string.');
        }

        $fields = $definition['fields'] ?? null;
        if (! is_array($fields) || array_is_list($fields)) {
            throw $fail('"fields" must be an object keyed by field path.');
        }
        foreach (array_keys($fields) as $path) {
            if (! preg_match(self::PATH, (string) $path)) {
                throw $fail("field \"{$path}\" must be title, content or data.<key>.");
            }
        }

        if (! array_key_exists('defaults', $definition)) {
            throw $fail('"defaults" are required: they are what a new section starts with.');
        }
        $defaults = $definition['defaults'];
        if (! is_array($defaults) || array_is_list($defaults) && $defaults !== []) {
            throw $fail('"defaults" must be an object with title, content and data.');
        }
        if ($extra = array_diff(array_keys($defaults), ['title', 'content', 'data'])) {
            throw $fail('"defaults" may only hold title, content and data, not '.implode(', ', $extra).'.');
        }
        if (isset($defaults['data']) && (! is_array($defaults['data']) || array_is_list($defaults['data']) && $defaults['data'] !== [])) {
            throw $fail('"defaults.data" must be an object.');
        }

        return [
            'label' => $definition['label'],
            'blurb' => $definition['blurb'] ?? '',
            'fields' => self::fields($fields, $fail),
            'defaults' => [
                'title' => $defaults['title'] ?? '',
                'content' => $defaults['content'] ?? '',
                'data' => $defaults['data'] ?? [],
            ],
        ];
    }

    /**
     * Check a set of fields, returning them with select options in one shape.
     *
     * @param  callable(string): \Throwable  $fail
     */
    public static function fields(array $fields, callable $fail, string $within = ''): array
    {
        $checked = [];
        foreach ($fields as $path => $field) {
            $name = $within.$path;
            if (! is_array($field)) {
                throw $fail("field \"{$name}\" must be an object.");
            }
            if ($unknown = array_diff(array_keys($field), self::FIELD_KEYS)) {
                throw $fail("field \"{$name}\" has unknown keys: ".implode(', ', $unknown).'.');
            }

            $type = $field['type'] ?? null;
            if (! in_array($type, self::FIELD_TYPES, true)) {
                throw $fail("field \"{$name}\" has unknown type \"".(is_scalar($type) ? $type : gettype($type)).'"; use one of '.implode(', ', self::FIELD_TYPES).'.');
            }
            if (! is_string($field['label'] ?? null) || trim($field['label']) === '') {
                throw $fail("field \"{$name}\" needs a label.");
            }
            foreach (['required', 'inspector', 'preview'] as $flag) {
                if (isset($field[$flag]) && ! is_bool($field[$flag])) {
                    throw $fail("field \"{$name}\": \"{$flag}\" must be true or false.");
                }
            }
            if (isset($field['placeholder']) && ! is_string($field['placeholder'])) {
                throw $fail("field \"{$name}\": \"placeholder\" must be a string.");
            }

            if ($type === 'select') {
                $field['options'] = self::options($field['options'] ?? null, fn () => $fail("field \"{$name}\": a select needs \"options\", a list of strings or {value, label}."));
            } elseif (isset($field['options'])) {
                throw $fail("field \"{$name}\": only a select takes \"options\".");
            }

            if ($type === 'list') {
                $item = $field['item'] ?? null;
                if (is_string($item)) {
                    if (! in_array($item, self::SCALAR_TYPES, true)) {
                        throw $fail("field \"{$name}\": a list of single values holds one of ".implode(', ', self::SCALAR_TYPES).", not \"{$item}\".");
                    }
                } elseif (is_array($item) && ! array_is_list($item)) {
                    foreach (array_keys($item) as $key) {
                        if (! preg_match(self::KEY, (string) $key)) {
                            throw $fail("field \"{$name}.{$key}\": an item key must be lower case letters, digits and underscores.");
                        }
                    }
                    $field['item'] = self::fields($item, $fail, "{$name}.");
                } else {
                    throw $fail("field \"{$name}\": a list needs \"item\", an object of fields or a single value type.");
                }
            } elseif (isset($field['item'])) {
                throw $fail("field \"{$name}\": only a list takes \"item\".");
            }

            $checked[$path] = $field;
        }

        return $checked;
    }

    /** @return array<int, array{value: string, label: string}> */
    private static function options(mixed $options, callable $fail): array
    {
        if (! is_array($options) || $options === [] || ! array_is_list($options)) {
            throw $fail();
        }

        return array_map(function ($option) use ($fail) {
            if (is_string($option)) {
                return ['value' => $option, 'label' => $option];
            }
            // A value may be a number, for a choice stored as one (a column count, say).
            if (is_array($option) && (is_string($option['value'] ?? null) || is_int($option['value'] ?? null) || is_float($option['value'] ?? null))) {
                return ['value' => $option['value'], 'label' => is_string($option['label'] ?? null) ? $option['label'] : (string) $option['value']];
            }
            throw $fail();
        }, $options);
    }
}
