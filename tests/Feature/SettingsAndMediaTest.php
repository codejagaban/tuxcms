<?php

namespace Tests\Feature;

use App\Models\MediaLibrary;
use App\Models\Page;
use App\Models\Setting;
use Illuminate\Http\UploadedFile;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SettingsAndMediaTest extends TestCase
{
    // ── Settings ───────────────────────────────────────────

    public function test_get_string_unwraps_array_wrapped_values(): void
    {
        // Older seeds stored every value wrapped in an array.
        Setting::create(['key' => 'legacy', 'value' => ['Wrapped'], 'group' => 'site']);
        Setting::create(['key' => 'modern', 'value' => 'Plain', 'group' => 'site']);

        $this->assertSame('Wrapped', Setting::getString('legacy'));
        $this->assertSame('Plain', Setting::getString('modern'));
        $this->assertSame('fallback', Setting::getString('missing', 'fallback'));
    }

    public function test_bulk_update_requires_authentication(): void
    {
        $this->putJson('/api/v1/settings', ['settings' => ['site_name' => 'Nope']])
            ->assertUnauthorized();
    }

    public function test_reading_settings_requires_authentication(): void
    {
        $this->getJson('/api/v1/settings')->assertUnauthorized();
        $this->getJson('/api/v1/settings/grouped')->assertUnauthorized();
        $this->getJson('/api/v1/settings/site_name')->assertUnauthorized();
    }

    public function test_editor_can_bulk_update_settings(): void
    {
        Sanctum::actingAs($this->editor());
        Setting::set('site_name', 'Old Name', 'site');

        $response = $this->putJson('/api/v1/settings', [
            'settings' => [
                'site_name' => 'New Name',
                'web3forms_access_key' => 'key-123',
            ],
        ]);

        $response->assertOk();
        $this->assertSame('New Name', Setting::getString('site_name'));
        $this->assertSame('key-123', Setting::getString('web3forms_access_key'));
    }

    public function test_bulk_update_preserves_the_existing_group(): void
    {
        Sanctum::actingAs($this->editor());
        Setting::set('site_name', 'Old', 'site');

        $this->putJson('/api/v1/settings', ['settings' => ['site_name' => 'New']])->assertOk();

        $this->assertSame('site', Setting::where('key', 'site_name')->first()->group);
    }

    // ── Media ──────────────────────────────────────────────

    public function test_reading_media_metadata_requires_authentication(): void
    {
        $this->getJson('/api/v1/media')->assertUnauthorized();
        $this->getJson('/api/v1/media/1')->assertUnauthorized();
    }

    public function test_upload_without_a_model_goes_to_the_site_library(): void
    {
        Sanctum::actingAs($this->editor());

        $response = $this->postJson('/api/v1/media', [
            'file' => UploadedFile::fake()->image('logo.png'),
        ]);

        $response->assertCreated()->assertJsonPath('data.collection_name', 'library');
        $this->assertSame(1, MediaLibrary::count(), 'The library owner should be created once.');
    }

    public function test_upload_can_attach_to_a_page(): void
    {
        Sanctum::actingAs($this->editor());
        $page = Page::factory()->create();

        $response = $this->postJson('/api/v1/media', [
            'file' => UploadedFile::fake()->image('hero.jpg'),
            'model_type' => 'Page',
            'model_id' => $page->id,
            'collection' => 'featured_image',
        ]);

        $response->assertCreated();
        $this->assertSame(1, $page->fresh()->getMedia('featured_image')->count());
    }

    /** Guards against class names being built from raw input. */
    public function test_unknown_model_types_are_rejected(): void
    {
        Sanctum::actingAs($this->editor());

        $this->postJson('/api/v1/media', [
            'file' => UploadedFile::fake()->image('x.png'),
            'model_type' => 'User',
            'model_id' => 1,
        ])->assertStatus(422);
    }

    public function test_disallowed_file_types_are_rejected(): void
    {
        Sanctum::actingAs($this->editor());

        $this->postJson('/api/v1/media', [
            'file' => UploadedFile::fake()->create('payload.php', 10, 'application/x-php'),
        ])->assertStatus(422);
    }

    public function test_upload_requires_authentication(): void
    {
        $this->postJson('/api/v1/media', ['file' => UploadedFile::fake()->image('x.png')])
            ->assertUnauthorized();
    }
}
