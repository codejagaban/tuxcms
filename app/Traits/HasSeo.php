<?php

namespace App\Traits;

use App\Models\Seo;
use App\Services\Seo\SchemaGenerator;
use App\Models\Setting;
use App\Support\Site;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasSeo
{
    public function seo(): MorphOne
    {
        return $this->morphOne(Seo::class, 'seoable');
    }

    public function getSeoData()
    {
        return $this->seo ?? $this->seo()->create();
    }

    public function updateSeo(array $data)
    {
        return $this->seo()->updateOrCreate(
            ['seoable_id' => $this->id, 'seoable_type' => static::class],
            $data
        );
    }

    /**
     * The public canonical URL. Built from the configured site URL and the
     * page's materialized path — never from the current request, which during
     * a CLI build points nowhere useful.
     */
    public function getCanonicalUrl(): string
    {
        return $this->seo?->canonical_url ?: Site::urlFor($this);
    }

    /** Best available social share image, as an absolute URL. */
    public function getShareImage(): ?string
    {
        $candidates = [
            $this->seo?->og_image,
            $this->relationLoaded('media') || $this->exists
                ? ($this->getFirstMediaUrl('featured_image') ?: null)
                : null,
            Site::defaultOgImage(),
        ];

        foreach ($candidates as $candidate) {
            if (!empty($candidate)) {
                return Site::absolute($candidate);
            }
        }

        return null;
    }

    public function getMetaTags(): array
    {
        $seo = $this->seo;

        return array_filter([
            'title' => $seo?->meta_title ?: $this->title,
            'description' => $seo?->meta_description ?: $this->excerpt,
            'keywords' => $seo?->meta_keywords,
            'canonical' => $this->getCanonicalUrl(),
            'robots' => $seo?->robots ?: 'index, follow',
        ]);
    }

    public function getOgTags(): array
    {
        $seo = $this->seo;

        return array_filter([
            'og:site_name' => Site::name(),
            'og:title' => $seo?->og_title ?: $seo?->meta_title ?: $this->title,
            'og:description' => $seo?->og_description ?: $seo?->meta_description ?: $this->excerpt,
            'og:image' => $image = $this->getShareImage(),
            ...$this->shareImageDetails($image),
            'og:type' => $seo?->og_type ?: 'website',
            'og:url' => $this->getCanonicalUrl(),
            'og:locale' => Setting::getString('site_locale'),
        ]);
    }

    /**
     * Width, height and alt text for the share image. Sizes are read from the
     * file itself, so they are only given for images this site hosts.
     */
    private function shareImageDetails(?string $image): array
    {
        if (! $image) {
            return [];
        }

        $details = [];
        $base = rtrim(Site::url(), '/');

        if (str_starts_with($image, $base.'/')) {
            $file = public_path(ltrim(parse_url(substr($image, strlen($base)), PHP_URL_PATH) ?: '', '/'));

            if (is_file($file) && ($size = @getimagesize($file))) {
                $details['og:image:width'] = (string) $size[0];
                $details['og:image:height'] = (string) $size[1];
            }
        }

        // The alt text setting describes the site-wide default image only.
        if ($image === Site::defaultOgImage()) {
            $details['og:image:alt'] = Setting::getString('default_og_image_alt');
        }

        return $details;
    }

    public function getTwitterTags(): array
    {
        $seo = $this->seo;

        return array_filter([
            'twitter:card' => $seo?->twitter_card ?: 'summary_large_image',
            'twitter:title' => $seo?->twitter_title ?: $seo?->meta_title ?: $this->title,
            'twitter:description' => $seo?->twitter_description ?: $seo?->meta_description ?: $this->excerpt,
            'twitter:image' => $seo?->twitter_image ? Site::absolute($seo->twitter_image) : $this->getShareImage(),
        ]);
    }

    /** JSON-LD graph for this page. */
    public function getSchemaMarkup(): array
    {
        return app(SchemaGenerator::class)->forPage($this);
    }

    /** True when this page asks search engines not to index it. */
    public function isNoindex(): bool
    {
        return str_contains(strtolower($this->seo?->robots ?? ''), 'noindex');
    }
}
