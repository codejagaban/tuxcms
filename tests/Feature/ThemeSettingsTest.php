<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Support\InvalidThemeManifest;
use App\Support\ThemeManifest;
use Illuminate\Support\Facades\View;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/** A theme can declare settings, which editors then change under Settings. */
class ThemeSettingsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        View::addLocation(base_path('tests/Fixtures/views'));
        Sanctum::actingAs(User::factory()->create());
    }

    public function test_the_site_themes_settings_are_offered_in_their_groups(): void
    {
        config(['site.theme' => 'schematest']);

        $groups = $this->getJson('/api/v1/theme-settings')->assertOk()->json('data');

        $this->assertSame('Promotions', $groups[0]['group']);
        $this->assertSame(['promo_banner', 'promo_link'], array_column($groups[0]['fields'], 'key'));
        $this->assertSame('textarea', $groups[0]['fields'][0]['type']);
        $this->assertSame('Shown above the menu.', $groups[0]['fields'][0]['help']);
    }

    public function test_a_setting_the_core_already_shows_is_left_to_the_core(): void
    {
        config(['site.theme' => 'schematest']);

        $keys = collect($this->getJson('/api/v1/theme-settings')->json('data'))->flatMap(fn ($group) => array_column($group['fields'], 'key'));

        $this->assertNotContains('contact_email', $keys);
    }

    public function test_an_installed_theme_the_site_does_not_use_offers_nothing(): void
    {
        config(['site.theme' => 'shopfront']);

        $this->assertSame([], $this->getJson('/api/v1/theme-settings')->json('data'));
    }

    public function test_a_theme_setting_is_saved_like_any_other(): void
    {
        config(['site.theme' => 'schematest']);

        $this->putJson('/api/v1/settings', ['settings' => ['promo_banner' => 'Spring offer']])->assertOk();

        $this->assertSame('Spring offer', Setting::getString('promo_banner'));
    }

    public function test_the_list_needs_a_sign_in(): void
    {
        $this->app['auth']->forgetGuards();

        $this->getJson('/api/v1/theme-settings')->assertUnauthorized();
    }

    public function test_a_badly_declared_setting_is_named(): void
    {
        foreach ([
            [['Promo' => ['label' => 'Promo']], 'setting "Promo": a key must be'],
            [['promo' => []], 'setting "promo": "label" is required'],
            [['promo' => ['label' => 'Promo', 'type' => 'colour']], 'setting "promo" has unknown type "colour"'],
            [['promo' => ['label' => 'Promo', 'width' => 2]], 'setting "promo" has unknown keys: width'],
            [['promo', 'link'], '"settings" must be an object keyed by setting key'],
        ] as [$settings, $message]) {
            try {
                ThemeManifest::parseSettings('acme', $settings);
                $this->fail('The settings were accepted: '.json_encode($settings));
            } catch (InvalidThemeManifest $e) {
                $this->assertStringContainsString('Theme "acme": '.$message, $e->getMessage());
            }
        }
    }
}
