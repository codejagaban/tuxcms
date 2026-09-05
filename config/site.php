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

    /*
     * Top-level slugs the static build must never claim. Everything here is a
     * real directory in the docroot: a page at /admin would have `site:build`
     * write admin/index.html straight over the dashboard, and one at /storage
     * would write into the linked media directory.
     */
    'reserved_slugs' => [
        'admin',
        'api',
        'build',
        'storage',
        'up',
    ],
];
