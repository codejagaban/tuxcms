<?php

namespace Tests\Feature;

use App\Models\Page;
use Illuminate\Support\Facades\File;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Editing a section or its SEO is editing the page.
 *
 * pages.updated_at is what the dashboard's "Updated" column shows and what the
 * publish tracker compares against, so if a section-only edit doesn't move it,
 * the CMS reports "up to date" while the live site is stale.
 */
class ContentTimestampTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Sanctum::actingAs($this->editor());
    }

    private function sectionPayload(Page $page, array $overrides = []): array
    {
        $section = $page->sections->first();

        return ['sections' => [array_merge([
            'id' => $section->id,
            'key' => $section->key,
            'type' => $section->type,
            'title' => $section->title,
            'content' => $section->content,
            'data' => $section->data ?? [],
            'order' => 0,
            'is_visible' => true,
        ], $overrides)]];
    }

    public function test_editing_only_a_section_bumps_the_page_timestamp(): void
    {
        $page = Page::factory()->create();
        $page->sections()->create(['key' => 'hero', 'type' => 'hero', 'title' => 'Before', 'order' => 0]);
        $page->refresh()->load('sections');

        $original = $page->updated_at;
        $this->travel(1)->minute();

        $this->putJson("/api/v1/pages/{$page->id}", $this->sectionPayload($page, ['title' => 'After']))
            ->assertOk();

        $this->assertTrue(
            $page->fresh()->updated_at->gt($original),
            'A section-only edit must mark the page as changed.'
        );
    }

    public function test_editing_only_seo_bumps_the_page_timestamp(): void
    {
        $page = Page::factory()->create();
        $original = $page->updated_at;
        $this->travel(1)->minute();

        $this->putJson("/api/v1/pages/{$page->id}", ['seo' => ['meta_title' => 'New title']])
            ->assertOk();

        $this->assertTrue($page->fresh()->updated_at->gt($original));
    }

    public function test_adding_a_section_bumps_the_page_timestamp(): void
    {
        $page = Page::factory()->create();
        $original = $page->updated_at;
        $this->travel(1)->minute();

        $this->putJson("/api/v1/pages/{$page->id}", ['sections' => [
            ['id' => null, 'key' => 'cta', 'type' => 'cta', 'title' => 'New', 'data' => [], 'order' => 0, 'is_visible' => true],
        ]])->assertOk();

        $this->assertTrue($page->fresh()->updated_at->gt($original));
    }

    public function test_removing_a_section_bumps_the_page_timestamp(): void
    {
        $page = Page::factory()->create();
        $page->sections()->create(['key' => 'hero', 'type' => 'hero', 'order' => 0]);
        $page->refresh();

        $original = $page->updated_at;
        $this->travel(1)->minute();

        $this->putJson("/api/v1/pages/{$page->id}", ['sections' => []])->assertOk();

        $this->assertTrue($page->fresh()->updated_at->gt($original));
    }

    /** The consequence that actually matters: publish tracking. */
    public function test_a_section_edit_registers_as_a_pending_change(): void
    {
        $output = storage_path('framework/testing/ts-' . uniqid());
        File::ensureDirectoryExists($output);
        config(['site.output_path' => $output, 'site.url' => 'https://example.test']);
        $this->seedSiteSettings();

        $page = Page::factory()->homepage()->create();
        $page->sections()->create(['key' => 'hero', 'type' => 'hero', 'title' => 'Before', 'order' => 0]);
        $page->refresh()->load('sections');

        $this->postJson('/api/v1/site/publish')->assertOk();
        $this->assertSame(0, $this->getJson('/api/v1/site/status')->json('data.pending_changes'));

        $this->travel(1)->minute();
        $this->putJson("/api/v1/pages/{$page->id}", $this->sectionPayload($page, ['title' => 'After']))->assertOk();

        $this->assertSame(
            1,
            $this->getJson('/api/v1/site/status')->json('data.pending_changes'),
            'Editing section text must show up as a pending publish.'
        );

        File::deleteDirectory($output);
    }
}
