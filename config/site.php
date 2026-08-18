<?php

return [
    /*
     * Canonical public URL for the generated site. Falls back to the
     * `site_url` setting, then APP_URL. Used for canonical tags, JSON-LD,
     * the sitemap and every absolute asset URL — the static build runs from
     * the CLI, where the request host is unavailable.
     */
    'url' => env('SITE_URL'),

    /*
     * Where the generated HTML is written. Defaults to Laravel's public/
     * directory, whose .htaccess already serves real files and directories
     * without touching PHP.
     */
    'output_path' => env('SITE_OUTPUT_PATH'),
];
