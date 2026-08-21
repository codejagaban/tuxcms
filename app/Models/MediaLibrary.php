<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Owns media uploaded to the site library — images that aren't attached to a
 * particular page (logos, share images, downloads reused across the site).
 *
 * A singleton: there is only ever one row.
 */
class MediaLibrary extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $table = 'media_libraries';

    protected $guarded = [];

    public static function singleton(): self
    {
        return static::firstOrCreate([]);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('library');
    }
}
