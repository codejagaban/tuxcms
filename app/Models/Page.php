<?php

namespace App\Models;

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
    use HasFactory, HasSlug, HasSeo, InteractsWithMedia, SoftDeletes;

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
        'show_in_nav',
        'nav_label',
        'custom_fields',
        'published_at',
        'author_id',
    ];

    protected $casts = [
        'custom_fields' => 'array',
        'is_homepage' => 'boolean',
        'show_in_nav' => 'boolean',
        'published_at' => 'datetime',
        'order' => 'integer',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug')
            // Slugs only need to be unique among siblings, matching the
            // unique(parent_id, slug) constraint. Without this scope Spatie
            // would suffix globally and turn /about/design into /about/design-1
            // just because /services/design already exists.
            ->extraScope(fn ($builder) => $builder->where('parent_id', $this->parent_id))
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

    public function revisions()
    {
        return $this->hasMany(PageRevision::class);
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

    /** Pages that should appear in the derived site navigation. */
    public function scopeInNav($query)
    {
        return $query->where('show_in_nav', true)->orderBy('order');
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

    /**
     * The page's URL path. Reads the materialized `path` column, falling back
     * to walking the parent chain if it hasn't been computed yet.
     */
    public function getFullPath(): string
    {
        return $this->path ?: $this->computePath();
    }

    /**
     * Build the path from the parent chain. The homepage is always '/',
     * whatever its slug happens to be.
     */
    public function computePath(): string
    {
        if ($this->is_homepage) {
            return '/';
        }

        $segments = collect([$this->slug]);
        $parent = $this->parent;

        while ($parent) {
            $segments->prepend($parent->slug);
            $parent = $parent->parent;
        }

        return '/' . $segments->implode('/');
    }

    /** The label to show in navigation. */
    public function navLabel(): string
    {
        return $this->nav_label ?: $this->title;
    }

    /** Ancestor chain, root first — used for breadcrumbs. */
    public function ancestors(): \Illuminate\Support\Collection
    {
        $chain = collect();
        $parent = $this->parent;

        while ($parent) {
            $chain->prepend($parent);
            $parent = $parent->parent;
        }

        return $chain;
    }
}
