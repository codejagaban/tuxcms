<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/** The post editor shows a post as the site would, before it is saved or published. */
class PostPreviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['site.url' => 'https://example.test']);
    }

    public function test_the_preview_needs_a_signed_in_editor(): void
    {
        $this->postJson('/api/v1/posts/preview', ['title' => 'Hi'])->assertUnauthorized();
    }

    public function test_an_unsaved_post_is_drawn_through_the_theme_with_its_body_cleaned(): void
    {
        Sanctum::actingAs(User::factory()->create(['name' => 'Ada Writer']));
        config(['site.theme' => 'shopfront']);

        $html = $this->post('/api/v1/posts/preview', [
            'title' => 'Not saved yet',
            'excerpt' => 'Still being written.',
            'body' => '<h2>First part</h2><p>Words.</p><script>alert(1)</script><img src="x" onerror="steal()">',
            'tags' => ['Studio notes'],
        ], ['Accept' => 'text/html'])->assertOk()->assertHeader('Cache-Control', 'no-store, private')->getContent();

        $this->assertStringContainsString('Not saved yet', $html);
        $this->assertStringContainsString('<h2>First part</h2><p>Words.</p>', $html);
        $this->assertStringContainsString('Ada Writer', $html);
        $this->assertStringContainsString('href="/blog/tag/studio-notes/"', $html);
        $this->assertStringContainsString('class="block read"', $html);
        foreach (['alert(1)', 'onerror', 'steal'] as $gone) {
            $this->assertStringNotContainsString($gone, $html);
        }
    }

    public function test_a_saved_post_keeps_its_author_and_shows_its_neighbours(): void
    {
        $author = User::factory()->create(['name' => 'First Author']);
        Sanctum::actingAs(User::factory()->create(['name' => 'Someone Else']));
        Post::create(['title' => 'An older one', 'slug' => 'older', 'status' => 'published', 'published_at' => now()->subWeek()]);
        $draft = Post::create(['title' => 'Draft', 'slug' => 'draft', 'status' => 'draft', 'author_id' => $author->id]);

        $html = $this->post('/api/v1/posts/preview', ['id' => $draft->id, 'title' => 'Draft, edited'], ['Accept' => 'text/html'])
            ->assertOk()->getContent();

        $this->assertStringContainsString('Draft, edited', $html);
        $this->assertStringContainsString('First Author', $html);
        $this->assertStringContainsString('href="/blog/older/"', $html);
        $this->assertSame('Draft', $draft->fresh()->title, 'A preview saves nothing.');
    }
}
