<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use App\Support\PublishState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/** Blog tags can be renamed, merged and deleted from the dashboard. */
class PostTagTest extends TestCase
{
    use RefreshDatabase;

    private function tagged(string $slug, array $tags): Post
    {
        $post = Post::create(['title' => ucfirst($slug), 'slug' => $slug, 'status' => 'published', 'published_at' => now()->subDay()]);
        $post->syncTagsWithType($tags, Post::TAG_TYPE);

        return $post;
    }

    protected function setUp(): void
    {
        parent::setUp();
        Sanctum::actingAs(User::factory()->create());
    }

    public function test_a_tag_is_renamed_and_its_address_follows(): void
    {
        $post = $this->tagged('one', ['Clinet work']);

        $this->putJson('/api/v1/post-tags/clinet-work', ['name' => 'Client work'])
            ->assertOk()
            ->assertJsonPath('data', [['name' => 'Client work', 'slug' => 'client-work', 'posts_count' => 1]]);

        $this->assertSame(['Client work'], $post->fresh()->tagNames());
    }

    public function test_renaming_to_another_tag_merges_them(): void
    {
        $one = $this->tagged('one', ['Clinet work']);
        $two = $this->tagged('two', ['Client work', 'Clinet work']);
        $this->tagged('three', ['Client work']);

        $this->putJson('/api/v1/post-tags/clinet-work', ['name' => 'client WORK'])
            ->assertOk()
            ->assertJsonPath('data', [['name' => 'Client work', 'slug' => 'client-work', 'posts_count' => 3]]);

        $this->assertSame(['Client work'], $one->fresh()->tagNames());
        $this->assertSame(['Client work'], $two->fresh()->tagNames(), 'A post that had both keeps one.');
    }

    public function test_a_deleted_tag_leaves_its_posts(): void
    {
        $post = $this->tagged('one', ['Studio', 'Old news']);
        PublishState::markPublished(now());
        $this->travel(1)->minutes();

        $this->deleteJson('/api/v1/post-tags/old-news')->assertNoContent();

        $this->assertSame(['Studio'], $post->fresh()->tagNames());
        $this->assertTrue(PublishState::hasPendingChanges(), 'The tag page comes off the site at the next publish.');
        $this->getJson('/api/v1/post-tags')->assertJsonCount(1, 'data');
    }

    public function test_unknown_tags_and_bad_names_are_refused(): void
    {
        $this->tagged('one', ['Studio']);

        $this->putJson('/api/v1/post-tags/nope', ['name' => 'X'])->assertNotFound();
        $this->deleteJson('/api/v1/post-tags/nope')->assertNotFound();
        $this->putJson('/api/v1/post-tags/studio', ['name' => ' '])->assertUnprocessable();
    }
}
