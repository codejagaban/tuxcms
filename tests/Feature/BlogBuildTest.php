<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use App\Services\Site\SiteBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogBuildTest extends TestCase
{
    use RefreshDatabase;

    private string $site;

    protected function setUp(): void
    {
        parent::setUp();
        $this->site = storage_path('framework/testing/blog-site-'.uniqid());
        config(['site.output_path' => $this->site, 'site.url' => 'https://example.test']);
    }

    protected function tearDown(): void
    {
        \Illuminate\Support\Facades\File::deleteDirectory($this->site);
        parent::tearDown();
    }

    public function test_a_site_without_published_posts_has_no_blog(): void
    {
        Page::factory()->homepage()->create();
        Post::create(['title' => 'Still a draft', 'slug' => 'still-a-draft', 'status' => 'draft']);
        Post::create(['title' => 'Next week', 'slug' => 'next-week', 'status' => 'published', 'published_at' => now()->addWeek()]);

        app(SiteBuilder::class)->build();

        $this->assertFileDoesNotExist($this->site.'/blog/index.html');
        $this->assertFileDoesNotExist($this->site.'/blog/next-week/index.html');
        $this->assertStringNotContainsString('href="/blog/"', $this->read('index.html'));
        $this->assertStringNotContainsString('/blog/', $this->read('sitemap.xml'));
    }

    public function test_publishing_builds_the_listing_the_post_and_its_category(): void
    {
        Page::factory()->homepage()->create();
        $author = User::factory()->create(['name' => 'Ada Writer']);
        $news = PostCategory::create(['name' => 'News', 'slug' => 'news']);
        PostCategory::create(['name' => 'Empty', 'slug' => 'empty']);
        Post::create([
            'title' => 'Opening the new shop', 'slug' => 'opening-the-new-shop', 'status' => 'published',
            'published_at' => now()->subDay(), 'excerpt' => 'We have moved.', 'author_id' => $author->id,
            'post_category_id' => $news->id, 'cover_image' => '/storage/1/shop.jpg', 'cover_image_alt' => 'The shop front',
            'body' => '<h2>Why we moved</h2><p>More room.</p><script>alert(1)</script>',
        ]);
        Post::create(['title' => 'An older note', 'slug' => 'an-older-note', 'status' => 'published', 'published_at' => now()->subWeek(), 'body' => '<p>Older.</p>']);

        $result = app(SiteBuilder::class)->build();
        $this->assertSame(3, $result['pages']);

        $listing = $this->read('blog/index.html');
        $this->assertStringContainsString('Opening the new shop', $listing);
        $this->assertStringContainsString('We have moved.', $listing);
        $this->assertStringContainsString('href="/blog/category/news/"', $listing);
        $this->assertStringNotContainsString('/blog/category/empty/', $listing);
        $this->assertLessThan(strpos($listing, 'An older note'), strpos($listing, 'Opening the new shop'));
        $this->assertStringNotContainsString('Older posts', $listing);

        $post = $this->read('blog/opening-the-new-shop/index.html');
        $this->assertStringContainsString('<h2>Why we moved</h2><p>More room.</p>', $post);
        $this->assertStringNotContainsString('alert(1)', $post);
        $this->assertStringContainsString('"@type":"BlogPosting"', $post);
        $this->assertStringContainsString('Ada Writer', $post);
        $this->assertStringContainsString('<link rel="canonical" href="https://example.test/blog/opening-the-new-shop/">', $post);
        $this->assertStringContainsString('<meta property="og:image" content="https://example.test/storage/1/shop.jpg">', $post);
        $this->assertStringContainsString('alt="The shop front"', $post);
        $this->assertStringContainsString('href="/blog/an-older-note/"', $post);

        $category = $this->read('blog/category/news/index.html');
        $this->assertStringContainsString('Opening the new shop', $category);
        $this->assertStringNotContainsString('An older note', $category);
        $this->assertFileDoesNotExist($this->site.'/blog/category/empty/index.html');

        // The menu links to the blog, and marks it as the current place there.
        $this->assertStringContainsString('href="/blog/"', $this->read('index.html'));
        $this->assertMatchesRegularExpression('#href="/blog/"[^>]*aria-current="page"#', $listing);

        $sitemap = $this->read('sitemap.xml');
        $this->assertStringContainsString('https://example.test/blog/</loc>', $sitemap);
        $this->assertStringContainsString('https://example.test/blog/opening-the-new-shop/</loc>', $sitemap);
        $this->assertStringContainsString('https://example.test/blog/category/news/</loc>', $sitemap);

        $feed = $this->read('blog/feed.xml');
        $this->assertNotFalse(simplexml_load_string($feed));
        $this->assertStringContainsString('<link>https://example.test/blog/opening-the-new-shop/</link>', $feed);
        $this->assertStringContainsString('<category>News</category>', $feed);

        $this->assertStringContainsString('## Blog', $this->read('llms.txt'));
    }

    public function test_a_long_listing_is_split_into_pages(): void
    {
        foreach (range(1, Post::PER_PAGE + 2) as $n) {
            Post::create(['title' => 'Post number '.$n, 'slug' => 'post-'.$n, 'status' => 'published', 'published_at' => now()->subDays($n)]);
        }

        app(SiteBuilder::class)->build();

        $first = $this->read('blog/index.html');
        $second = $this->read('blog/page/2/index.html');
        $this->assertStringContainsString('href="/blog/page/2/"', $first);
        $this->assertStringContainsString('Page 1 of 2', $first);
        $this->assertStringNotContainsString('Newer posts', $first);
        $this->assertStringContainsString('href="/blog/" rel="prev"', $second);
        $this->assertStringContainsString('Post number '.(Post::PER_PAGE + 1), $second);
        $this->assertStringNotContainsString('Post number '.(Post::PER_PAGE + 1).'<', $first);
        $this->assertFileDoesNotExist($this->site.'/blog/page/3/index.html');
    }

    public function test_unpublishing_the_last_post_removes_the_blog(): void
    {
        $post = Post::create(['title' => 'Only post', 'slug' => 'only-post', 'status' => 'published', 'published_at' => now()->subDay()]);
        app(SiteBuilder::class)->build();
        $this->assertFileExists($this->site.'/blog/only-post/index.html');

        $post->update(['status' => 'draft']);
        app(SiteBuilder::class)->build();

        $this->assertFileDoesNotExist($this->site.'/blog/only-post/index.html');
        $this->assertFileDoesNotExist($this->site.'/blog/index.html');
        $this->assertFileDoesNotExist($this->site.'/blog/feed.xml');
    }

    private function read(string $relative): string
    {
        return file_get_contents($this->site.'/'.$relative);
    }
}
