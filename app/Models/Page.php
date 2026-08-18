<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasSeo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Page extends Model implements HasMedia
{
    use HasFactory, HasSlug, HasSeo, InteractsWithMedia, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'excerpt',
        'template',
        'status',
        'parent_id',
        'order',
        'is_homepage',
        'custom_fields',
        'published_at',
        'author_id',
    ];

    protected $casts = [
        'custom_fields' => 'array',
        'is_homepage' => 'boolean',
        'published_at' => 'datetime',
        'order' => 'integer',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    // ── Relationships ──────────────────────────────────────

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function parent()
    {
        return $this->belongsTo(Page::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Page::class, 'parent_id')->orderBy('order');
    }

    public function sections()
    {
        return $this->hasMany(PageSection::class)->orderBy('order');
    }

    // ── Scopes ─────────────────────────────────────────────

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeByTemplate($query, string $template)
    {
        return $query->where('template', $template);
    }

    // ── Media Collections ──────────────────────────────────

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('featured_image')->singleFile();
        $this->addMediaCollection('gallery');
        $this->addMediaCollection('files');
    }

    // ── Helpers ────────────────────────────────────────────

    public function isPublished(): bool
    {
        return $this->status === 'published'
            && ($this->published_at === null || $this->published_at->lte(now()));
    }

    public function getFullPath(): string
    {
        $segments = collect([$this->slug]);
        $parent = $this->parent;

        while ($parent) {
            $segments->prepend($parent->slug);
            $parent = $parent->parent;
        }

        return '/' . $segments->implode('/');
    }
}
