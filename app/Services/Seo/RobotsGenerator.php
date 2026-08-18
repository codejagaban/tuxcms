<?php

namespace App\Services\Seo;

use App\Models\Setting;
use App\Support\Site;

class RobotsGenerator
{
    public function generate(): string
    {
        // Never let a staging build get indexed.
        if (Site::isNoindex()) {
            return "User-agent: *\nDisallow: /\n";
        }

        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin/',
            'Disallow: /api/',
        ];

        if ($extra = Setting::getString('robots_extra')) {
            $lines[] = trim($extra);
        }

        $lines[] = '';
        $lines[] = 'Sitemap: ' . Site::url() . '/sitemap.xml';

        return implode("\n", $lines) . "\n";
    }
}
