<?php

namespace App\Services\Site;

use App\Models\JobPost;
use App\Models\Page;
use App\Models\Post;
use App\Services\Seo\FeedGenerator;
use App\Services\Seo\LlmsTxtGenerator;
use App\Services\Seo\RobotsGenerator;
use App\Services\Seo\SitemapGenerator;
use App\Support\Preview;
use App\Support\Site;
use App\Support\ThemeManifest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;

/**
 * Renders the whole site to static HTML.
 *
 * Output lands in Laravel's own `public/` directory. The stock .htaccess only
 * falls through to index.php when the request matches no real file or
 * directory, so `/about/index.html` is served straight by the web server with
 * no PHP, while `/api/...` still reaches Laravel.
 *
 * Publishing is per-file: render everything to a temp directory first, then
 * move each file into place with an atomic rename. A failed render therefore
 * never leaves a half-built site live. Files from a previous build that are no
 * longer produced are removed via the manifest, so nothing else in the docroot
 * is ever touched.
 */
class SiteBuilder
{
    private const MANIFEST = '.site-manifest.json';

    public function __construct(
        private NavigationBuilder $navigation,
        private SitemapGenerator $sitemap,
        private RobotsGenerator $robots,
        private LlmsTxtGenerator $llms,
        private FeedGenerator $feed,
    ) {}

    /** @return array{pages: int, files: int, pruned: int, output: string} */
    public function build(?callable $progress = null): array
    {
        $output = $this->outputPath();
        $temp = storage_path('app/site-build-'.uniqid());

        // CLI has no request context, so absolute URLs must come from config.
        URL::forceRootUrl(Site::url());

        // Assets stay root-relative ('/build/...'). Baking the host into every
        // asset URL would break the moment the site moves domain, and unlike
        // canonical/OG tags there is no requirement for them to be absolute.
        config(['app.asset_url' => '']);

        File::ensureDirectoryExists($temp);

        try {
            $written = $this->render($temp, $progress);
            $pruned = $this->publish($temp, $output, $written);
        } finally {
            File::deleteDirectory($temp);
        }

        return [
            'pages' => $this->publishedPages()->count() + JobPost::published()->count() + Post::published()->count(),
            'files' => count($written),
            'pruned' => $pruned,
            'output' => $output,
        ];
    }

    /**
     * Render one page exactly as a build would, without writing anything.
     *
     * The dashboard edits pages on the real rendered site, but a draft, a
     * scheduled page or one that has never been published has no built file
     * to show. This is what the editor looks at instead.
     */
    public function renderPage(Page $page): string
    {
        URL::forceRootUrl(Site::url());
        config(['app.asset_url' => '']);

        $page->load(['sections' => fn ($q) => $q->visible()->orderBy('order'), 'seo', 'media', 'parent']);

        if (Preview::active()) {
            $this->warnOfUndrawnThemeTypes($page);
        }

        return View::make($this->themeView('page', $page->template), [
            'page' => $page,
            'navigation' => $this->navigation->build($page),
            'buildYear' => date('Y'),
        ])->render();
    }

    /**
     * A theme that declares a section type in theme.json but ships no view
     * for it draws the section with its fallback. That is safe, but almost
     * certainly not what the theme meant, so say so while it is edited.
     */
    private function warnOfUndrawnThemeTypes(Page $page): void
    {
        $theme = Site::pageTheme($page->template);
        $declared = ThemeManifest::sections($theme);

        foreach ($page->sections->pluck('type')->unique() as $type) {
            if (isset($declared[$type]) && ! View::exists("site.themes.{$theme}.sections.".str_replace('_', '-', $type))) {
                Log::warning("Theme \"{$theme}\" declares the section type \"{$type}\" in theme.json but has no sections/".str_replace('_', '-', $type).'.blade.php; it is drawn with the fallback.');
            }
        }
    }

