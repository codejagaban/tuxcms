<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** The site search finds published blog posts as well as pages. */
class PostSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_posts_that_match_are_found_alongside_pages(): void
    {
        Page::factory()->create(['title' => 'Kiln hire', 'status' => 'published']);
        $found = Post::create(['title' => 'Firing the new kiln', 'slug' => 'firing-the-new-kiln', 'status' => 'published', 'published_at' => now()->subDay(), 'body' => '<p>It took a week.</p>']);
        $found->syncTagsWithType(['Studio'], Post::TAG_TYPE);
        Post::create(['title' => 'Glazes', 'slug' => 'glazes', 'status' => 'published', 'published_at' => now()->subDays(2), 'body' => '<p>Ours go in the kiln twice.</p>']);
        Post::create(['title' => 'Kiln draft', 'slug' => 'kiln-draft', 'status' => 'draft']);
        Post::create(['title' => 'Kiln next week', 'slug' => 'kiln-next-week', 'status' => 'published', 'published_at' => now()->addWeek()]);
        Post::create(['title' => 'Nothing to see', 'slug' => 'nothing', 'status' => 'published', 'published_at' => now()->subDay()]);

        $this->getJson('/api/v1/search?q=kiln')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.title', 'Kiln hire')
            ->assertJsonCount(2, 'posts')
            ->assertJsonPath('posts.0.path', '/blog/firing-the-new-kiln/')
            ->assertJsonPath('posts.0.tags', ['Studio'])
            ->assertJsonPath('posts.0.summary', 'It took a week.')
            ->assertJsonPath('posts.1.slug', 'glazes')
            ->assertJsonMissingPath('posts.0.body');
    }
}
