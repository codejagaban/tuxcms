<?php

namespace App\Services\Site;

use App\Models\JobPost;
use App\Models\Page;
use App\Services\Seo\RobotsGenerator;
use App\Services\Seo\SitemapGenerator;
use App\Support\Site;
use Illuminate\Support\Facades\File;
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
            'pages' => $this->publishedPages()->count() + JobPost::published()->count(),
            'files' => count($written),
            'pruned' => $pruned,
            'output' => $output,
        ];
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

            $themeView = 'site.themes.'.str_replace('_', '-', $page->template).'.page';
            $view = View::exists($themeView) ? $themeView : 'site.page';

            $html = View::make($view, [
                'page' => $page,
                'navigation' => $this->navigation->build($page),
                'buildYear' => date('Y'),
            ])->render();

            $written[$relative] = $this->write($temp, $relative, $html);

            $progress && $progress($page->title, $relative);
        }

        $jobs = JobPost::published()->orderByDesc('published_at')->get();
        if ($jobs->isNotEmpty()) {
            $written['careers/index.html'] = $this->write($temp, 'careers/index.html', View::make('site.themes.crystal.jobs.index', [
                'jobs' => $jobs,
                'navigation' => $navigation,
                'buildYear' => date('Y'),
            ])->render());
        }

        foreach ($jobs as $job) {
            $relative = 'careers/'.$job->slug.'/index.html';
            $written[$relative] = $this->write($temp, $relative, View::make('site.themes.crystal.jobs.show', [
                'job' => $job,
                'navigation' => $navigation,
                'buildYear' => date('Y'),
            ])->render());
        }

        $notFound = View::make('site.404', [
            'page' => $this->placeholderPage('Page not found'),
            'navigation' => $navigation,
            'buildYear' => date('Y'),
        ])->render();

        $written['404.html'] = $this->write($temp, '404.html', $notFound);
        $written['sitemap.xml'] = $this->write($temp, 'sitemap.xml', $this->sitemap->generate());
        $written['robots.txt'] = $this->write($temp, 'robots.txt', $this->robots->generate());

        return $written;
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