    /**
     * Render every page plus sitemap/robots/404 into the temp directory.
     *
     * @return array<string, string> relative path => sha1 of contents
     */
    private function render(string $temp, ?callable $progress): array
    {
        $pages = $this->publishedPages();
        $navigation = $this->navigation->build();
        $written = [];

        foreach ($pages as $page) {
            $relative = $this->relativePathFor($page);

            $view = $this->themeView('page', $page->template);

            $html = View::make($view, [
                'page' => $page,
                'navigation' => $this->navigation->build($page),
                'buildYear' => date('Y'),
            ])->render();

            $written[$relative] = $this->write($temp, $relative, $html);

            $progress && $progress($page->title, $relative);
        }

        $jobs = JobPost::published()->orderByDesc('published_at')->get();
        $careersNavigation = $this->navigation->build(currentPath: '/careers/');
        $written['careers/index.html'] = $this->write($temp, 'careers/index.html', View::make($this->themeView('jobs.index'), [
            'jobs' => $jobs,
            'navigation' => $careersNavigation,
            'buildYear' => date('Y'),
        ])->render());

        foreach ($jobs as $job) {
            $relative = 'careers/'.$job->slug.'/index.html';
            $written[$relative] = $this->write($temp, $relative, View::make($this->themeView('jobs.show'), [
                'job' => $job,
                'navigation' => $careersNavigation,
                'buildYear' => date('Y'),
            ])->render());
        }

        $posts = $this->publishedPosts();
        $written += $this->renderBlog($temp, $posts, $progress);

        $notFound = View::make($this->themeView('404'), [
            'page' => $this->placeholderPage('Page not found'),
            'navigation' => $navigation,
            'buildYear' => date('Y'),
        ])->render();

        $written['404.html'] = $this->write($temp, '404.html', $notFound);
        $written['sitemap.xml'] = $this->write($temp, 'sitemap.xml', $this->sitemap->generate());
        $written['robots.txt'] = $this->write($temp, 'robots.txt', $this->robots->generate());
        $written['llms.txt'] = $this->write($temp, 'llms.txt', $this->llms->generate($pages, $jobs, $posts));

        return $written;
    }

    /**
     * The blog: a paged listing, a page per post, a paged listing per category
     * and the RSS feed. A site with no published posts has no blog at all, so
     * nothing is written and the menu shows no link to it.
     *
     * @return array<string, string> relative path => sha1 of contents
     */
    private function renderBlog(string $temp, Collection $posts, ?callable $progress): array
    {
        if ($posts->isEmpty()) {
            return [];
        }

        $written = [];
        $navigation = $this->navigation->build(currentPath: '/blog/');
        $shared = ['navigation' => $navigation, 'buildYear' => date('Y')];

        // Only categories with something to read are offered to visitors.
        $categories = $posts->pluck('category')->filter()->unique('id')->sortBy('name')->values();

        $listings = [['base' => '/blog/', 'posts' => $posts, 'category' => null]];
        foreach ($categories as $category) {
            $listings[] = [
                'base' => '/blog/category/'.$category->slug.'/',
                'posts' => $posts->where('post_category_id', $category->id)->values(),
                'category' => $category,
            ];
        }

        foreach ($listings as $listing) {
            $chunks = $listing['posts']->chunk(Post::PER_PAGE)->values();
            $last = $chunks->count();

            foreach ($chunks as $index => $chunk) {
                $number = $index + 1;
                $address = fn (int $n) => $n === 1 ? $listing['base'] : $listing['base'].'page/'.$n.'/';
                $relative = ltrim($address($number), '/').'index.html';

                $written[$relative] = $this->write($temp, $relative, View::make($this->themeView('blog.index'), $shared + [
                    'posts' => $chunk->values(),
                    'category' => $listing['category'],
                    'categories' => $categories,
                    'path' => $address($number),
                    'pageNumber' => $number,
                    'lastPage' => $last,
                    'newerUrl' => $number > 1 ? $address($number - 1) : null,
                    'olderUrl' => $number < $last ? $address($number + 1) : null,
                ])->render());
            }
        }

        foreach ($posts as $index => $post) {
            $relative = 'blog/'.$post->slug.'/index.html';
            $written[$relative] = $this->write($temp, $relative, View::make($this->themeView('blog.show'), $shared + [
                'post' => $post,
                // The list is newest first, so the next one down is older.
                'newer' => $posts->get($index - 1),
                'older' => $posts->get($index + 1),
            ])->render());

            $progress && $progress($post->title, $relative);
        }

        $written['blog/feed.xml'] = $this->write($temp, 'blog/feed.xml', $this->feed->generate($posts));

        return $written;
    }

