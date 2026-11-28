<?php

namespace App\Support;

use RuntimeException;

/** A theme's theme.json cannot be used. The message names the theme, and the section type when there is one. */
class InvalidThemeManifest extends RuntimeException
{
    public static function for(string $theme, ?string $type, string $problem): self
    {
        $where = $type === null ? "Theme \"{$theme}\"" : "Theme \"{$theme}\", section type \"{$type}\"";

        return new self("{$where}: {$problem}");
    }
}
