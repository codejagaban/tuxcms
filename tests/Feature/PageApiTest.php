<?php

namespace Tests\Feature;

use App\Models\Page;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PageApiTest extends TestCase
{
    public function test_public_listing_only_returns_published_pages(): void
    {
        Page::factory()->create(['title' => 'Live']);
        Page::factory()->draft()->create(['title' => 'Hidden']);
        Page::factory()->scheduled()->create(['title' => 'Later']);

        $response = $this->getJson('/api/v1/pages');

        $response->assertOk();
        $titles = collect($response->json('data'))->pluck('title');

        $this->assertContains('Live', $titles);
        $this->assertNotContains('Hidden', $titles);
        $this->assertNotContains('Later', $titles, 'A future published_at should not be live yet.');
    }

    public function test_page_size_is_capped(): void
    {
        Page::factory()->create();

        $this->getJson('/api/v1/pages?per_page=10000')
            ->assertOk()
            ->assertJsonPath('meta.per_page', 100);
    }

    /**
     * Regression: read routes are public, so $request->user() is null even with
     * a valid token. Editors could never see their own drafts.
     */
    public function test_authenticated_editor_sees_drafts(): void
    {
        Page::factory()->create(['title' => 'Live']);
        Page::factory()->draft()->create(['title' => 'Hidden']);

        Sanctum::actingAs($this->editor());

        $titles = collect($this->getJson('/api/v1/pages')->json('data'))->pluck('title');

        $this->assertContains('Hidden', $titles);
    }

    public function test_public_tree_does_not_expose_draft_descendants(): void
    {
        $parent = Page::factory()->create(['title' => 'Public parent']);
        Page::factory()->draft()->create([
            'title' => 'Draft child',
            'parent_id' => $parent->id,
        ]);

        $response = $this->getJson('/api/v1/pages/tree');

        $response->assertOk();
        $this->assertSame([], $response->json('data.0.children'));
    }

    public function test_authenticated_tree_includes_draft_descendants(): void
    {
        $parent = Page::factory()->create(['title' => 'Public parent']);
        Page::factory()->draft()->create([
            'title' => 'Draft child',
            'parent_id' => $parent->id,
        ]);

        Sanctum::actingAs($this->editor());

        $this->getJson('/api/v1/pages/tree')
            ->assertOk()
            ->assertJsonPath('data.0.children.0.title', 'Draft child');
    }

    public function test_public_request_for_a_draft_page_is_404(): void
    {
        $draft = Page::factory()->draft()->create(['slug' => 'secret']);

        $this->getJson('/api/v1/pages/secret')->assertNotFound();

        Sanctum::actingAs($this->editor());
        $this->getJson('/api/v1/pages/secret')->assertOk();
    }

    public function test_nested_page_resolves_by_path(): void
    {
        $parent = Page::factory()->create(['slug' => 'services']);
        Page::factory()->create(['slug' => 'web-design', 'parent_id' => $parent->id, 'title' => 'Web Design']);

        $response = $this->getJson('/api/v1/pages/resolve?path=/services/web-design');

        $response->assertOk()->assertJsonPath('data.title', 'Web Design');
        $this->assertSame('/services/web-design', $response->json('data.path'));
    }

    public function test_creating_a_page_requires_authentication(): void
    {
        $this->postJson('/api/v1/pages', ['title' => 'Nope'])->assertUnauthorized();
    }

    public function test_editor_can_create_a_page_with_sections_and_seo(): void
    {
        Sanctum::actingAs($this->editor());

        $response = $this->postJson('/api/v1/pages', [
            'title' => 'New Page',
            'status' => 'published',
            'sections' => [
                ['key' => 'hero', 'type' => 'hero', 'title' => 'Hello', 'content' => 'World', 'data' => ['alignment' => 'left'], 'order' => 0, 'is_visible' => true],
            ],
            'seo' => ['meta_title' => 'New Page — SEO'],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.sections.0.type', 'hero')
            ->assertJsonPath('data.seo.meta_title', 'New Page — SEO');
    }

    public function test_updating_a_page_replaces_its_sections(): void
    {
        Sanctum::actingAs($this->editor());
        $page = Page::factory()->create();
        $page->sections()->create(['key' => 'old', 'type' => 'text', 'title' => 'Old', 'order' => 0]);

        $response = $this->putJson("/api/v1/pages/{$page->id}", [
            'sections' => [
                ['key' => 'new', 'type' => 'cta', 'title' => 'New', 'data' => [], 'order' => 0, 'is_visible' => true],
            ],
        ]);

        $response->assertOk();
        $this->assertSame(['cta'], $page->fresh()->sections->pluck('type')->all());
    }

    public function test_hidden_sections_survive_an_editor_round_trip(): void
    {
        Sanctum::actingAs($this->editor());
        $page = Page::factory()->create();
        $page->sections()->create(['key' => 'a', 'type' => 'text', 'order' => 0, 'is_visible' => true]);
        $page->sections()->create(['key' => 'b', 'type' => 'cta', 'order' => 1, 'is_visible' => false]);

        // The editor loads the page, then saves back exactly what it received.
        $loaded = $this->getJson("/api/v1/pages/{$page->id}")->json('data.sections');
        $this->assertCount(2, $loaded, 'Editors must receive hidden sections or saving would drop them.');

        $this->putJson("/api/v1/pages/{$page->id}", ['sections' => $loaded])->assertOk();

        $this->assertCount(2, $page->fresh()->sections);
    }

    public function test_nav_fields_persist(): void
    {
        Sanctum::actingAs($this->editor());
        $page = Page::factory()->create();

        $this->putJson("/api/v1/pages/{$page->id}", [
            'show_in_nav' => false,
            'nav_label' => 'Shorter',
        ])->assertOk();

        $page->refresh();
        $this->assertFalse($page->show_in_nav);
        $this->assertSame('Shorter', $page->nav_label);
    }
}
