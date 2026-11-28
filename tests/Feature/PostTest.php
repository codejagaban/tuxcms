<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\PostCategory;
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
        $this->getJson('/api/v1/post-categories')->assertUnauthorized();
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
        $this->postJson('/api/v1/posts', [...$this->payload(), 'slug' => 'category'])->assertUnprocessable()->assertJsonValidationErrors('slug');
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

    public function test_categories_can_be_managed_and_filter_posts(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $news = $this->postJson('/api/v1/post-categories', ['name' => 'Company News'])->assertCreated()
            ->assertJsonPath('data.slug', 'company-news')->json('data.id');
        $this->postJson('/api/v1/post-categories', ['name' => 'Company news'])->assertUnprocessable();

        $this->postJson('/api/v1/posts', [...$this->payload(), 'post_category_id' => $news])->assertCreated()
            ->assertJsonPath('data.category.name', 'Company News');
        $this->postJson('/api/v1/posts', [...$this->payload(), 'slug' => 'second', 'title' => 'Second'])->assertCreated();

        $this->getJson('/api/v1/posts?category='.$news)->assertJsonPath('meta.total', 1);
        $this->getJson('/api/v1/post-categories')->assertJsonPath('data.0.posts_count', 1);

        $this->deleteJson('/api/v1/post-categories/'.$news)->assertNoContent();
        $this->assertSame(2, Post::count());
        $this->assertNull(Post::where('slug', 'a-first-post')->first()->post_category_id);
        $this->assertSame(0, PostCategory::count());
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
