<?php

namespace App\Services\Content;

use App\Models\Page;
use App\Models\PageRevision;
use App\Models\User;

class PageRevisionService
{
    /** Store the current page state unless it is identical to the latest copy. */
    public function capture(Page $page, ?User $user): ?PageRevision
    {
        return $this->captureSnapshot($page, $this->snapshot($page), $user);
    }

    public function captureSnapshot(Page $page, array $snapshot, ?User $user): ?PageRevision
    {
        $hash = hash('sha256', json_encode($snapshot, JSON_UNESCAPED_SLASHES));
        $latest = $page->revisions()->latest('id')->first();

        if ($latest?->content_hash === $hash) {
            return null;
        }

        $revision = $page->revisions()->create([
            'user_id' => $user?->id,
            'content_hash' => $hash,
            'snapshot' => $snapshot,
        ]);

        // Revision history is protection, not an unbounded audit log.
        $page->revisions()->latest('id')->skip(30)->take(PHP_INT_MAX)->get()->each->delete();

        return $revision;
    }

    public function snapshot(Page $page): array
    {
        $page->loadMissing(['sections', 'seo']);

        return [
            'title' => $page->title,
            'slug' => $page->slug,
            'content' => $page->content ?? '',
            'excerpt' => $page->excerpt ?? '',
            'template' => $page->template,
            'status' => $page->status,
            'parent_id' => $page->parent_id,
            'order' => $page->order,
            'is_homepage' => $page->is_homepage,
            'show_in_nav' => $page->show_in_nav,
            'nav_label' => $page->nav_label,
            'custom_fields' => $page->custom_fields,
            'published_at' => $page->published_at?->toIso8601String(),
            'sections' => $page->sections->map(fn ($section) => [
                'id' => $section->id,
                'key' => $section->key,
                'type' => $section->type,
                'title' => $section->title ?? '',
                'content' => $section->content ?? '',
                'data' => $section->data ?? [],
                'order' => $section->order,
                'is_visible' => $section->is_visible,
            ])->values()->all(),
            'seo' => $page->seo?->only([
                'meta_title', 'meta_description', 'meta_keywords', 'og_title',
                'og_description', 'og_image', 'og_type', 'twitter_card',
                'twitter_title', 'twitter_description', 'twitter_image',
                'canonical_url', 'robots', 'schema_markup', 'custom_head',
            ]) ?? [],
        ];
    }
}
