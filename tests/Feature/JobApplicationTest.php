<?php

namespace Tests\Feature;

use App\Mail\JobApplicationReceived;
use App\Models\JobApplication;
use App\Models\JobPost;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class JobApplicationTest extends TestCase
{
    public function test_candidate_can_apply_to_a_published_job(): void
    {
        Storage::fake('local');
        Mail::fake();
        $job = JobPost::create([
            'title' => 'Manager', 'slug' => 'manager', 'summary' => 'Lead the team',
            'status' => 'published', 'published_at' => now(),
            'application_email' => 'crystalservicesltd@outlook.com',
        ]);

        $response = $this->postJson('/api/v1/job-applications', [
            'job' => $job->slug,
            'name' => 'Jane Applicant',
            'email' => 'jane@example.com',
            'phone' => '07123456789',
            'cover_letter' => 'I have the required experience.',
            'cv' => UploadedFile::fake()->create('jane-cv.pdf', 200, 'application/pdf'),
        ]);

        $response->assertCreated()->assertJsonPath('message', 'Thank you. Your application has been received.');
        $application = JobApplication::sole();
        Storage::disk('local')->assertExists($application->cv_path);
        Mail::assertSent(JobApplicationReceived::class, fn ($mail) => $mail->hasTo('crystalservicesltd@outlook.com'));
        $this->assertSame('sent', $application->fresh()->delivery_status);
    }

    public function test_application_rejects_closed_jobs_and_unsafe_files(): void
    {
        Storage::fake('local');
        $job = JobPost::create(['title' => 'Closed', 'slug' => 'closed', 'summary' => 'Closed', 'status' => 'closed']);

        $this->postJson('/api/v1/job-applications', [
            'job' => $job->slug,
            'name' => 'Jane Applicant',
            'email' => 'jane@example.com',
            'cover_letter' => 'Application',
            'cv' => UploadedFile::fake()->create('payload.php', 10, 'application/x-php'),
        ])->assertUnprocessable()->assertJsonValidationErrors('cv');

        $this->assertSame(0, JobApplication::count());
    }

    public function test_application_listing_requires_authentication(): void
    {
        $this->getJson('/api/v1/job-applications')->assertUnauthorized();
        Sanctum::actingAs($this->editor());
        $this->getJson('/api/v1/job-applications')->assertOk();
    }

    public function test_editor_can_review_an_application_without_exposing_its_storage_path(): void
    {
        $job = JobPost::create([
            'title' => 'Manager', 'slug' => 'manager', 'summary' => 'Lead the team',
            'status' => 'published', 'published_at' => now(),
        ]);
        JobApplication::create([
            'job_post_id' => $job->id,
            'name' => 'Jane Applicant',
            'email' => 'jane@example.com',
            'cover_letter' => 'Experienced manager.',
            'cv_path' => 'job-applications/secret/cv.pdf',
            'cv_name' => 'jane-cv.pdf',
        ]);

        Sanctum::actingAs($this->editor());
        $response = $this->getJson('/api/v1/job-applications');

        $response->assertOk()
            ->assertJsonPath('data.0.name', 'Jane Applicant')
            ->assertJsonPath('data.0.job.title', 'Manager')
            ->assertJsonMissingPath('data.0.cv_path');
    }

    public function test_cv_download_is_private(): void
    {
        Storage::fake('local');
        $job = JobPost::create([
            'title' => 'Manager', 'slug' => 'manager', 'summary' => 'Lead the team',
            'status' => 'published', 'published_at' => now(),
        ]);
        Storage::disk('local')->put('job-applications/1/jane-cv.pdf', 'private cv');
        $application = JobApplication::create([
            'job_post_id' => $job->id,
            'name' => 'Jane Applicant',
            'email' => 'jane@example.com',
            'cover_letter' => 'Experienced manager.',
            'cv_path' => 'job-applications/1/jane-cv.pdf',
            'cv_name' => 'jane-cv.pdf',
        ]);

        $this->getJson("/api/v1/job-applications/{$application->id}/cv")->assertUnauthorized();

        Sanctum::actingAs($this->editor());
        $this->get("/api/v1/job-applications/{$application->id}/cv")
            ->assertOk()
            ->assertDownload('jane-cv.pdf');
    }

    public function test_deleting_a_job_preserves_its_applications(): void
    {
        $job = JobPost::create([
            'title' => 'Manager', 'slug' => 'manager', 'summary' => 'Lead the team',
            'status' => 'published', 'published_at' => now(),
        ]);
        $application = JobApplication::create([
            'job_post_id' => $job->id,
            'name' => 'Jane Applicant',
            'email' => 'jane@example.com',
            'cover_letter' => 'Experienced manager.',
            'cv_path' => 'job-applications/1/jane-cv.pdf',
            'cv_name' => 'jane-cv.pdf',
        ]);

        $job->delete();

        $this->assertDatabaseHas('job_applications', [
            'id' => $application->id,
            'job_post_id' => $job->id,
        ]);
    }
}
