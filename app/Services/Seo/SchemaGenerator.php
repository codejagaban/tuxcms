<?php

namespace App\Services\Seo;

use App\Models\Page;
use App\Support\Site;

/**
 * Builds the JSON-LD @graph for a page.
 *
 * A manually-authored `seo.schema_markup` replaces this entirely, so an editor
 * who needs something bespoke isn't fighting generated output.
 */
class SchemaGenerator
{
    public function forPage(Page $page): array
    {
        if (!empty($page->seo?->schema_markup)) {
            return $page->seo->schema_markup;
        }

        $siteUrl = Site::url();

        $graph = [
            $this->organization($siteUrl),
            $this->webSite($siteUrl),
            $this->webPage($page, $siteUrl),
        ];

        if ($breadcrumbs = $this->breadcrumbs($page)) {
            $graph[] = $breadcrumbs;
        }

        if ($faq = $this->faq($page)) {
            $graph[] = $faq;
        }

        return [
            '@context' => 'https://schema.org',
            '@graph' => array_values(array_filter($graph)),
        ];
    }

    private function organization(string $siteUrl): array
    {
        $contact = Site::contact();

        $org = [
            '@type' => 'Organization',
            '@id' => $siteUrl . '/#organization',
            'name' => Site::name(),
            'url' => $siteUrl . '/',
        ];

        if ($logo = Site::logo()) {
            $org['logo'] = $logo;
        }

        if ($social = array_values(Site::socialLinks())) {
            $org['sameAs'] = $social;
        }

        if (!empty($contact['email']) || !empty($contact['phone'])) {
            $org['contactPoint'] = array_filter([
                '@type' => 'ContactPoint',
                'contactType' => 'customer service',
                'email' => $contact['email'] ?? null,
                'telephone' => $contact['phone'] ?? null,
            ]);
        }

        return $org;
    }

    private function webSite(string $siteUrl): array
    {
        return array_filter([
            '@type' => 'WebSite',
            '@id' => $siteUrl . '/#website',
            'name' => Site::name(),
            'url' => $siteUrl . '/',
            'description' => Site::description(),
            'publisher' => ['@id' => $siteUrl . '/#organization'],
        ]);
    }

    private function webPage(Page $page, string $siteUrl): array
    {
        $url = Site::urlFor($page);

        return array_filter([
            '@type' => 'WebPage',
            '@id' => $url . '#webpage',
            'url' => $url,
            'name' => $page->seo?->meta_title ?: $page->title,
            'description' => $page->seo?->meta_description ?: $page->excerpt,
            'isPartOf' => ['@id' => $siteUrl . '/#website'],
            'datePublished' => $page->published_at?->toAtomString(),
            'dateModified' => $page->updated_at?->toAtomString(),
            'inLanguage' => str_replace('_', '-', app()->getLocale()),
        ]);
    }

    private function breadcrumbs(Page $page): ?array
    {
        $ancestors = $page->ancestors();

        if ($ancestors->isEmpty() || $page->is_homepage) {
            return null;
        }

        $items = [];
        $position = 1;

        $items[] = [
            '@type' => 'ListItem',
            'position' => $position++,
            'name' => 'Home',
            'item' => Site::url() . '/',
        ];

        foreach ($ancestors as $ancestor) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'name' => $ancestor->navLabel(),
                'item' => Site::urlFor($ancestor),
            ];
        }

        $items[] = [
            '@type' => 'ListItem',
            'position' => $position,
            'name' => $page->title,
            'item' => Site::urlFor($page),
        ];

        return [
            '@type' => 'BreadcrumbList',
            'itemListElement' => $items,
        ];
    }

    /** FAQPage markup when the page carries a visible faq section. */
    private function faq(Page $page): ?array
    {
        $section = $page->sections
            ->first(fn($s) => $s->type === 'faq' && $s->is_visible);

        if (!$section) {
            return null;
        }

        $items = collect($section->data['items'] ?? [])
            ->filter(fn($i) => !empty($i['question']) && !empty($i['answer']));

        if ($items->isEmpty()) {
            return null;
        }

        return [
            '@type' => 'FAQPage',
            'mainEntity' => $items->map(fn($item) => [
                '@type' => 'Question',
                'name' => $item['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $item['answer'],
                ],
            ])->values()->all(),
        ];
    }
}
