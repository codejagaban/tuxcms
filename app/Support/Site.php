<?php

namespace App\Support;

use App\Models\Page;
use App\Models\Setting;

/**
 * Site-wide context for the public site.
 *
 * The static builder runs from the CLI, where url()->current() is meaningless,
 * so every public URL is built from the configured site URL instead.
 */
class Site
{
    /** @var array<string, mixed>|null */
    private static ?array $cache = null;

    /** The canonical site root, with no trailing slash. */
    public static function url(): string
    {
        return rtrim(
            config('site.url')
                ?: Setting::getString('site_url')
                ?: config('app.url'),
            '/'
        );
    }

    /**
     * Absolute URL for a page. Paths always carry a trailing slash so that the
     * generated `about/index.html` is served without a redirect.
     */
    public static function urlFor(Page $page): string
    {
        return self::url() . self::pathFor($page);
    }

    /** Root-relative path for a page, with trailing slash. */
    public static function pathFor(Page $page): string
    {
        $path = $page->getFullPath();

        return $path === '/' ? '/' : rtrim($path, '/') . '/';
    }

    /** Make a possibly-relative URL absolute against the site root. */
    public static function absolute(?string $url): ?string
    {
        if (!$url) {
            return null;
        }

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        return self::url() . '/' . ltrim($url, '/');
    }

    public static function name(): string
    {
        return Setting::getString('site_name', config('app.name')) ?? 'Website';
    }

    public static function tagline(): ?string
    {
        return Setting::getString('site_tagline');
    }

    public static function description(): ?string
    {
        return Setting::getString('site_description');
    }

    public static function logo(): ?string
    {
        return self::absolute(Setting::getString('site_logo'));
    }

    public static function defaultOgImage(): ?string
    {
        return self::absolute(Setting::getString('default_og_image'));
    }

    public static function web3formsKey(): ?string
    {
        return Setting::getString('web3forms_access_key');
    }

    public static function analyticsId(): ?string
    {
        return Setting::getString('google_analytics_id');
    }

    /** Non-empty social profile URLs, keyed by network. */
    public static function socialLinks(): array
    {
        $links = [];

        foreach (['facebook', 'twitter', 'instagram', 'linkedin'] as $network) {
            $url = Setting::getString('social_' . $network);

            if ($url) {
                $links[$network] = $url;
            }
        }

        return $links;
    }

    /** Contact details used by the footer and Organization schema. */
    public static function contact(): array
    {
        return array_filter([
            'email' => Setting::getString('contact_email'),
            'phone' => Setting::getString('contact_phone'),
            'address' => Setting::getString('contact_address'),
        ]);
    }

    /** True when the whole site should be excluded from indexing. */
    public static function isNoindex(): bool
    {
        return (bool) Setting::getString('site_noindex')
            || !app()->environment('production');
    }

    /**
     * Root-relative <link> tags for the compiled stylesheet.
     *
     * Read straight from the Vite manifest rather than via @vite/asset(),
     * because during a CLI build those resolve against APP_URL and bake an
     * absolute host into every page — which breaks the moment the site moves
     * domain. Asset paths have no reason to be absolute.
     */
    public static function styleTags(): string
    {
        $manifestPath = public_path('build/manifest.json');

        if (!is_file($manifestPath)) {
            return '';
        }

        $manifest = json_decode(file_get_contents($manifestPath), true) ?: [];
        $entry = $manifest['resources/css/app.css'] ?? null;

        if (!$entry || empty($entry['file'])) {
            return '';
        }

        return '<link rel="stylesheet" href="/build/' . e($entry['file']) . '">';
    }

    /** Reset memoised settings — used between builds. */
    public static function flush(): void
    {
        self::$cache = null;
    }
}
