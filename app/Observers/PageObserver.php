<?php

namespace App\Observers;

use App\Models\Page;
use Illuminate\Support\Facades\DB;

/**
 * Keeps the materialized `path` column in sync.
 *
 * Without this the path has to be derived by walking `parent` on every read,
 * which is an N+1 on any page listing and unusable for the static builder.
 *
 * Path computation deliberately happens in `saved()`, not `saving()`: Spatie's
 * HasSlug generates the slug during the save, so in `saving()` the slug is
 * still null on create and every path would collapse to '/'.
 */
class PageObserver
{
    public function saving(Page $page): void
    {
        // Only one page can be the homepage. Safe here — doesn't need the slug.
        if ($page->is_homepage && $page->isDirty('is_homepage')) {
            Page::where('id', '!=', $page->id ?? 0)
                ->where('is_homepage', true)
                ->update(['is_homepage' => false]);
        }
    }

    public function saved(Page $page): void
    {
        // computePath() walks the parent chain. If `parent` was loaded before
        // parent_id changed, Eloquent hands back the *old* parent and the page
        // keeps its previous path after being moved.
        $page->unsetRelation('parent');

        $path = $page->computePath();

        if ($page->path !== $path) {
            // saveQuietly so this doesn't re-enter the observer.
            $page->forceFill(['path' => $path])->saveQuietly();
        }

        // A slug, parent or homepage change moves every descendant too.
        if ($page->wasChanged(['slug', 'parent_id', 'is_homepage']) || $page->wasRecentlyCreated) {
            $this->repathDescendants($page);
        }
    }

    /** Recursively recompute paths for everything beneath this page. */
    private function repathDescendants(Page $page): void
    {
        DB::transaction(function () use ($page) {
            foreach ($page->children()->get() as $child) {
                $base = $page->path === '/' ? '' : rtrim($page->path ?? '', '/');
                $newPath = $base . '/' . $child->slug;

                if ($child->path !== $newPath) {
                    $child->forceFill(['path' => $newPath])->saveQuietly();
                }

                $this->repathDescendants($child);
            }
        });
    }
}
