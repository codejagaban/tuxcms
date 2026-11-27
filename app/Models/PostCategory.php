<?php

namespace App\Models;

use App\Support\PublishState;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PostCategory extends Model
{
    protected $fillable = ['name', 'slug'];

    protected static function booted(): void
    {
        static::saved(fn () => PublishState::markChanged());
        static::deleted(fn () => PublishState::markChanged());
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}
