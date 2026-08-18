<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'key',
        'value',
        'group',
    ];

    protected $casts = [
        'value' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get a setting by key.
     */
    public static function get($key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set a setting value.
     */
    public static function set($key, $value, $group = 'general')
    {
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group]
        );
    }

    /**
     * Get all settings grouped.
     */
    public static function getGrouped()
    {
        return static::all()->groupBy('group')->mapWithKeys(function ($items, $group) {
            return [$group => $items->pluck('value', 'key')];
        });
    }

    /**
     * Get settings by group.
     */
    public static function getByGroup($group)
    {
        return static::where('group', $group)->get()->pluck('value', 'key');
    }
}
