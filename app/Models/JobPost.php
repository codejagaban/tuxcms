<?php

namespace App\Models;

use App\Support\PublishState;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobPost extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title', 'slug', 'location', 'job_type', 'hours', 'salary',
        'employment_type', 'summary', 'description', 'responsibilities',
        'essential', 'desirable', 'benefits', 'application_email', 'status',
        'published_at', 'closes_at', 'author_id',
    ];

    protected function casts(): array
    {
        return [
            'responsibilities' => 'array',
            'essential' => 'array',
            'desirable' => 'array',
            'benefits' => 'array',
            'published_at' => 'datetime',
            'closes_at' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => PublishState::markChanged());
        static::deleted(fn () => PublishState::markChanged());
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->where(fn (Builder $q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->where(fn (Builder $q) => $q->whereNull('closes_at')->orWhere('closes_at', '>=', today()));
    }
}
