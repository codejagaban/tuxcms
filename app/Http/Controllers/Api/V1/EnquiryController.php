<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Mail\EnquiryReceived;
use App\Models\Enquiry;
use App\Models\Setting;
use App\Support\SpamFilter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class EnquiryController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(Enquiry::TYPES)],
            'name' => ['required', 'string', 'max:255'],
            'organisation' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'message' => ['nullable', 'string', 'max:10000'],
            'consent' => ['accepted'],
            // Free-form but bounded: short scalar values or flat lists.
            'details' => ['nullable', 'array', 'max:20'],
            'details.*' => ['nullable'],
            // Honeypot — people never see it, bots fill it.
            'website' => ['nullable', 'max:0'],
            // Seconds the form was open, reported by the site script.
            'form_time' => ['nullable'],
        ]);

        $spam = SpamFilter::isSpam(
            [$validated['message'] ?? null, $validated['organisation'] ?? null, $validated['name']],
            $validated['form_time'] ?? null
        );

        $enquiry = Enquiry::create([
            'type' => $validated['type'],
            'name' => $validated['name'],
            'organisation' => $validated['organisation'] ?? null,
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'message' => $validated['message'] ?? null,
            'details' => $this->cleanDetails($validated['details'] ?? []),
        ]);

        // Junk is kept for review in the dashboard but never reaches an inbox.
        // The sender gets the usual reply, so they learn nothing from it.
        if ($spam) {
            $enquiry->update(['delivery_status' => 'spam']);

            return $this->thanks($enquiry);
        }

        // The enquiry is already stored, so a mail failure loses nothing: the
        // dashboard inbox shows it either way, flagged as undelivered.
        // Each form can have its own inbox; without one it falls back to the
        // general enquiry address, then the public contact email.
        $recipient = Setting::getString('enquiry_email_'.$enquiry->type)
            ?: Setting::getString('enquiry_email')
            ?: Setting::getString('contact_email');

        try {
            if (! $recipient) {
                throw new \RuntimeException('No enquiry recipient is configured.');
            }

            Mail::to($recipient)->send(new EnquiryReceived($enquiry));
            $enquiry->update(['delivery_status' => 'sent', 'emailed_at' => now()]);
        } catch (\Throwable $error) {
            $enquiry->update(['delivery_status' => 'failed']);
            Log::error('Enquiry email failed', ['enquiry_id' => $enquiry->id, 'error' => $error->getMessage()]);
        }

        return $this->thanks($enquiry);
    }

    private function thanks(Enquiry $enquiry): JsonResponse
    {
        return response()->json([
            'message' => 'Thank you. We have your details and will be in touch shortly.',
            'reference' => $enquiry->id,
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        $enquiries = Enquiry::query()
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->string('type')))
            ->latest()
            ->paginate(min(max($request->integer('per_page', 20), 1), 100));

        return response()->json([
            'data' => collect($enquiries->items())->map(fn (Enquiry $enquiry) => [
                'id' => $enquiry->id,
                'type' => $enquiry->type,
                'type_label' => $enquiry->typeLabel(),
                'name' => $enquiry->name,
                'organisation' => $enquiry->organisation,
                'email' => $enquiry->email,
                'phone' => $enquiry->phone,
                'message' => $enquiry->message,
                'details' => $enquiry->details ?? [],
                'delivery_status' => $enquiry->delivery_status,
                'created_at' => $enquiry->created_at,
            ]),
            'meta' => [
                'current_page' => $enquiries->currentPage(),
                'last_page' => $enquiries->lastPage(),
                'total' => $enquiries->total(),
            ],
        ]);
    }

    /** Keep only short scalars and flat lists of them. */
    private function cleanDetails(array $details): array
    {
        $clean = [];

        foreach ($details as $key => $value) {
            $key = substr(preg_replace('/[^a-z0-9_]/', '', strtolower((string) $key)), 0, 40);

            if ($key === '') {
                continue;
            }

            if (is_array($value)) {
                $value = array_values(array_filter(
                    array_map(fn ($item) => is_scalar($item) ? substr(trim((string) $item), 0, 200) : null, array_slice($value, 0, 60)),
                    fn ($item) => $item !== null && $item !== ''
                ));
            } elseif (is_scalar($value)) {
                $value = substr(trim((string) $value), 0, 500);
            } else {
                continue;
            }

            if ($value !== '' && $value !== []) {
                $clean[$key] = $value;
            }
        }

        return $clean;
    }
}
