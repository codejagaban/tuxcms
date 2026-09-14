<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Setting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PublishTest extends TestCase
{
    private string $output;

    protected function setUp(): void
    {
        parent::setUp();

        $this->output = storage_path('framework/testing/publish-'.uniqid());
        File::ensureDirectoryExists($this->output);
        config(['site.output_path' => $this->output, 'site.url' => 'https://example.test']);

        $this->seedSiteSettings();
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->output);

        parent::tearDown();
    }

    public function test_publishing_requires_authentication(): void
    {
        $this->postJson('/api/v1/site/build')->assertUnauthorized();
        $this->getJson('/api/v1/site/status')->assertUnauthorized();
    }

    public function test_publishing_writes_the_site(): void
    {
        Sanctum::actingAs($this->editor());
        Page::factory()->homepage()->create(['title' => 'Welcome']);

        $response = $this->postJson('/api/v1/site/build');

        $response->assertOk()->assertJsonPath('data.pages', 1);
        $this->assertFileExists($this->output.'/index.html');
    }

    public function test_status_reports_pending_changes_before_the_first_publish(): void
    {
        Sanctum::actingAs($this->editor());
        Page::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/site/status');

        $response->assertOk();
        $this->assertSame(3, $response->json('data.pending_changes'));
        $this->assertNull($response->json('data.last_published_at'));
    }

    public function test_publishing_clears_pending_changes(): void
    {
        Sanctum::actingAs($this->editor());
        Page::factory()->homepage()->create();

        $this->postJson('/api/v1/site/build')->assertOk();

        $status = $this->getJson('/api/v1/site/status');

        $this->assertSame(0, $status->json('data.pending_changes'));
        $this->assertNotNull($status->json('data.last_published_at'));
        $this->assertTrue($status->json('data.has_output'));
    }

    public function test_editing_after_publishing_marks_changes_pending_again(): void
    {
        Sanctum::actingAs($this->editor());
        $page = Page::factory()->homepage()->create();

        $this->postJson('/api/v1/site/build')->assertOk();
        $this->assertSame(0, $this->getJson('/api/v1/site/status')->json('data.pending_changes'));

        $this->travel(2)->minutes();
        $page->update(['title' => 'Changed']);

        $this->assertSame(1, $this->getJson('/api/v1/site/status')->json('data.pending_changes'));
    }

    public function test_settings_changes_are_reported_as_pending(): void
    {
        Sanctum::actingAs($this->editor());
        Page::factory()->homepage()->create();
        Setting::set('site_name', 'Before', 'site');
        $this->postJson('/api/v1/site/build')->assertOk();

        $this->travel(2)->seconds();
        $this->putJson('/api/v1/settings', ['settings' => ['site_name' => 'After']])->assertOk();

        $this->assertSame(1, $this->getJson('/api/v1/site/status')->json('data.pending_changes'));
    }

    public function test_media_changes_are_reported_as_pending(): void
    {
        Sanctum::actingAs($this->editor());
        Page::factory()->homepage()->create();
        $this->postJson('/api/v1/site/build')->assertOk();

        $this->travel(2)->seconds();
        $this->postJson('/api/v1/media', [
            'file' => UploadedFile::fake()->image('new-image.png'),
        ])->assertCreated();

        $this->assertSame(1, $this->getJson('/api/v1/site/status')->json('data.pending_changes'));
    }

    public function test_page_deletion_is_reported_as_pending(): void
    {
        Sanctum::actingAs($this->editor());
        $page = Page::factory()->homepage()->create();
        $this->postJson('/api/v1/site/build')->assertOk();

        $this->travel(2)->seconds();
        $this->deleteJson("/api/v1/pages/{$page->id}")->assertOk();

        $this->assertSame(1, $this->getJson('/api/v1/site/status')->json('data.pending_changes'));
    }
}
