<?php

namespace Tests\Feature;

use App\Mail\EnquiryReceived;
use App\Models\Enquiry;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EnquiryTest extends TestCase
{
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'type' => 'staff_request',
            'name' => 'Pat Manager',
            'organisation' => 'Oak Lodge Care Home',
            'email' => 'pat@example.com',
            'phone' => '01234 567890',
            'message' => 'Night cover needed this weekend.',
            'consent' => '1',
            'details' => ['roles' => ['Healthcare Assistant'], 'shifts' => ['Sat Night', 'Sun Night']],
        ], $overrides);
    }

    public function test_staff_request_is_stored_and_emailed(): void
    {
        Mail::fake();
        Setting::updateOrCreate(['key' => 'contact_email'], ['value' => 'office@example.com', 'group' => 'contact']);

        $this->postJson('/api/v1/enquiries', $this->payload())->assertCreated();

        $enquiry = Enquiry::sole();
        $this->assertSame(['Sat Night', 'Sun Night'], $enquiry->details['shifts']);
        $this->assertSame('sent', $enquiry->delivery_status);
        Mail::assertSent(EnquiryReceived::class, fn ($mail) => $mail->hasTo('office@example.com'));
    }

    public function test_enquiry_is_kept_when_no_recipient_is_configured(): void
    {
        Mail::fake();
        Setting::whereIn('key', ['contact_email', 'enquiry_email'])->delete();

        $this->postJson('/api/v1/enquiries', $this->payload())->assertCreated();

        $this->assertSame('failed', Enquiry::sole()->delivery_status);
        Mail::assertNothingSent();
    }

    public function test_enquiry_requires_consent_and_rejects_bots(): void
    {
        $this->postJson('/api/v1/enquiries', $this->payload(['consent' => null]))
            ->assertUnprocessable()->assertJsonValidationErrors('consent');

        $this->postJson('/api/v1/enquiries', $this->payload(['website' => 'http://spam.example']))
            ->assertUnprocessable()->assertJsonValidationErrors('website');

        $this->assertSame(0, Enquiry::count());
    }

    public function test_inbox_requires_authentication(): void
    {
        $this->getJson('/api/v1/enquiries')->assertUnauthorized();

        Enquiry::create(['type' => 'contact', 'name' => 'A', 'email' => 'a@example.com']);
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/v1/enquiries')->assertOk()->assertJsonPath('meta.total', 1);
    }
}
