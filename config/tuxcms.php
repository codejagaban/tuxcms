<?php

return [
    /*
    |--------------------------------------------------------------------------
    | TuxCMS Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration settings for the TuxCMS headless CMS
    |
    */

    'api' => [
        'prefix' => 'api/v1',
        'pagination' => [
            'per_page' => 15,
            'max_per_page' => 100,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Multi-Tenancy
    |--------------------------------------------------------------------------
    |
    | base_domain: Used for subdomain-based tenant resolution.
    | e.g., if set to "tuxcms.com", requests to "acme.tuxcms.com"
    | will resolve to the tenant with slug "acme".
    |
    */
    'base_domain' => env('TUXCMS_BASE_DOMAIN', null),

    'pages' => [
        'per_page' => 15,
        'templates' => ['default', 'landing', 'contact', 'about', 'services', 'blank'],
    ],

    'media' => [
        'max_file_size' => 10240, // in KB
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'],
        'collections' => [
            'featured_image',
            'gallery',
            'attachments',
        ],
    ],

    'seo' => [
        'enable_sitemap' => true,
        'enable_schema_markup' => true,
        'default_robots' => 'index, follow',
        'og_image_fallback' => null,
        'twitter_card_type' => 'summary_large_image',
    ],

    'cache' => [
        'enabled' => false,
        'ttl' => 3600,
    ],
];
