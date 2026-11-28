<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use App\Services\Content\PostRevisionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/** A post keeps its earlier states, so a change can be undone. */
class PostRevisionTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return [...['title' => 'First title', 'slug' => 'a-post', 'body' => '<p>One.</p>', 'status' => 'draft'], ...$overrides];
    }

    public function test_each_save_that_changes_the_post_keeps_the_state_before_it(): void
    {
        Sanctum::actingAs(User::factory()->create(['name' => 'Ada Writer']));
        $id = $this->postJson('/api/v1/posts', [...$this->payload(), 'tags' => ['Studio']])->assertCreated()->json('data.id');

        $this->putJson("/api/v1/posts/{$id}", $this->payload(['title' => 'Second title']))->assertOk();
        $this->putJson("/api/v1/posts/{$id}", $this->payload(['title' => 'Second title']))->assertOk();
        $this->putJson("/api/v1/posts/{$id}", [...$this->payload(['title' => 'Second title']), 'tags' => []])->assertOk();

        $this->getJson("/api/v1/posts/{$id}/revisions")
            ->assertOk()
            ->assertJsonCount(2, 'data')
            // Newest first: before the tags were cleared, then the first state.
            ->assertJsonPath('data.0.snapshot.tags', ['Studio'])
            ->assertJsonPath('data.0.snapshot.title', 'Second title')
            ->assertJsonPath('data.1.snapshot.title', 'First title')
            ->assertJsonPath('data.1.snapshot.body', '<p>One.</p>')
            ->assertJsonPath('data.1.author', 'Ada Writer');
    }

    public function test_autosaves_keep_one_state_every_ten_minutes(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $id = $this->postJson('/api/v1/posts', $this->payload())->assertCreated()->json('data.id');

        foreach (['A', 'B', 'C'] as $word) {
            $this->putJson("/api/v1/posts/{$id}", $this->payload(['body' => "<p>{$word}</p>", 'autosave' => true]))->assertOk();
            $this->travel(2)->minutes();
        }
        $this->assertSame(1, Post::find($id)->revisions()->count(), 'Autosaves close together keep one state.');

        $this->travel(PostRevisionService::AUTOSAVE_GAP_MINUTES)->minutes();
        $this->putJson("/api/v1/posts/{$id}", $this->payload(['body' => '<p>D</p>', 'autosave' => true]))->assertOk();
        // A save the editor asks for always keeps one.
        $this->putJson("/api/v1/posts/{$id}", $this->payload(['body' => '<p>E</p>']))->assertOk();

        $this->assertSame(['<p>D</p>', '<p>C</p>', '<p>One.</p>'],
            Post::find($id)->revisions()->latest('id')->get()->pluck('snapshot.body')->all());
    }

    public function test_only_the_last_thirty_are_kept_and_they_go_with_the_post(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $id = $this->postJson('/api/v1/posts', $this->payload())->assertCreated()->json('data.id');

        foreach (range(1, PostRevisionService::KEEP + 5) as $n) {
            $this->putJson("/api/v1/posts/{$id}", $this->payload(['title' => "Title {$n}"]))->assertOk();
        }

        $this->assertSame(PostRevisionService::KEEP, Post::find($id)->revisions()->count());
        Post::find($id)->forceDelete();
        $this->assertDatabaseCount('post_revisions', 0);
    }

    public function test_history_needs_a_signed_in_editor(): void
    {
        $post = Post::create(['title' => 'A', 'slug' => 'a', 'status' => 'draft']);

        $this->getJson("/api/v1/posts/{$post->id}/revisions")->assertUnauthorized();
    }
}
