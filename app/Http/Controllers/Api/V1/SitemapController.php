<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $pages = Page::published()->select('slug', 'updated_at', 'is_homepage')->get();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($pages as $page) {
            $priority = $page->is_homepage ? '1.0' : '0.8';
            $xml .= $this->urlEntry(
                url('/') . '/' . $page->slug,
                $page->updated_at,
                $priority
            );
        }

        $xml .= '</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=utf-8']);
    }

    private function urlEntry(string $loc, $lastmod, string $priority = '0.8'): string
    {
        return '    <url>' . "\n"
            . '        <loc>' . htmlspecialchars($loc) . '</loc>' . "\n"
            . '        <lastmod>' . $lastmod->toIso8601String() . '</lastmod>' . "\n"
            . '        <changefreq>weekly</changefreq>' . "\n"
            . '        <priority>' . $priority . '</priority>' . "\n"
            . '    </url>' . "\n";
    }
}
