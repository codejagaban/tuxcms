<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Seo extends Model
{
    use HasFactory;

    protected $table = 'seos';

    protected $fillable = [
        'seoable_id',
        'seoable_type',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_title',
        'og_description',
        'og_image',
        'og_type',
        'twitter_card',
        'twitter_title',
        'twitter_description',
        'twitter_image',
        'canonical_url',
        'robots',
        'schema_markup',
        'custom_head',
    ];

    protected $casts = [
        'schema_markup' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the model that owns the SEO metadata.
     */
    public function seoable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Generate default meta title if not set.
     */
    public function getMetaTitleAttribute($value)
    {
        return $value ?? $this->seoable?->title ?? $this->seoable?->name ?? '';
    }

    /**
     * Generate default meta description if not set.
     */
    public function getMetaDescriptionAttribute($value)
    {
        return $value ?? $this->seoable?->excerpt ?? substr($this->seoable?->content ?? '', 0, 160) ?? '';
    }

    /**
     * Get the Open Graph image URL.
     */
    public function getOgImageUrl()
    {
        return $this->og_image ?? $this->seoable?->featured_image ?? null;
    }

    /**
     * Generate JSON-LD schema.
     */
    public function getSchemaJson()
    {
        if ($this->schema_markup) {
            return json_encode($this->schema_markup, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        return null;
    }
}
