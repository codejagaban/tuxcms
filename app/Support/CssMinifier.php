<?php

namespace App\Support;

/**
 * A deliberately cautious CSS minifier: it drops comments and collapses
 * whitespace, and nothing else. Spaces inside values are kept (calc() needs
 * them), and quoted strings are left untouched.
 */
class CssMinifier
{
    public static function minify(string $css): string
    {
        // Comments go first: an apostrophe in a comment is not a string.
        $out = preg_replace('#/\*.*?\*/#s', '', $css);

        // Set quoted strings aside so nothing inside them is altered.
        $strings = [];
        $out = $out === null ? null : preg_replace_callback('/"[^"\n]*+"|\'[^\'\n]*+\'/', function ($match) use (&$strings) {
            $strings[] = $match[0];

            return '___STR'.(count($strings) - 1).'___';
        }, $out);

        $out = $out === null ? null : preg_replace('/\s+/', ' ', $out);
        // Whitespace next to braces, semicolons and commas carries no meaning.
        $out = $out === null ? null : preg_replace('/\s*([{};,])\s*/', '$1', $out);

        // A failed pattern returns null. Serving the original beats serving nothing.
        if ($out === null || trim($out) === '') {
            return $css;
        }

        $out = str_replace(';}', '}', $out);
        $out = preg_replace_callback('/___STR(\d+)___/', fn ($match) => $strings[(int) $match[1]], $out) ?? $css;

        return trim($out);
    }

    /**
     * Write site.min.css beside a theme's site.css when it is missing or out
     * of date. Returns the public path to serve, falling back to the source.
     */
    public static function themeStylesheet(string $theme): string
    {
        $source = public_path("themes/{$theme}/css/site.css");
        $minified = public_path("themes/{$theme}/css/site.min.css");

        if (! is_file($source)) {
            return "/themes/{$theme}/css/site.css";
        }

        if (! is_file($minified) || filemtime($minified) < filemtime($source)) {
            if (@file_put_contents($minified, self::minify(file_get_contents($source))) === false) {
                return "/themes/{$theme}/css/site.css";
            }
        }

        return "/themes/{$theme}/css/site.min.css";
    }
}
