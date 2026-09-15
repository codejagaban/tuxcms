<?php

namespace Tests\Feature;

use App\Models\JobPost;
use App\Models\Page;
use App\Models\User;
use App\Services\Site\SiteBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class JobPostTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_management_requires_authentication(): void
    {
        $this->getJson('/api/v1/jobs')->assertUnauthorized();
        $this->postJson('/api/v1/jobs', [])->assertUnauthorized();
    }

    public function test_editor_can_create_and_update_a_job(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $payload = $this->payload();
        $id = $this->postJson('/api/v1/jobs', $payload)->assertCreated()->json('data.id');
        $this->putJson('/api/v1/jobs/'.$id, [...$payload, 'status' => 'published'])
            ->assertOk()->assertJsonPath('data.status', 'published');
        $this->assertNotNull(JobPost::find($id)->published_at);
    }

    public function test_publishing_builds_careers_listing_and_job_page(): void
    {
        config(['site.output_path' => storage_path('framework/testing/jobs-site')]);
        JobPost::create([...$this->payload(), 'status' => 'published', 'published_at' => now()]);
        app(SiteBuilder::class)->build();
        $this->assertFileExists(storage_path('framework/testing/jobs-site/careers/index.html'));
        $this->assertFileExists(storage_path('framework/testing/jobs-site/careers/business-development-manager/index.html'));
        $this->assertStringContainsString('Business Development Manager', file_get_contents(storage_path('framework/testing/jobs-site/careers/index.html')));
        $detail = file_get_contents(storage_path('framework/testing/jobs-site/careers/business-development-manager/index.html'));
        $this->assertStringContainsString('JobPosting', $detail);
        $this->assertStringContainsString('mailto:jobs@example.com', $detail);
        $sitemap = file_get_contents(storage_path('framework/testing/jobs-site/sitemap.xml'));
        $this->assertStringContainsString('/careers/business-development-manager/', $sitemap);
    }

    public function test_careers_navigation_and_empty_listing_are_always_built(): void
    {
        config(['site.output_path' => storage_path('framework/testing/jobs-site-empty')]);
        Page::factory()->homepage()->create();

        app(SiteBuilder::class)->build();

        $home = file_get_contents(storage_path('framework/testing/jobs-site-empty/index.html'));
        $careers = file_get_contents(storage_path('framework/testing/jobs-site-empty/careers/index.html'));

        $this->assertSame(3, substr_count($home, 'href="/careers/"'));
        $this->assertStringContainsString('There are no open roles at the moment.', $careers);
    }

    private function payload(): array
    {
        return ['title' => 'Business Development Manager', 'slug' => 'business-development-manager', 'location' => 'Worcestershire, UK', 'job_type' => 'Full-time', 'hours' => '37.5 hours', 'salary' => '£56,000', 'employment_type' => 'Permanent', 'summary' => 'Lead business growth.', 'description' => 'Develop new opportunities.', 'responsibilities' => ['Generate leads'], 'essential' => ['Sales experience'], 'desirable' => [], 'benefits' => ['Permanent role'], 'application_email' => 'jobs@example.com', 'status' => 'draft', 'published_at' => null, 'closes_at' => null];
    }
}
