<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Mail\JobApplicationReceived;
use App\Models\JobApplication;
use App\Models\JobPost;
use App\Models\Setting;
use App\Support\SpamFilter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class JobApplicationController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'job' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'cover_letter' => ['required', 'string', 'max:10000'],
            'cv' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
            'website' => ['nullable', 'max:0'],
            // Signed proof of when the form page was opened.
            'form_token' => ['nullable'],
        ]);

        $job = JobPost::published()->where('slug', $validated['job'])->firstOrFail();
        $file = $request->file('cv');
        $path = $file->store("job-applications/{$job->id}", 'local');

        $application = JobApplication::create([
            'job_post_id' => $job->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'cover_letter' => $validated['cover_letter'],
            'cv_path' => $path,
            'cv_name' => $file->getClientOriginalName(),
        ]);

        // Junk is kept in the dashboard, with the reason, but never reaches
        // an inbox. Uncertain cases are emailed as normal and flagged.
        $check = SpamFilter::judge([$validated['cover_letter'], $validated['name']], $validated['form_token'] ?? null);

        if ($check['reason']) {
            $application->update(['spam_reason' => $check['reason']]);
        }

        if ($check['verdict'] === SpamFilter::SPAM) {
            $application->update(['delivery_status' => 'spam']);

            return $this->thanks($application);
        }

        $recipient = $job->application_email ?: Setting::getString('contact_email');
        try {
            Mail::to($recipient)->send(new JobApplicationReceived($application->load('jobPost')));
            $application->update(['delivery_status' => 'sent', 'emailed_at' => now()]);
        } catch (\Throwable $error) {
            $application->update(['delivery_status' => 'failed']);
            Log::error('Job application email failed', ['application_id' => $application->id, 'error' => $error->getMessage()]);
        }

        return $this->thanks($application);
    }

    private function thanks(JobApplication $application): JsonResponse
    {
        return response()->json([
            'message' => 'Thank you. Your application has been received.',
            'reference' => $application->id,
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        $applications = JobApplication::with('jobPost:id,title')
            ->latest()->paginate(min(max($request->integer('per_page', 20), 1), 100));

        $data = collect($applications->items())->map(fn (JobApplication $application) => [
            'id' => $application->id,
            'name' => $application->name,
            'email' => $application->email,
            'phone' => $application->phone,
            'cover_letter' => $application->cover_letter,
            'cv_name' => $application->cv_name,
            'delivery_status' => $application->delivery_status,
            'spam_reason' => $application->spam_reason,
            'emailed_at' => $application->emailed_at,
            'created_at' => $application->created_at,
            'job' => $application->jobPost ? [
                'id' => $application->jobPost->id,
                'title' => $application->jobPost->title,
            ] : null,
        ]);

        return response()->json(['data' => $data, 'meta' => [
            'current_page' => $applications->currentPage(),
            'last_page' => $applications->lastPage(),
            'total' => $applications->total(),
        ]]);
    }

    public function download(JobApplication $application): BinaryFileResponse
    {
        abort_unless(Storage::disk('local')->exists($application->cv_path), 404);

        return response()->download(
            Storage::disk('local')->path($application->cv_path),
            $application->cv_name
        );
    }
}
