<?php

namespace App\Models;

use App\Support\PublishState;
use App\Support\RichText;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use SoftDeletes;

    /** Posts shown on each page of the blog listing. */
    public const PER_PAGE = 9;

    protected $fillable = [
        'title', 'slug', 'excerpt', 'body', 'cover_image', 'cover_image_alt',
        'post_category_id', 'author_id', 'status', 'published_at',
        'meta_title', 'meta_description',
    ];

    protected function casts(): array
    {
        return ['published_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        // The body is published as written, so it is cleaned on the way in:
        // whatever reaches the table is safe to print into a page.
        static::saving(function (Post $post): void {
            if ($post->isDirty('body')) {
                $post->body = app(RichText::class)->clean($post->body);
            }
        });
        static::saved(fn () => PublishState::markChanged());
        static::deleted(fn () => PublishState::markChanged());
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PostCategory::class, 'post_category_id');
    }

    /** Published, and not scheduled for later. */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->where(fn (Builder $q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    /** Published, but with a date still to come. */
    public function scopeScheduled(Builder $query): Builder
    {
        return $query->where('status', 'published')->where('published_at', '>', now());
    }

    public function isScheduled(): bool
    {
        return $this->status === 'published' && $this->published_at?->isFuture() === true;
    }

    /** The summary shown in listings: the excerpt, or the start of the body. */
    public function summary(int $limit = 180): string
    {
        if (filled($this->excerpt)) {
            return trim($this->excerpt);
        }

        $text = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags(str_replace('<', ' <', (string) $this->body)), ENT_QUOTES | ENT_HTML5)));

        return mb_strlen($text) > $limit ? rtrim(mb_substr($text, 0, $limit - 1)).'…' : $text;
    }

    /** Whole minutes to read at about 220 words a minute; never less than one. */
    public function readingMinutes(): int
    {
        return max(1, (int) ceil(str_word_count(strip_tags((string) $this->body)) / 220));
    }

    public function path(): string
    {
        return '/blog/'.$this->slug.'/';
    }
}
