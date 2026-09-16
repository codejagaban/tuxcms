<?php

namespace Tests\Feature;

use App\Models\Page;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PageRevisionTest extends TestCase
{
    public function test_page_update_captures_the_previous_state(): void
    {
        $page = Page::factory()->create(['title' => 'Before']);
        $section = $page->sections()->create(['type' => 'text', 'title' => 'Original section', 'order' => 0]);
        Sanctum::actingAs($this->editor());

        $this->putJson("/api/v1/pages/{$page->id}", [
            'title' => 'After',
            'sections' => [[
                'id' => $section->id,
                'type' => 'text',
                'title' => 'Updated section',
                'order' => 0,
            ]],
        ])->assertOk();

        $revision = $page->revisions()->sole();
        $this->assertSame('Before', $revision->snapshot['title']);
        $this->assertSame('Original section', $revision->snapshot['sections'][0]['title']);
    }

    public function test_editor_can_list_revisions_but_guests_cannot(): void
    {
        $page = Page::factory()->create(['title' => 'Before']);
        $user = $this->editor();
        $page->revisions()->create([
            'user_id' => $user->id,
            'content_hash' => str_repeat('a', 64),
            'snapshot' => ['title' => 'Before', 'sections' => [], 'seo' => []],
        ]);

        $this->getJson("/api/v1/pages/{$page->id}/revisions")->assertUnauthorized();

        Sanctum::actingAs($user);
        $this->getJson("/api/v1/pages/{$page->id}/revisions")
            ->assertOk()
            ->assertJsonPath('data.0.snapshot.title', 'Before')
            ->assertJsonPath('data.0.author', $user->name);
    }

    public function test_identical_states_are_not_captured_twice(): void
    {
        $page = Page::factory()->create(['title' => 'Before']);
        Sanctum::actingAs($this->editor());

        $this->putJson("/api/v1/pages/{$page->id}", ['title' => 'After'])->assertOk();
        $this->putJson("/api/v1/pages/{$page->id}", ['title' => 'After again'])->assertOk();
        $this->putJson("/api/v1/pages/{$page->id}", ['title' => 'After again'])->assertOk();

        $this->assertCount(2, $page->revisions);
    }
}
