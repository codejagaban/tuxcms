<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // Site settings
            ['key' => 'site_name', 'value' => ['TuxCMS'], 'group' => 'site'],
            ['key' => 'site_description', 'value' => ['A headless CMS with full SEO support'], 'group' => 'site'],
            ['key' => 'site_url', 'value' => [config('app.url')], 'group' => 'site'],
            ['key' => 'posts_per_page', 'value' => [10], 'group' => 'site'],
            ['key' => 'comments_enabled', 'value' => [true], 'group' => 'site'],
            ['key' => 'moderate_comments', 'value' => [true], 'group' => 'site'],

            // SEO settings
            ['key' => 'meta_keywords', 'value' => ['cms, blog, seo'], 'group' => 'seo'],
            ['key' => 'google_analytics_id', 'value' => [''], 'group' => 'seo'],
            ['key' => 'google_site_verification', 'value' => [''], 'group' => 'seo'],

            // Social settings
            ['key' => 'social_facebook', 'value' => [''], 'group' => 'social'],
            ['key' => 'social_twitter', 'value' => [''], 'group' => 'social'],
            ['key' => 'social_instagram', 'value' => [''], 'group' => 'social'],
            ['key' => 'social_linkedin', 'value' => [''], 'group' => 'social'],

            // Email settings
            ['key' => 'contact_email', 'value' => [config('mail.from.address')], 'group' => 'email'],
            ['key' => 'notification_email', 'value' => [config('mail.from.address')], 'group' => 'email'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
