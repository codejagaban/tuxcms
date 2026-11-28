<?php

namespace App\Services\Seo;

use App\Models\Page;
use App\Models\Post;
use App\Models\Setting;
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

    /** JSON-LD for a blog post: the site, plus the post as a BlogPosting. */
    public function forPost(Post $post): array
    {
        $siteUrl = Site::url();
        $url = $siteUrl.$post->path();

        return [
            '@context' => 'https://schema.org',
            '@graph' => [
                $this->organization($siteUrl),
                $this->webSite($siteUrl),
                array_filter([
                    '@type' => 'BlogPosting',
                    '@id' => $url.'#article',
                    'headline' => $post->title,
                    'description' => $post->meta_description ?: $post->summary(160),
                    'image' => Site::absolute($post->cover_image) ?: Site::defaultOgImage(),
                    'datePublished' => $post->published_at?->toAtomString(),
                    'dateModified' => $post->updated_at?->toAtomString(),
                    'mainEntityOfPage' => $url,
                    'author' => $post->author
                        ? ['@type' => 'Person', 'name' => $post->author->name]
                        : ['@id' => $siteUrl.'/#organization'],
                    'publisher' => ['@id' => $siteUrl.'/#organization'],
                    'isPartOf' => ['@id' => $siteUrl.'/#website'],
                    'inLanguage' => str_replace('_', '-', Setting::getString('site_locale') ?: app()->getLocale()),
                ]),
            ],
        ];
    }

    private function organization(string $siteUrl): array
    {
        $contact = Site::contact();

        $org = [
            // Settings can name a more specific schema.org type, such as
            // EmploymentAgency or LocalBusiness.
            '@type' => Setting::getString('organization_type') ?: 'Organization',
            '@id' => $siteUrl . '/#organization',
            'name' => Site::name(),
            'url' => $siteUrl . '/',
        ];

        if ($logo = Site::schemaLogo()) {
            $org['logo'] = $logo;
        }

        if ($description = Site::description()) {
            $org['description'] = $description;
        }

        if (!empty($contact['address'])) {
            $org['address'] = $this->postalAddress($contact['address']);
        }

        if ($image = Site::defaultOgImage()) {
            $org['image'] = $image;
        }

        if ($areas = Setting::getString('area_served')) {
            $org['areaServed'] = array_values(array_filter(array_map('trim', explode(',', $areas))));
        }

        if ($social = array_values(Site::socialLinks())) {
            $org['sameAs'] = $social;
        }

        if (!empty($contact['email']) || !empty($contact['phone'])) {
            $org['contactPoint'] = array_filter([
                '@type' => 'ContactPoint',
                'contactType' => 'customer service',
                'email' => $contact['email'] ?? null,
                'telephone' => $this->internationalPhone($contact['phone'] ?? null),
            ]);
        }

        return $org;
    }

    /**
     * "Street, Town, Postcode" becomes a PostalAddress. Anything that does not
     * fit that shape is left as the plain text the editor typed.
     */
    private function postalAddress(string $address): array|string
    {
        $parts = array_values(array_filter(array_map('trim', explode(',', $address))));

        if (count($parts) < 3) {
            return $address;
        }

        $postcode = array_pop($parts);
        $town = array_pop($parts);

        return array_filter([
            '@type' => 'PostalAddress',
            'streetAddress' => implode(', ', $parts),
            'addressLocality' => $town,
            'postalCode' => $postcode,
            'addressCountry' => Setting::getString('address_country'),
        ]);
    }

    /** A UK number typed with a leading 0 is given its +44 form. */
    private function internationalPhone(?string $phone): ?string
    {
        if (! $phone) {
            return null;
        }

        $digits = preg_replace('/[^0-9+]/', '', $phone);

        if (Setting::getString('address_country') === 'GB' && str_starts_with($digits, '0')) {
            return '+44'.substr($digits, 1);
        }

        return $digits;
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
            'inLanguage' => str_replace('_', '-', Setting::getString('site_locale') ?: app()->getLocale()),
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
