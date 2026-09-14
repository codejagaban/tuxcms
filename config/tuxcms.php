<?php

return [

    'pages' => [
        'per_page' => 15,
        'templates' => ['default', 'landing', 'contact', 'about', 'services', 'blank', 'crystal'],
        'section_types' => [
            'hero', 'text', 'features', 'stats', 'testimonials', 'team',
            'faq', 'cta', 'contact', 'contact_form', 'map',
        ],
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

];