    /** Published posts, newest first. */
    private function publishedPosts(): Collection
    {
        return Post::published()->with(['category', 'author'])
            ->orderByDesc('published_at')->orderByDesc('id')->get();
    }

    /**
     * Move rendered files into the docroot, then delete files this build no
     * longer produces. Only ever removes paths recorded in the last manifest,
     * so index.php, .htaccess, build/ and admin/ are never at risk.
     */
    private function publish(string $temp, string $output, array $written): int
    {
        File::ensureDirectoryExists($output);

        foreach (array_keys($written) as $relative) {
            $destination = $output.'/'.$relative;
            File::ensureDirectoryExists(dirname($destination));

            $staged = $destination.'.tmp';
            File::copy($temp.'/'.$relative, $staged);
            // rename() is atomic on the same filesystem — no visitor ever sees
            // a partially written page.
            rename($staged, $destination);
        }

        $previous = $this->readManifest($output);
        $pruned = 0;

        foreach (array_keys($previous) as $relative) {
            if (isset($written[$relative])) {
                continue;
            }

            $stale = $output.'/'.$relative;

            if (File::exists($stale)) {
                File::delete($stale);
                $pruned++;
            }

            $this->removeEmptyDirectories(dirname($stale), $output);
        }

        File::put(
            $output.'/'.self::MANIFEST,
            json_encode(['generated_at' => now()->toAtomString(), 'files' => $written], JSON_PRETTY_PRINT)
        );

        return $pruned;
    }

    /**
     * Resolve a view through the theme chain: the page's own template, then
     * the site theme, then Shopfront (the bundled theme, which ships every
     * view), then the unthemed default.
     */
    private function themeView(string $name, ?string $template = null): string
    {
        // Which theme draws the page also decides which section types the
        // page may hold, so that choice is made in one place.
        if ($name === 'page') {
            $theme = Site::pageTheme($template);

            return $theme ? "site.themes.{$theme}.page" : 'site.page';
        }

        $themes = array_filter([
            $template ? str_replace('_', '-', $template) : null,
            config('site.theme'),
            $name === '404' ? null : 'shopfront',
        ]);

        foreach ($themes as $theme) {
            if (View::exists($view = "site.themes.{$theme}.{$name}")) {
                return $view;
            }
        }

        return "site.{$name}";
    }

    private function write(string $root, string $relative, string $contents): string
    {
        $path = $root.'/'.$relative;
        File::ensureDirectoryExists(dirname($path));
        File::put($path, $contents);

        return sha1($contents);
    }

    /** '/' => index.html, '/services/web-design' => services/web-design/index.html */
    private function relativePathFor(Page $page): string
    {
        $path = trim($page->getFullPath(), '/');

        return $path === '' ? 'index.html' : $path.'/index.html';
    }

    private function publishedPages()
    {
        return Page::published()
            ->with(['sections' => fn ($q) => $q->visible()->orderBy('order'), 'seo', 'media', 'parent'])
            ->orderBy('path')
            ->get();
    }

    /** The 404 page isn't a real Page, but the layout expects one. */
    private function placeholderPage(string $title): Page
    {
        $page = new Page(['title' => $title]);
        $page->setRelation('sections', collect());
        $page->setRelation('seo', null);

        return $page;
    }

    private function readManifest(string $output): array
    {
        $path = $output.'/'.self::MANIFEST;

        if (! File::exists($path)) {
            return [];
        }

        return json_decode(File::get($path), true)['files'] ?? [];
    }

    /** Tidy up directories emptied by pruning, never past the output root. */
    private function removeEmptyDirectories(string $directory, string $root): void
    {
        $root = realpath($root) ?: $root;

        while (
            $directory !== $root
            && str_starts_with($directory, $root)
            && File::isDirectory($directory)
            && count(File::allFiles($directory)) === 0
            && count(File::directories($directory)) === 0
        ) {
            File::deleteDirectory($directory);
            $directory = dirname($directory);
        }
    }

    public function outputPath(): string
    {
        return rtrim(config('site.output_path') ?: public_path(), '/');
    }
}
