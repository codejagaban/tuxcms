<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

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
     * Get a setting as a plain string.
     *
     * `value` is cast to array, and values have historically been stored
     * wrapped (`['Lumen Studio']`). Templates want the scalar, so unwrap
     * single-element arrays defensively rather than trusting the shape.
     */
    public static function getString(string $key, ?string $default = null): ?string
    {
        $value = static::get($key);

        if (is_array($value)) {
            $value = reset($value);
        }

        return ($value === null || $value === '') ? $default : (string) $value;
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
