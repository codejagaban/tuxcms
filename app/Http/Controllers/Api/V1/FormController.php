<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\FormResource;
use App\Http\Resources\FormSubmissionResource;
use App\Models\Form;
use App\Models\FormSubmission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FormController extends Controller
{
    /**
     * List all forms (admin only sees all, public sees active only).
     */
    public function index(Request $request): JsonResponse
    {
        $query = Form::query();

        if (!$request->user()) {
            $query->active();
        }

        $forms = $query->get();

        return response()->json([
            'data' => FormResource::collection($forms),
        ]);
    }

    /**
     * Get a single form by slug or ID (returns field schema for frontend rendering).
     */
    public function show(string $slugOrId): JsonResponse
    {
        if (is_numeric($slugOrId)) {
            $form = Form::active()->findOrFail($slugOrId);
        } else {
            $form = Form::active()->where('slug', $slugOrId)->firstOrFail();
        }

        return response()->json([
            'data' => new FormResource($form),
        ]);
    }

    /**
     * Submit a form (public endpoint).
     * Validates dynamically based on the form's field configuration.
     */
    public function submit(Request $request, string $slugOrId): JsonResponse
    {
        if (is_numeric($slugOrId)) {
            $form = Form::active()->findOrFail($slugOrId);
        } else {
            $form = Form::active()->where('slug', $slugOrId)->firstOrFail();
        }

        // Build validation rules from form field config
        $rules = $form->getValidationRules();
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Only store fields defined in the form schema
        $fieldNames = collect($form->fields)->pluck('name')->toArray();
        $submissionData = $request->only($fieldNames);

        $submission = $form->submissions()->create([
            'data' => $submissionData,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'message' => $form->success_message,
            'data' => [
                'id' => $submission->id,
            ],
        ], 201);
    }

    // ── Admin endpoints (authenticated) ────────────────────

    /**
     * Create a new form.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'fields' => 'required|array|min:1',
            'fields.*.name' => 'required|string|max:100',
            'fields.*.label' => 'required|string|max:255',
            'fields.*.type' => 'required|string|in:text,email,tel,textarea,select,checkbox,radio,number,date,file,url,hidden',
            'fields.*.required' => 'boolean',
            'fields.*.placeholder' => 'nullable|string|max:255',
            'fields.*.validation' => 'nullable|string|max:500',
            'fields.*.options' => 'nullable|array',
            'fields.*.options.*' => 'string|max:255',
            'notification_email' => 'nullable|email|max:255',
            'success_message' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $form = Form::create($validated);

        return response()->json([
            'data' => new FormResource($form),
            'message' => 'Form created successfully.',
        ], 201);
    }

    /**
     * Update a form.
     */
    public function update(Request $request, Form $form): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'fields' => 'sometimes|array|min:1',
            'fields.*.name' => 'required_with:fields|string|max:100',
            'fields.*.label' => 'required_with:fields|string|max:255',
            'fields.*.type' => 'required_with:fields|string|in:text,email,tel,textarea,select,checkbox,radio,number,date,file,url,hidden',
            'fields.*.required' => 'boolean',
            'fields.*.placeholder' => 'nullable|string|max:255',
            'fields.*.validation' => 'nullable|string|max:500',
            'fields.*.options' => 'nullable|array',
            'notification_email' => 'nullable|email|max:255',
            'success_message' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $form->update($validated);

        return response()->json([
            'data' => new FormResource($form),
            'message' => 'Form updated successfully.',
        ]);
    }

    /**
     * Delete a form.
     */
    public function destroy(Form $form): JsonResponse
    {
        $form->delete();

        return response()->json([
            'message' => 'Form deleted successfully.',
        ]);
    }

    /**
     * List submissions for a form.
     */
    public function submissions(Request $request, Form $form): JsonResponse
    {
        $submissions = $form->submissions()
            ->when($request->input('unread_only'), fn($q) => $q->unread())
            ->orderByDesc('created_at')
            ->paginate($request->input('per_page', 20));

        return response()->json([
            'data' => FormSubmissionResource::collection($submissions),
            'meta' => [
                'current_page' => $submissions->currentPage(),
                'last_page' => $submissions->lastPage(),
                'per_page' => $submissions->perPage(),
                'total' => $submissions->total(),
                'unread_count' => $form->submissions()->unread()->count(),
            ],
        ]);
    }

    /**
     * View a single submission.
     */
    public function showSubmission(Form $form, FormSubmission $submission): JsonResponse
    {
        if ($submission->form_id !== $form->id) {
            abort(404);
        }

        $submission->markAsRead();

        return response()->json([
            'data' => new FormSubmissionResource($submission),
        ]);
    }

    /**
     * Delete a submission.
     */
    public function destroySubmission(Form $form, FormSubmission $submission): JsonResponse
    {
        if ($submission->form_id !== $form->id) {
            abort(404);
        }

        $submission->delete();

        return response()->json([
            'message' => 'Submission deleted successfully.',
        ]);
    }
}
