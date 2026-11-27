<?php

namespace App\Support;

use App\Models\PageSection;

/**
 * Markers that tell the editor exactly which element shows which field.
 *
 * A theme writes `@field($section, 'title')` on the element that displays a
 * section's title, and the editor makes that element editable and saves what
 * is typed to that section and path. Nothing has to be guessed from the text.
 *
 * The markers are written only while a page is rendered for the editor's
 * preview. A published page is rendered with them off, so the built site
 * carries none of them.
 *
 * Blade only recognises a directive that follows a space, so a marker always
 * goes after one: `<h2 class="head" @field($section, 'title')>`. On the
 * published site that leaves a space inside the tag and nothing else.
 */
class Preview
{
    private static bool $active = false;

    /**
     * Render something with the markers switched on, and always switch them
     * off again afterwards, even when the render fails.
     *
     * @template T
     *
     * @param  callable(): T  $render
     * @return T
     */
    public static function during(callable $render): mixed
    {
        $before = self::$active;
        self::$active = true;

        try {
            return $render();
        } finally {
            self::$active = $before;
        }
    }

    public static function active(): bool
    {
        return self::$active;
    }

    /** The element shows this field's text: `data-tuxcms-field="12:title"`. */
    public static function field(?PageSection $section, string $path): string
    {
        return self::attribute('data-tuxcms-field', $section, $path);
    }

    /** The element shows this field's picture: `data-tuxcms-image="12:data.image"`. */
    public static function image(?PageSection $section, string $path): string
    {
        return self::attribute('data-tuxcms-image', $section, $path);
    }

    /** The element holds this whole section: `data-tuxcms-section="12"`. */
    public static function section(?PageSection $section): string
    {
        if (! self::$active || ! $section?->getKey()) {
            return '';
        }

        return 'data-tuxcms-section="'.e((string) $section->getKey()).'"';
    }

    /**
     * A section that is missing (a hand-built page asking for a section the
     * page does not have) or not saved yet has nothing to point at, so it
     * gets no marker rather than a broken one.
     */
    private static function attribute(string $name, ?PageSection $section, string $path): string
    {
        if (! self::$active || ! $section?->getKey() || $path === '') {
            return '';
        }

        return $name.'="'.e($section->getKey().':'.$path).'"';
    }
}
