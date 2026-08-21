<?php

namespace Tests\Feature;

use App\Models\Page;
use Tests\TestCase;

/**
 * The materialized `path` column drives navigation, canonical URLs, the
 * sitemap and where the static builder writes each file. If it drifts, the
 * whole site is wrong, so it is worth pinning down carefully.
 */
class PagePathTest extends TestCase
{
    public function test_homepage_path_is_root_regardless_of_slug(): void
    {
        $page = Page::factory()->homepage()->create(['slug' => 'welcome']);

        $this->assertSame('/', $page->fresh()->path);
    }

    public function test_top_level_page_path_uses_its_slug(): void
    {
        $page = Page::factory()->create(['slug' => 'about']);

        $this->assertSame('/about', $page->fresh()->path);
    }

    public function test_nested_page_path_includes_ancestors(): void
    {
        $services = Page::factory()->create(['slug' => 'services']);
        $child = Page::factory()->create(['slug' => 'web-design', 'parent_id' => $services->id]);

        $this->assertSame('/services/web-design', $child->fresh()->path);
    }

    public function test_deeply_nested_page_path(): void
    {
        $a = Page::factory()->create(['slug' => 'a']);
        $b = Page::factory()->create(['slug' => 'b', 'parent_id' => $a->id]);
        $c = Page::factory()->create(['slug' => 'c', 'parent_id' => $b->id]);

        $this->assertSame('/a/b/c', $c->fresh()->path);
    }

    /**
     * Regression: paths were computed in `saving()`, which runs before Spatie
     * generates the slug, so every path collapsed to '/'.
     */
    public function test_path_is_set_even_when_slug_is_generated_from_title(): void
    {
        $page = Page::create(['title' => 'Our Great Services', 'status' => 'published']);

        $this->assertSame('our-great-services', $page->fresh()->slug);
        $this->assertSame('/our-great-services', $page->fresh()->path);
    }

    public function test_renaming_a_parent_slug_repaths_descendants(): void
    {
        $parent = Page::factory()->create(['slug' => 'services']);
        $child = Page::factory()->create(['slug' => 'web-design', 'parent_id' => $parent->id]);
        $grandchild = Page::factory()->create(['slug' => 'seo', 'parent_id' => $child->id]);

        $parent->update(['slug' => 'what-we-do']);

        $this->assertSame('/what-we-do', $parent->fresh()->path);
        $this->assertSame('/what-we-do/web-design', $child->fresh()->path);
        $this->assertSame('/what-we-do/web-design/seo', $grandchild->fresh()->path);
    }

    public function test_moving_a_page_to_a_new_parent_repaths_it(): void
    {
        $a = Page::factory()->create(['slug' => 'a']);
        $b = Page::factory()->create(['slug' => 'b']);
        $child = Page::factory()->create(['slug' => 'child', 'parent_id' => $a->id]);

        $child->update(['parent_id' => $b->id]);

        $this->assertSame('/b/child', $child->fresh()->path);
    }

    public function test_only_one_page_can_be_the_homepage(): void
    {
        $first = Page::factory()->homepage()->create();
        $second = Page::factory()->create(['slug' => 'new-home']);

        $second->update(['is_homepage' => true]);

        $this->assertFalse($first->fresh()->is_homepage);
        $this->assertTrue($second->fresh()->is_homepage);
        $this->assertSame('/', $second->fresh()->path);
    }

    public function test_siblings_under_different_parents_may_share_a_slug(): void
    {
        $services = Page::factory()->create(['slug' => 'services']);
        $about = Page::factory()->create(['slug' => 'about']);

        $one = Page::factory()->create(['slug' => 'design', 'parent_id' => $services->id]);
        $two = Page::factory()->create(['slug' => 'design', 'parent_id' => $about->id]);

        $this->assertSame('/services/design', $one->fresh()->path);
        $this->assertSame('/about/design', $two->fresh()->path);
    }
}
