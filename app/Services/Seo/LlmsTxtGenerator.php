<?php

namespace App\Services\Seo;

use App\Models\Setting;
use App\Support\Site;
use Illuminate\Support\Collection;

/**
 * Writes /llms.txt: a plain-language summary of the site for AI assistants
 * and answer engines, following the llmstxt.org layout. It is generated from
 * the same published pages as the sitemap, so it can never drift from them.
 */
class LlmsTxtGenerator
{
    public function generate(Collection $pages, Collection $jobs): string
    {
        $lines = ['# ' . Site::name(), ''];

        if ($description = Site::description()) {
            $lines[] = '> ' . $description;
            $lines[] = '';
        }

        $facts = array_filter([
            'Areas served' => Setting::getString('area_served'),
            'Phone' => collect([Setting::getString('contact_phone'), Setting::getString('contact_phone_2')])->filter()->implode(', '),
            'Email' => Site::contact()['email'] ?? null,
            'Registered office' => Setting::getString('contact_address'),
        ]);

        foreach ($facts as $label => $value) {
            $lines[] = "- {$label}: {$value}";
        }

        if ($facts) {
            $lines[] = '';
        }

        $lines[] = '## Pages';
        $lines[] = '';

        foreach ($pages as $page) {
            if (($page->seo?->robots ?? '') && str_contains($page->seo->robots, 'noindex')) {
                continue;
            }

            $summary = $page->seo?->meta_description ?: $page->excerpt;
            $lines[] = '- [' . $page->title . '](' . Site::urlFor($page) . ')' . ($summary ? ': ' . $summary : '');
        }

        if ($jobs->isNotEmpty()) {
            $lines[] = '';
            $lines[] = '## Vacancies';
            $lines[] = '';

            foreach ($jobs as $job) {
                $lines[] = '- [' . $job->title . '](' . Site::url() . '/careers/' . $job->slug . '/)' . ($job->summary ? ': ' . $job->summary : '');
            }
        }

        return implode("\n", $lines) . "\n";
    }
}
