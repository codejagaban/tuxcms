<?php

namespace App\Traits;

use App\Models\Seo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasSeo
{
    /**
     * Get the SEO metadata for this model.
     */
    public function seo(): MorphOne
    {
        return $this->morphOne(Seo::class, 'seoable');
    }

    /**
     * Get or create SEO metadata.
     */
    public function getSeoData()
    {
        return $this->seo ?? $this->seo()->create();
    }

    /**
     * Update SEO metadata.
     */
    public function updateSeo(array $data)
    {
        return $this->seo()->updateOrCreate(
            ['seoable_id' => $this->id, 'seoable_type' => static::class],
            $data
        );
    }

    /**
     * Get the canonical URL.
     */
    public function getCanonicalUrl()
    {
        $seo = $this->seo;

        if ($seo && $seo->canonical_url) {
            return $seo->canonical_url;
        }

        return match (static::class) {
            'App\Models\Post' => route('api.v1.posts.show', $this->slug),
            'App\Models\Category' => route('api.v1.categories.show', $this->slug),
            default => url()->current(),
        };
    }

    /**
     * Get Open Graph tags as array.
     */
    public function getOgTags()
    {
        $seo = $this->seo;

        return [
            'og:title' => $seo?->og_title ?? $this->title ?? $this->name,
            'og:description' => $seo?->og_description ?? $this->excerpt ?? substr($this->content ?? '', 0, 160),
            'og:image' => $seo?->og_image ?? $this->featured_image,
            'og:type' => $seo?->og_type ?? 'website',
            'og:url' => $this->getCanonicalUrl(),
        ];
    }

    /**
     * Get Twitter Card tags as array.
     */
    public function getTwitterTags()
    {
        $seo = $this->seo;

        return [
            'twitter:card' => $seo?->twitter_card ?? 'summary_large_image',
            'twitter:title' => $seo?->twitter_title ?? $this->title ?? $this->name,
            'twitter:description' => $seo?->twitter_description ?? $this->excerpt ?? substr($this->content ?? '', 0, 160),
            'twitter:image' => $seo?->twitter_image ?? $this->featured_image,
        ];
    }

    /**
     * Get meta tags as array.
     */
    public function getMetaTags()
    {
        $seo = $this->seo;

        return [
            'title' => $seo?->meta_title ?? $this->title ?? $this->name,
            'description' => $seo?->meta_description ?? $this->excerpt ?? substr($this->content ?? '', 0, 160),
            'keywords' => $seo?->meta_keywords,
            'canonical' => $this->getCanonicalUrl(),
            'robots' => $seo?->robots ?? 'index, follow',
        ];
    }

    /**
     * Get JSON-LD schema markup.
     */
    public function getSchemaMarkup()
    {
        $seo = $this->seo;

        if (!$seo || !$seo->schema_markup) {
            // Generate default schema based on model type
            return $this->generateDefaultSchema();
        }

        return $seo->schema_markup;
    }

    /**
     * Generate default schema markup for the model.
     */
    private function generateDefaultSchema()
    {
        return match (static::class) {
            'App\Models\Post' => [
                '@context' => 'https://schema.org',
                '@type' => 'BlogPosting',
                'headline' => $this->title,
                'description' => $this->excerpt ?? substr($this->content ?? '', 0, 160),
                'image' => $this->featured_image,
                'datePublished' => $this->published_at?->toIso8601String(),
                'dateModified' => $this->updated_at->toIso8601String(),
                'author' => [
                    '@type' => 'Person',
                    'name' => $this->author?->name,
                ],
            ],
            'App\Models\Category' => [
                '@context' => 'https://schema.org',
                '@type' => 'CollectionPage',
                'name' => $this->name,
                'description' => $this->description,
                'url' => $this->getCanonicalUrl(),
            ],
            default => [],
        };
    }
}
