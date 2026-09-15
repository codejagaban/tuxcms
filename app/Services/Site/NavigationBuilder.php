<?php

namespace App\Services\Site;

use App\Models\JobPost;
use App\Models\Page;
use App\Support\Site;

/**
 * Builds site navigation from the page tree.
 *
 * There is no menu builder — nav is `parent_id` for nesting, `order` for
 * sequence and `show_in_nav` for inclusion, so it can never drift out of sync
 * with the pages that actually exist.
 */
class NavigationBuilder
{
    /** @return array<int, array{label: string, url: string, active: bool, children: array}> */
    public function build(?Page $current = null): array
    {
        $pages = Page::published()
            ->where('show_in_nav', true)
            ->where('is_homepage', false)
            ->orderBy('order')
            ->orderBy('title')
            ->get(['id', 'parent_id', 'title', 'nav_label', 'slug', 'path', 'is_homepage', 'order']);

        $byParent = $pages->groupBy('parent_id');
        $currentPath = $current ? Site::pathFor($current) : null;

        $items = $byParent->get(null, collect())
            ->map(fn (Page $page) => $this->item($page, $byParent, $currentPath))
            ->values()
            ->all();

        if (JobPost::published()->exists()) {
            $items[] = ['label' => 'Careers', 'url' => '/careers/', 'active' => false, 'children' => []];
        }

        return $items;
    }

    private function item(Page $page, $byParent, ?string $currentPath): array
    {
        $url = Site::pathFor($page);

        $children = $byParent->get($page->id, collect())
            ->map(fn (Page $child) => $this->item($child, $byParent, $currentPath))
            ->values()
            ->all();

        $active = $currentPath === $url
            || collect($children)->contains(fn ($child) => $child['active']);

        return [
            'label' => $page->navLabel(),
            'url' => $url,
            'active' => $active,
            'children' => $children,
        ];
    }
}
