<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MenuItem extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'menu_id',
        'title',
        'url',
        'target',
        'parent_id',
        'order',
        'type',
        'linkable_id',
        'linkable_type',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the menu that this item belongs to.
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    /**
     * Get the parent menu item.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    /**
     * Get the child menu items.
     */
    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('order');
    }

    /**
     * Get the linkable model (Post, Category, Page, etc).
     */
    public function linkable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the URL for this menu item.
     */
    public function getUrlAttribute($value)
    {
        if ($this->type === 'custom') {
            return $value;
        }

        if ($this->linkable) {
            return match ($this->linkable_type) {
                'App\Models\Post' => route('api.v1.posts.show', $this->linkable->slug),
                'App\Models\Category' => route('api.v1.categories.show', $this->linkable->slug),
                default => $value,
            };
        }

        return $value;
    }

    protected static function booted(): void
    {
        static::creating(function ($item) {
            if (!$item->order) {
                $item->order = static::where('menu_id', $item->menu_id)
                    ->max('order') + 1;
            }
        });
    }
}
