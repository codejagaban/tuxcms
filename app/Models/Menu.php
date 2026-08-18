<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'name',
        'slug',
        'location',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the menu items for this menu.
     */
    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class)->whereNull('parent_id')->orderBy('order');
    }

    /**
     * Get all menu items including nested ones.
     */
    public function allItems(): HasMany
    {
        return $this->hasMany(MenuItem::class)->orderBy('order');
    }

    /**
     * Get menu items with their nested structure.
     */
    public function getNestedItems()
    {
        return $this->items()->with(['children' => function ($query) {
            $query->orderBy('order');
        }])->get();
    }
}
