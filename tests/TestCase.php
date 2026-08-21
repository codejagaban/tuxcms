<?php

namespace Tests;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    /** An authenticated editor, for API calls that require one. */
    protected function editor(): User
    {
        return User::factory()->create();
    }

    /**
     * Pretend we're in production.
     *
     * Site::isNoindex() deliberately returns true outside production so a
     * staging build can never be indexed. Anything asserting real robots or
     * canonical output has to opt into production first.
     */
    protected function asProduction(): void
    {
        // app()->environment() reads the value captured during bootstrap, so
        // setting config('app.env') alone has no effect.
        config(['app.env' => 'production']);
        $this->app->detectEnvironment(fn () => 'production');
    }

    /** Minimal site settings so the theme has something to render. */
    protected function seedSiteSettings(array $overrides = []): void
    {
        $defaults = [
            'site_name' => 'Test Site',
            'site_url' => 'https://example.test',
            'site_description' => 'A site used in tests.',
        ];

        foreach (array_merge($defaults, $overrides) as $key => $value) {
            Setting::set($key, $value, 'site');
        }
    }
}
