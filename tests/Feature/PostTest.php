<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use App\Support\PublishState;
use App\Support\RichText;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_management_requires_authentication(): void
    {
        $this->getJson('/api/v1/posts')->assertUnauthorized();
        $this->postJson('/api/v1/posts', [])->assertUnauthorized();
        $this->getJson('/api/v1/post-tags')->assertUnauthorized();
    }

    public function test_editor_can_create_publish_and_delete_a_post(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $id = $this->postJson('/api/v1/posts', $this->payload())->assertCreated()
            ->assertJsonPath('data.status', 'draft')
            ->assertJsonPath('data.author', $user->name)
            ->assertJsonPath('data.path', '/blog/a-first-post/')
            ->json('data.id');
        $this->assertNull(Post::find($id)->published_at);

        $this->putJson('/api/v1/posts/'.$id, [...$this->payload(), 'status' => 'published'])
            ->assertOk()->assertJsonPath('data.status', 'published');
        $this->assertNotNull(Post::find($id)->published_at);

        $this->deleteJson('/api/v1/posts/'.$id)->assertNoContent();
        $this->assertSoftDeleted('posts', ['id' => $id]);
    }

    public function test_a_future_date_schedules_a_post(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/v1/posts', [...$this->payload(), 'status' => 'published', 'published_at' => now()->addWeek()->toIso8601String()])
            ->assertCreated()->assertJsonPath('data.is_scheduled', true);

        $this->assertSame(0, Post::published()->count());
        $this->assertSame(1, Post::scheduled()->count());
        $this->getJson('/api/v1/posts?status=scheduled')->assertJsonPath('meta.total', 1);
        $this->getJson('/api/v1/posts?status=published')->assertJsonPath('meta.total', 0);
    }

    public function test_slug_must_be_unique_and_not_one_the_blog_uses(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $this->postJson('/api/v1/posts', $this->payload())->assertCreated();

        $this->postJson('/api/v1/posts', $this->payload())->assertUnprocessable()->assertJsonValidationErrors('slug');
        $this->postJson('/api/v1/posts', [...$this->payload(), 'slug' => 'tag'])->assertUnprocessable()->assertJsonValidationErrors('slug');
        $this->postJson('/api/v1/posts', [...$this->payload(), 'slug' => 'page'])->assertUnprocessable()->assertJsonValidationErrors('slug');
    }

    public function test_the_body_is_cleaned_before_it_is_stored(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $body = '<h2 style="color:red" onclick="steal()">Heading</h2>'
            .'<p>Hello <strong>there</strong><script>alert(1)</script></p>'
            .'<p><a href="javascript:alert(1)">bad</a> <a href="https://example.com/page">good</a> <a href="/contact/">local</a></p>'
            .'<img src="/storage/1/photo.jpg" alt="A photo" onerror="steal()"><img src="data:image/png;base64,AAAA">'
            .'<iframe src="https://example.com"></iframe><div class="x">Loose words</div>';

        $stored = Post::find($this->postJson('/api/v1/posts', [...$this->payload(), 'body' => $body])->assertCreated()->json('data.id'))->body;

        $this->assertStringContainsString('<h2>Heading</h2>', $stored);
        $this->assertStringContainsString('<strong>there</strong>', $stored);
        $this->assertStringContainsString('<a href="https://example.com/page" rel="noopener noreferrer" target="_blank">good</a>', $stored);
        $this->assertStringContainsString('<a href="/contact/">local</a>', $stored);
        $this->assertStringContainsString('<img src="/storage/1/photo.jpg" alt="A photo" />', $stored);
        $this->assertStringContainsString('Loose words', $stored);
        foreach (['script', 'alert', 'onclick', 'onerror', 'style=', 'javascript:', 'data:image', 'iframe', '<div', 'steal'] as $gone) {
            $this->assertStringNotContainsString($gone, $stored);
        }
        $this->assertStringContainsString('<a>bad</a>', $stored);
    }

    public function test_an_empty_editor_stores_no_body(): void
    {
        $this->assertNull(app(RichText::class)->clean('<p></p>'));
        $this->assertNull(app(RichText::class)->clean("  \n "));
        $this->assertSame('<p>Café – “quoted”</p>', app(RichText::class)->clean('<p>Café – “quoted”</p>'));
    }

    public function test_listing_summary_and_reading_time(): void
    {
        $post = new Post(['body' => '<h2>Title</h2><p>'.str_repeat('word ', 450).'</p>']);
        $this->assertSame(3, $post->readingMinutes());
        $this->assertSame(180, mb_strlen($post->summary()));
        $this->assertStringStartsWith('Title word', $post->summary());
        $this->assertSame('Written by hand.', (new Post(['excerpt' => ' Written by hand. ', 'body' => '<p>Body</p>']))->summary());
    }

    public function test_posts_are_tagged_and_filtered_by_tag(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $id = $this->postJson('/api/v1/posts', [...$this->payload(), 'tags' => ['Company News', ' Design ']])->assertCreated()
            ->assertJsonPath('data.tags', [['name' => 'Company News', 'slug' => 'company-news'], ['name' => 'Design', 'slug' => 'design']])
            ->json('data.id');
        $this->postJson('/api/v1/posts', [...$this->payload(), 'slug' => 'second', 'title' => 'Second', 'tags' => ['Design']])->assertCreated();
        $this->postJson('/api/v1/posts', [...$this->payload(), 'slug' => 'third', 'title' => 'Third', 'tags' => ['a', 'A']])
            ->assertUnprocessable()->assertJsonValidationErrors('tags.1');

        $this->getJson('/api/v1/posts?tag=company-news')->assertJsonPath('meta.total', 1);
        $this->getJson('/api/v1/posts?tag=design')->assertJsonPath('meta.total', 2);
        $this->getJson('/api/v1/post-tags')->assertJsonPath('data', [
            ['name' => 'Company News', 'slug' => 'company-news', 'posts_count' => 1],
            ['name' => 'Design', 'slug' => 'design', 'posts_count' => 2],
        ]);

        // Saving without tags leaves them alone; an empty list clears them.
        $this->putJson('/api/v1/posts/'.$id, $this->payload())->assertOk()->assertJsonCount(2, 'data.tags');
        $this->putJson('/api/v1/posts/'.$id, [...$this->payload(), 'tags' => []])->assertOk()->assertJsonCount(0, 'data.tags');
        $this->getJson('/api/v1/post-tags')->assertJsonCount(1, 'data');
    }

    public function test_a_post_without_an_address_gets_one_from_its_title(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $first = ['title' => 'Notes from the studio', 'status' => 'draft'];

        $id = $this->postJson('/api/v1/posts', $first)->assertCreated()->assertJsonPath('data.slug', 'notes-from-the-studio')->json('data.id');
        $this->postJson('/api/v1/posts', [...$first, 'slug' => ''])->assertCreated()->assertJsonPath('data.slug', 'notes-from-the-studio-2');
        Post::find($id)->delete();
        // A deleted post still holds its address, so it can be restored.
        $this->postJson('/api/v1/posts', $first)->assertCreated()->assertJsonPath('data.slug', 'notes-from-the-studio-3');
        // The blog's own addresses are never handed out.
        $this->postJson('/api/v1/posts', ['title' => 'Tag', 'status' => 'draft'])->assertCreated()->assertJsonPath('data.slug', 'tag-2');
        $this->postJson('/api/v1/posts', ['title' => '¿¡!', 'status' => 'draft'])->assertCreated()->assertJsonPath('data.slug', 'post');
    }

    public function test_a_saved_post_keeps_its_address_when_none_is_sent(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $id = $this->postJson('/api/v1/posts', $this->payload())->assertCreated()->json('data.id');

        $this->putJson('/api/v1/posts/'.$id, [...$this->payload(), 'title' => 'A new title', 'slug' => null])
            ->assertOk()->assertJsonPath('data.slug', 'a-first-post');
    }

    public function test_publishing_without_an_excerpt_writes_one_from_the_body(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $body = '<h2>Why we moved</h2><p>We needed more room. The old studio had one window.</p><p>'.str_repeat('More words here. ', 20).'</p>';

        $id = $this->postJson('/api/v1/posts', [...$this->payload(), 'excerpt' => null, 'body' => $body])
            ->assertCreated()->assertJsonPath('data.excerpt', null)->json('data.id');

        $excerpt = $this->putJson('/api/v1/posts/'.$id, [...$this->payload(), 'excerpt' => '', 'body' => $body, 'status' => 'published'])
            ->assertOk()->json('data.excerpt');
        $this->assertStringStartsWith('We needed more room. The old studio had one window.', $excerpt);
        $this->assertStringNotContainsString('Why we moved', $excerpt);
        $this->assertLessThanOrEqual(200, mb_strlen($excerpt));
        $this->assertMatchesRegularExpression('/\.$/', $excerpt, 'It ends on a whole sentence.');

        // One the editor wrote is kept as it is.
        $this->putJson('/api/v1/posts/'.$id, [...$this->payload(), 'excerpt' => 'Written by hand.', 'status' => 'published'])
            ->assertOk()->assertJsonPath('data.excerpt', 'Written by hand.');
    }

    public function test_saving_a_post_marks_the_site_as_changed(): void
    {
        PublishState::markPublished(now());
        $this->travel(1)->minutes();
        $this->assertFalse(PublishState::hasPendingChanges());

        Post::create(['title' => 'New', 'slug' => 'new', 'status' => 'draft']);

        $this->assertTrue(PublishState::hasPendingChanges());
    }

    private function payload(): array
    {
        return [
            'title' => 'A first post',
            'slug' => 'a-first-post',
            'excerpt' => 'What the post is about.',
            'body' => '<p>The body of the post.</p>',
            'status' => 'draft',
        ];
    }
}
