<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Mail\EnquiryReceived;
use App\Models\Enquiry;
use App\Models\Setting;
use App\Support\EnquiryKinds;
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
            'type' => ['required', Rule::in(array_keys(EnquiryKinds::declared()))],
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
            // Signed proof of when the form page was opened.
            'form_token' => ['nullable'],
        ]);

        $check = SpamFilter::judge(
            [$validated['message'] ?? null, $validated['organisation'] ?? null, $validated['name']],
            $validated['form_token'] ?? null
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

        // The same long message arriving again is a mass mailing, not a person.
        $message = trim((string) $enquiry->message);
        if ($check['verdict'] !== SpamFilter::SPAM && mb_strlen($message) >= 80
            && Enquiry::where('id', '!=', $enquiry->id)->where('message', $enquiry->message)->exists()) {
            $check = ['verdict' => SpamFilter::SPAM, 'reason' => 'Same message received before'];
        }

        // Keep the reason so a wrong call can be spotted in the dashboard.
        if ($check['reason']) {
            $enquiry->update(['spam_reason' => $check['reason']]);
        }

        // Junk is kept in the dashboard but never reaches an inbox. The
        // sender gets the usual reply, so they learn nothing from it.
        // Uncertain cases carry on and are emailed as normal.
        if ($check['verdict'] === SpamFilter::SPAM) {
            $enquiry->update(['delivery_status' => 'spam']);

            return $this->thanks($enquiry);
        }

        $this->deliver($enquiry);

        return $this->thanks($enquiry);
    }

    /** Send a held enquiry on to the business after all. */
    public function release(Enquiry $enquiry): JsonResponse
    {
        abort_unless($enquiry->delivery_status === 'spam', 422, 'This enquiry is not being held.');

        $enquiry->update(['spam_reason' => null]);
        $this->deliver($enquiry);

        return response()->json([
            'delivery_status' => $enquiry->delivery_status,
            'message' => $enquiry->delivery_status === 'sent'
                ? 'Sent to the inbox.'
                : 'Marked as not spam, but the email could not be sent.',
        ]);
    }

    /**
     * Email the enquiry to the business. It is already stored, so a mail
     * failure loses nothing: the dashboard shows it either way, flagged as
     * undelivered.
     */
    private function deliver(Enquiry $enquiry): void
    {
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
            ->when($request->boolean('held'), fn ($q) => $q->where('delivery_status', 'spam'))
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
                'spam_reason' => $enquiry->spam_reason,
                'created_at' => $enquiry->created_at,
            ]),
            'meta' => [
                'current_page' => $enquiries->currentPage(),
                'last_page' => $enquiries->lastPage(),
                'total' => $enquiries->total(),
                // How many the spam filter is holding, whatever the filter.
                'held' => Enquiry::where('delivery_status', 'spam')->count(),
                // The tabs: the kinds this site's forms send.
                'kinds' => $this->kindList(),
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

    /** The kinds of enquiry this site takes, for the inbox's tabs and Settings. */
    public function kinds(): JsonResponse
    {
        return response()->json(['data' => $this->kindList()]);
    }

    /** @return array<int, array{value: string, label: string, plural: string, setting: string}> */
    private function kindList(): array
    {
        return collect(EnquiryKinds::shown())->map(fn (array $kind, string $value) => [
            'value' => $value,
            'label' => $kind['label'],
            'plural' => $kind['plural'],
            // Where a different notification address for this kind is kept.
            'setting' => 'enquiry_email_'.$value,
        ])->values()->all();
    }
}
