<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        // Values are stored as scalars. Setting::getString() unwraps legacy
        // array-wrapped values defensively, but new data should be flat.
        $settings = [
            // Site
            ['key' => 'site_name', 'value' => 'TuxCMS Site', 'group' => 'site'],
            ['key' => 'site_tagline', 'value' => '', 'group' => 'site'],
            ['key' => 'site_description', 'value' => 'A business website built with TuxCMS.', 'group' => 'site'],
            ['key' => 'site_url', 'value' => config('app.url'), 'group' => 'site'],
            ['key' => 'site_logo', 'value' => '', 'group' => 'site'],

            // SEO
            ['key' => 'meta_keywords', 'value' => '', 'group' => 'seo'],
            ['key' => 'default_og_image', 'value' => '', 'group' => 'seo'],
            ['key' => 'google_analytics_id', 'value' => '', 'group' => 'seo'],
            ['key' => 'google_site_verification', 'value' => '', 'group' => 'seo'],
            ['key' => 'robots_extra', 'value' => '', 'group' => 'seo'],
            ['key' => 'site_noindex', 'value' => '', 'group' => 'seo'],

            // Forms — Web3Forms handles submissions; this is the site-wide key.
            ['key' => 'web3forms_access_key', 'value' => '', 'group' => 'forms'],

            // Social
            ['key' => 'social_facebook', 'value' => '', 'group' => 'social'],
            ['key' => 'social_twitter', 'value' => '', 'group' => 'social'],
            ['key' => 'social_instagram', 'value' => '', 'group' => 'social'],
            ['key' => 'social_linkedin', 'value' => '', 'group' => 'social'],

            // Contact
            ['key' => 'contact_email', 'value' => config('mail.from.address'), 'group' => 'contact'],
            ['key' => 'contact_phone', 'value' => '', 'group' => 'contact'],
            ['key' => 'contact_address', 'value' => '', 'group' => 'contact'],

            // Footer directories. Stored as JSON so the dashboard can manage
            // ordered label and URL rows without requiring a schema change.
            ['key' => 'footer_intro', 'value' => '', 'group' => 'footer'],
            ['key' => 'footer_support_email', 'value' => '', 'group' => 'footer'],
            ['key' => 'footer_cleaning_links', 'value' => '', 'group' => 'footer'],
            ['key' => 'footer_salon_links', 'value' => '', 'group' => 'footer'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
