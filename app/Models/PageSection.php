<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class PageSection extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'page_id',
        'key',
        'type',
        'title',
        'content',
        'data',
        'order',
        'is_visible',
    ];

    protected $casts = [
        'data' => 'array',
        'is_visible' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Available section types:
     * - hero: Hero banner with heading, subheading, CTA, background image
     * - text: Rich text content block
     * - image_text: Image alongside text (left/right layout)
     * - gallery: Image gallery/grid
     * - cta: Call to action block
     * - features: Feature cards/grid (icon + title + description)
     * - testimonials: Testimonial slider/grid
     * - team: Team member cards
     * - faq: Accordion FAQ section
     * - contact: Contact information block
     * - form: Embedded form reference
     * - map: Map embed
     * - video: Video embed
     * - stats: Statistics/counters
     * - pricing: Pricing cards
     * - custom: Custom HTML/component block
     */

    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('section_images');
        $this->addMediaCollection('section_background')->singleFile();
    }
}
