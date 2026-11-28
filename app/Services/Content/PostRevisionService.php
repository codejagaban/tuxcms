<?php

namespace App\Services\Content;

use App\Models\Post;
use App\Models\PostRevision;
use App\Models\User;

/**
 * Keeps the last 30 states of a post, each taken just before a save changed
 * it, as pages do.
 *
 * Drafts also save themselves while they are written. Those autosaves keep
 * at most one state every ten minutes for each person, so the history holds
 * steps worth going back to rather than every few words.
 */
class PostRevisionService
{
    public const KEEP = 30;

    public const AUTOSAVE_GAP_MINUTES = 10;

    public function snapshot(Post $post): array
    {
        $post->loadMissing('tags');

        return [
            'title' => $post->title,
            'slug' => $post->slug,
            'excerpt' => $post->excerpt ?? '',
            'body' => $post->body ?? '',
            'cover_image' => $post->cover_image ?? '',
            'cover_image_alt' => $post->cover_image_alt ?? '',
            'tags' => $post->tagNames(),
            'status' => $post->status,
            'published_at' => $post->published_at?->toIso8601String(),
            'meta_title' => $post->meta_title ?? '',
            'meta_description' => $post->meta_description ?? '',
        ];
    }

    public function capture(Post $post, array $before, ?User $user, bool $autosave = false): ?PostRevision
    {
        $latest = $post->revisions()->latest('id')->first();
        $hash = hash('sha256', json_encode($before, JSON_UNESCAPED_SLASHES));

        if ($latest?->content_hash === $hash) {
            return null;
        }

        if ($autosave && $latest && $latest->user_id === $user?->id
            && $latest->created_at->gt(now()->subMinutes(self::AUTOSAVE_GAP_MINUTES))) {
            return null;
        }

        $revision = $post->revisions()->create([
            'user_id' => $user?->id,
            'content_hash' => $hash,
            'snapshot' => $before,
        ]);

        // History is protection, not an unbounded audit log.
        $post->revisions()->latest('id')->skip(self::KEEP)->take(PHP_INT_MAX)->get()->each->delete();

        return $revision;
    }
}
