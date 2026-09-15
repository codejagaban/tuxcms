<?php

namespace App\Services\Seo;

use App\Models\JobPost;
use App\Models\Page;
use App\Support\Site;

class SitemapGenerator
{
    /** Build sitemap.xml for every indexable published page. */
    public function generate(): string
    {
        $pages = Page::published()
            ->with('seo')
            ->orderBy('path')
            ->get()
            ->reject(fn (Page $page) => $page->isNoindex());

        $lines = [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">',
        ];

        foreach ($pages as $page) {
            $lines[] = '  <url>';
            $lines[] = '    <loc>'.e(Site::urlFor($page)).'</loc>';

            if ($page->updated_at) {
                $lines[] = '    <lastmod>'.$page->updated_at->toAtomString().'</lastmod>';
            }

            $lines[] = '    <changefreq>weekly</changefreq>';
            $lines[] = '    <priority>'.$this->priority($page).'</priority>';
            $lines[] = '  </url>';
        }

        $jobs = JobPost::published()->orderBy('slug')->get();
        if ($jobs->isNotEmpty()) {
            $lines[] = $this->url('/careers/', $jobs->max('updated_at'));
            foreach ($jobs as $job) {
                $lines[] = $this->url('/careers/'.$job->slug.'/', $job->updated_at);
            }
        }

        $lines[] = '</urlset>';

        return implode("\n", $lines)."\n";
    }

    private function url(string $path, $updatedAt): string
    {
        $base = rtrim(Site::url(), '/');
        $lastModified = $updatedAt ? "\n    <lastmod>".e($updatedAt->toAtomString()).'</lastmod>' : '';

        return '  <url>'."\n    <loc>".e($base.$path).'</loc>'.$lastModified."\n    <changefreq>weekly</changefreq>\n    <priority>0.7</priority>\n  </url>";
    }

    /** Homepage outranks top-level pages, which outrank deeper ones. */
    private function priority(Page $page): string
    {
        if ($page->is_homepage) {
            return '1.0';
        }

        $depth = substr_count(trim($page->getFullPath(), '/'), '/');

        return $depth === 0 ? '0.8' : '0.6';
    }
}
