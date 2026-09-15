<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Services\Site\NavigationBuilder;
use Tests\TestCase;

/**
 * Navigation is derived from the page tree — there is no menu builder — so
 * these rules are the entire nav contract.
 */
class NavigationTest extends TestCase
{
    private function nav(?Page $current = null): array
    {
        return app(NavigationBuilder::class)->build($current);
    }

    public function test_published_pages_appear_in_order(): void
    {
        Page::factory()->homepage()->create();
        Page::factory()->create(['title' => 'Second', 'slug' => 'second', 'order' => 2]);
        Page::factory()->create(['title' => 'First', 'slug' => 'first', 'order' => 1]);

        $labels = array_column($this->nav(), 'label');

        $this->assertSame(['First', 'Second', 'Careers'], $labels);
    }

    public function test_homepage_is_excluded_because_the_logo_links_there(): void
    {
        Page::factory()->homepage()->create(['title' => 'Home']);
        Page::factory()->create(['title' => 'About', 'slug' => 'about']);

        $this->assertSame(['About', 'Careers'], array_column($this->nav(), 'label'));
    }

    public function test_pages_hidden_from_nav_are_excluded(): void
    {
        Page::factory()->create(['title' => 'Shown', 'slug' => 'shown']);
        Page::factory()->hiddenFromNav()->create(['title' => 'Hidden', 'slug' => 'hidden']);

        $this->assertSame(['Shown', 'Careers'], array_column($this->nav(), 'label'));
    }

    public function test_drafts_are_excluded(): void
    {
        Page::factory()->create(['title' => 'Live', 'slug' => 'live']);
        Page::factory()->draft()->create(['title' => 'Draft', 'slug' => 'draft']);

        $this->assertSame(['Live', 'Careers'], array_column($this->nav(), 'label'));
    }

    public function test_nav_label_overrides_the_page_title(): void
    {
        Page::factory()->create(['title' => 'Professional Services', 'slug' => 'services', 'nav_label' => 'Services']);

        $this->assertSame(['Services', 'Careers'], array_column($this->nav(), 'label'));
    }

    public function test_children_are_nested_under_their_parent(): void
    {
        $parent = Page::factory()->create(['title' => 'Services', 'slug' => 'services']);
        Page::factory()->create(['title' => 'Web Design', 'slug' => 'web-design', 'parent_id' => $parent->id]);

        $nav = $this->nav();

        $this->assertCount(2, $nav);
        $this->assertSame('Web Design', $nav[0]['children'][0]['label']);
        $this->assertSame('/services/web-design/', $nav[0]['children'][0]['url']);
    }

    public function test_urls_carry_a_trailing_slash(): void
    {
        Page::factory()->create(['slug' => 'about']);

        $this->assertSame('/about/', $this->nav()[0]['url']);
    }

    public function test_current_page_is_marked_active(): void
    {
        $about = Page::factory()->create(['title' => 'About', 'slug' => 'about']);
        Page::factory()->create(['title' => 'Contact', 'slug' => 'contact']);

        $nav = collect($this->nav($about))->keyBy('label');

        $this->assertTrue($nav['About']['active']);
        $this->assertFalse($nav['Contact']['active']);
    }

    public function test_parent_is_active_when_a_child_is_current(): void
    {
        $parent = Page::factory()->create(['title' => 'Services', 'slug' => 'services']);
        $child = Page::factory()->create(['title' => 'Web Design', 'slug' => 'web-design', 'parent_id' => $parent->id]);

        $nav = $this->nav($child);

        $this->assertTrue($nav[0]['active'], 'A parent should highlight while a child page is open.');
    }

    public function test_careers_is_active_for_listing_and_job_paths(): void
    {
        $listing = collect(app(NavigationBuilder::class)->build(currentPath: '/careers/'))->keyBy('label');
        $detail = collect(app(NavigationBuilder::class)->build(currentPath: '/careers/business-development-manager/'))->keyBy('label');

        $this->assertTrue($listing['Careers']['active']);
        $this->assertTrue($detail['Careers']['active']);
    }
}
