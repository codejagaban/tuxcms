<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Tenant extends Model
{
    use HasFactory, HasSlug;

    protected $fillable = [
        'name',
        'slug',
        'domain',
        'api_key',
        'owner_id',
        'plan',
        'settings',
        'is_active',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'api_key',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    protected static function booted(): void
    {
        static::creating(function (Tenant $tenant) {
            if (empty($tenant->api_key)) {
                $tenant->api_key = 'tux_' . Str::random(40);
            }
        });
    }

    // ── Relationships ──────────────────────────────────────

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'tenant_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function pages()
    {
        return $this->hasMany(Page::class);
    }

    public function menus()
    {
        return $this->hasMany(Menu::class);
    }

    public function forms()
    {
        return $this->hasMany(Form::class);
    }

    public function siteSettings()
    {
        return $this->hasMany(Setting::class);
    }

    // ── Scopes ─────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ── Helpers ────────────────────────────────────────────

    public function regenerateApiKey(): string
    {
        $this->api_key = 'tux_' . Str::random(40);
        $this->save();

        return $this->api_key;
    }
}
