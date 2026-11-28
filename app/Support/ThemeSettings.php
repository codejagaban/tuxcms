<?php

namespace App\Support;

/**
 * Site-wide values a theme reads, declared under `settings` in the theme.json
 * of a theme the site uses, so editors can change them under Settings instead
 * of only through the API:
 *
 *     "settings": {
 *       "footer_notice": { "label": "Footer notice", "type": "textarea", "group": "Footer" }
 *     }
 *
 * The values are ordinary settings, read with Setting::getString() and saved
 * through the settings endpoints; this only says which exist and how to
 * offer them. A key the core's own Settings screen already shows is left to
 * the core, so a theme cannot put the same box on the page twice.
 */
class ThemeSettings
{
    /** Settings the core's own Settings screen shows. */
    private const CORE = [
        'site_name', 'site_description', 'site_url', 'site_tagline', 'site_logo', 'meta_keywords', 'default_og_image',
        'schema_logo', 'google_analytics_id', 'google_site_verification', 'robots_extra', 'site_noindex',
        'web3forms_access_key', 'social_facebook', 'social_twitter', 'social_instagram', 'social_linkedin',
        'social_tiktok', 'contact_email', 'hide_contact_email', 'contact_phone', 'contact_phone_2', 'contact_address',
        'company_number', 'whatsapp_number', 'whatsapp_message', 'enquiry_email', 'footer_intro',
        'footer_support_email', 'privacy_url', 'terms_url', 'cookie_url', 'google_reviews_url',
        'chatwoot_website_token', 'chatwoot_base_url', 'tawk_property_id', 'umami_website_id', 'umami_script_url',
    ];

    /**
     * The declared settings, in groups, in the order the themes list them.
     *
     * @return array<int, array{group: string, fields: array<int, array{key: string, label: string, type: string, help: ?string, placeholder: ?string}>}>
     */
    public static function groups(): array
    {
        $groups = [];

        foreach (Site::themesInUse() as $theme) {
            foreach (ThemeManifest::settings($theme) as $key => $setting) {
                if (in_array($key, self::CORE, true) || str_starts_with($key, 'enquiry_email_') || preg_match('/^footer_.+_links$/', $key)) {
                    continue;
                }
                $groups[$setting['group']][$key] = ['key' => $key] + array_diff_key($setting, ['group' => true]);
            }
        }

        return collect($groups)->map(fn ($fields, $group) => ['group' => $group, 'fields' => array_values($fields)])->values()->all();
    }
}
