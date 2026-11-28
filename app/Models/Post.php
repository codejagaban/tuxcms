<?php

namespace App\Models;

use App\Support\PublishState;
use App\Support\RichText;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Tags\HasTags;

class Post extends Model
{
    use HasTags, SoftDeletes;

    /** The tag type that keeps blog tags apart from any other tags. */
    public const TAG_TYPE = 'blog';

    /** Addresses under /blog/ that belong to the listing, not to a post. */
    public const RESERVED_SLUGS = ['page', 'tag', 'feed'];

    /** Posts shown on each page of the blog listing. */
    public const PER_PAGE = 9;

    protected $fillable = [
        'title', 'slug', 'excerpt', 'body', 'cover_image', 'cover_image_alt',
        'author_id', 'status', 'published_at',
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

            // A post saved without an address keeps the one it has, or gets
            // one made from its title.
            if (blank($post->slug)) {
                $post->slug = $post->getOriginal('slug') ?: self::uniqueSlug((string) $post->title, $post->id);
            }

            // Published without an excerpt, it gets one from its opening words,
            // so listings, feeds and search results have something to show.
            if ($post->status === 'published' && blank($post->excerpt)) {
                $post->excerpt = $post->excerptFromBody();
            }
        });
        static::saved(fn () => PublishState::markChanged());
        static::deleted(fn () => PublishState::markChanged());
    }

    /**
     * An address made from the title that no other post, even a deleted one,
     * already has and that the blog does not use for itself: "notes", then
     * "notes-2", "notes-3" and so on.
     */
    public static function uniqueSlug(string $title, ?int $ignore = null): string
    {
        $base = Str::limit(Str::slug($title), 200, '') ?: 'post';
        $taken = static::withTrashed()
            ->when($ignore, fn (Builder $query) => $query->whereKeyNot($ignore))
            ->where(fn (Builder $query) => $query->where('slug', $base)->orWhere('slug', 'like', $base.'-%'))
            ->pluck('slug')
            ->merge(self::RESERVED_SLUGS)
            ->flip();

        $slug = $base;
        for ($n = 2; isset($taken[$slug]); $n++) {
            $slug = $base.'-'.$n;
        }

        return $slug;
    }

    /**
     * An excerpt from the body: whole sentences from its start, up to about
     * 200 characters, or failing that the opening words with an ellipsis.
     * Null when the body has no words.
     */
    public function excerptFromBody(int $limit = 200): ?string
    {
        // Headings and code read badly out of place, so the excerpt comes from
        // the writing around them, unless that is all there is.
        $body = (string) $this->body;
        $prose = preg_replace('#<(h[1-6]|pre|figcaption)\b[^>]*>.*?</\1>#is', '', $body);
        $text = self::plainText($prose) ?: self::plainText($body);

        if ($text === '') {
            return null;
        }

        if (mb_strlen($text) <= $limit) {
            return $text;
        }

        $window = mb_substr($text, 0, $limit);
        if (preg_match('/^.*[.!?…](?=\s)/us', $window, $match) && mb_strlen($match[0]) >= 60) {
            return $match[0];
        }

        return rtrim(mb_substr($window, 0, mb_strrpos($window, ' ') ?: $limit), ' ,;:.').'…';
    }

    /** Words only: each block ends as a sentence, so blocks never run together. */
    private static function plainText(string $html): string
    {
        $html = preg_replace('#</(p|h[1-6]|li|blockquote|pre|figcaption)>#i', '. ', $html);
        $text = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags(str_replace('<', ' <', $html)), ENT_QUOTES | ENT_HTML5)));
        // A block that already ended its sentence does not need another stop.
        $text = trim(preg_replace('/([.!?…:])\s*\.(?=\s|$)/u', '$1', $text));

        return preg_match('/[\p{L}\p{N}]/u', $text) ? $text : '';
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(PostRevision::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /** The post's tags, by name. */
    public function tagNames(): array
    {
        return $this->tags->where('type', self::TAG_TYPE)->pluck('name')->values()->all();
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
