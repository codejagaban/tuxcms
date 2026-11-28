<?php

namespace App\Support;

use App\Models\Page;
use App\Models\Setting;
use Illuminate\Support\Facades\View;

/**
 * Site-wide context for the public site.
 *
 * The static builder runs from the CLI, where url()->current() is meaningless,
 * so every public URL is built from the configured site URL instead.
 */
class Site
{
    /**
     * Installed themes: every directory under resources/views/site/themes.
     * Adding a theme folder is all it takes for its name to be accepted as a
     * page template and for its pages to be edited on the live preview.
     *
     * Every view location is searched, not only resources/views, so a test
     * that adds a location of its own can install a fixture theme.
     *
     * @return array<int, string>
     */
    public static function themes(): array
    {
        $themes = [];
        foreach (View::getFinder()->getPaths() as $root) {
            foreach (glob($root.'/site/themes/*', GLOB_ONLYDIR) ?: [] as $directory) {
                $themes[] = basename($directory);
            }
        }

        return array_values(array_unique($themes));
    }

    /**
     * The theme that renders a page with this template: the template's own
     * theme when it has a page view, else the site theme (SITE_THEME) when it
     * has one, else none, and the page is drawn by the base views.
     */
    public static function pageTheme(?string $template): ?string
    {
        $candidates = array_filter([
            $template ? str_replace('_', '-', $template) : null,
            config('site.theme'),
        ]);

        foreach ($candidates as $theme) {
            if (View::exists("site.themes.{$theme}.page")) {
                return $theme;
            }
        }

        return null;
    }

    /** Every template a page may use: the built-in layouts plus the themes. */
    public static function templates(): array
    {
        return array_values(array_unique([...config('tuxcms.pages.templates', []), ...self::themes()]));
    }

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

    /**
     * The logo named in structured data. Search engines want a plain image
     * they can show on white, which is often not the file a theme draws in
     * its header, so it has its own setting and falls back to the site logo.
     */
    public static function schemaLogo(): ?string
    {
        return self::absolute(Setting::getString('schema_logo')) ?: self::logo();
    }

    public static function defaultOgImage(): ?string
    {
        return self::absolute(Setting::getString('default_og_image'));
    }

    public static function web3formsKey(): ?string
    {
        return Setting::getString('web3forms_access_key');
    }

    /**
     * The wa.me link for the WhatsApp number in Settings, with the opening
     * message already typed, or null when no number is set. A theme decides
     * whether and how to show a button, and may give its own default opening
     * message; this only builds the address.
     *
     * The number is stored the way it is written on the page. A leading 0 is
     * read as a UK national number and becomes 44; a number given with its
     * country code is used as it is.
     */
    public static function whatsappUrl(?string $defaultMessage = null): ?string
    {
        $digits = preg_replace('/\D/', '', (string) Setting::getString('whatsapp_number'));

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        } elseif (str_starts_with($digits, '0')) {
            $digits = '44'.substr($digits, 1);
        }

        $message = Setting::getString('whatsapp_message')
            ?: $defaultMessage
            ?: 'Hello '.self::name().", I'd like to make an enquiry.";

        return 'https://wa.me/'.$digits.'?text='.rawurlencode($message);
    }

    public static function analyticsId(): ?string
    {
        return Setting::getString('google_analytics_id');
    }

    /** Non-empty social profile URLs, keyed by network. */
    public static function socialLinks(): array
    {
        $links = [];

        foreach (['facebook', 'twitter', 'instagram', 'linkedin', 'tiktok'] as $network) {
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
        // The contact email can be kept off the public site (pages, structured
        // data and llms.txt) so address harvesters find nothing, while forms
        // still deliver to it.
        $hideEmail = (bool) Setting::getString('hide_contact_email');

        return array_filter([
            'email' => $hideEmail ? null : Setting::getString('contact_email'),
            'phone' => Setting::getString('contact_phone'),
            'address' => Setting::getString('contact_address'),
        ]);
    }

    /**
     * A small editable link directory stored as JSON in Settings.
     * Invalid or unsafe rows are discarded before they reach a template.
     */
    public static function linkDirectory(string $key, array $fallback = []): array
    {
        $raw = Setting::getString($key);
        $links = $raw ? json_decode($raw, true) : $fallback;

        if (!is_array($links)) {
            return $fallback;
        }

        return collect($links)
            ->filter(fn ($link) => is_array($link)
                && filled($link['label'] ?? null)
                && filled($link['url'] ?? null)
                && (str_starts_with($link['url'], '/') || filter_var($link['url'], FILTER_VALIDATE_URL)))
            ->map(fn ($link) => [
                'label' => trim((string) $link['label']),
                'url' => trim((string) $link['url']),
            ])
            ->values()
            ->all();
    }

    /** Configured policy links; blank entries stay out of the public footer. */
    public static function legalLinks(): array
    {
        $labels = [
            'privacy_url' => 'Privacy',
            'terms_url' => 'Terms',
            'cookie_url' => 'Cookies',
        ];

        return collect($labels)->map(function ($label, $key) {
            $url = Setting::getString($key);
            if (!$url || (!str_starts_with($url, '/') && !filter_var($url, FILTER_VALIDATE_URL))) {
                return null;
            }
            return compact('label', 'url');
        })->filter()->values()->all();
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

}
