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
    protected function setUp(): void
    {
        parent::setUp();

        // Staff requests are Crystal's kind of enquiry, declared in its theme.json.
        config(['site.theme' => 'crystal']);
    }

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
            'form_token' => \App\Support\SpamFilter::issueToken(time() - 40),
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

    public function test_each_form_can_have_its_own_recipient(): void
    {
        Mail::fake();
        Setting::updateOrCreate(['key' => 'contact_email'], ['value' => 'office@example.com', 'group' => 'contact']);
        Setting::updateOrCreate(['key' => 'enquiry_email_contact'], ['value' => 'support@example.com', 'group' => 'contact']);

        $this->postJson('/api/v1/enquiries', $this->payload(['type' => 'contact']))->assertCreated();
        $this->postJson('/api/v1/enquiries', $this->payload())->assertCreated();

        Mail::assertSent(EnquiryReceived::class, fn ($mail) => $mail->enquiry->type === 'contact' && $mail->hasTo('support@example.com'));
        Mail::assertSent(EnquiryReceived::class, fn ($mail) => $mail->enquiry->type === 'staff_request' && $mail->hasTo('office@example.com'));
    }

    public function test_enquiry_is_kept_when_no_recipient_is_configured(): void
    {
        Mail::fake();
        Setting::where('key', 'contact_email')->orWhere('key', 'like', 'enquiry_email%')->delete();

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

    public function test_a_sales_pitch_is_held_and_not_emailed(): void
    {
        Mail::fake();
        Setting::updateOrCreate(['key' => 'contact_email'], ['value' => 'office@example.com', 'group' => 'contact']);

        $this->postJson('/api/v1/enquiries', $this->payload([
            'type' => 'contact',
            'message' => 'I was going through your website. We help businesses rank higher on Google.',
        ]))->assertCreated();

        $this->assertSame('spam', Enquiry::sole()->delivery_status);
        $this->assertStringContainsString('Sales pitch', Enquiry::sole()->spam_reason);
        Mail::assertNothingSent();
    }

    public function test_an_unclear_message_is_emailed_and_flagged_for_a_check(): void
    {
        Mail::fake();
        Setting::updateOrCreate(['key' => 'contact_email'], ['value' => 'office@example.com', 'group' => 'contact']);

        $this->postJson('/api/v1/enquiries', $this->payload([
            'message' => 'We offer WordPress support if that is ever useful to you.',
        ]))->assertCreated();

        $this->assertSame('sent', Enquiry::sole()->delivery_status);
        $this->assertStringContainsString('Possible sales pitch', Enquiry::sole()->spam_reason);
        Mail::assertSent(EnquiryReceived::class);
    }

    public function test_mentioning_a_website_or_app_alone_is_not_spam(): void
    {
        Mail::fake();
        Setting::updateOrCreate(['key' => 'contact_email'], ['value' => 'office@example.com', 'group' => 'contact']);

        $this->postJson('/api/v1/enquiries', $this->payload([
            'message' => 'Do you have a mobile app for booking shifts? We are a care home in Ashford.',
        ]))->assertCreated();

        $this->assertSame('sent', Enquiry::sole()->delivery_status);
        $this->assertNull(Enquiry::sole()->spam_reason);
    }

    public function test_a_submission_sent_too_fast_is_held(): void
    {
        Mail::fake();
        Setting::updateOrCreate(['key' => 'contact_email'], ['value' => 'office@example.com', 'group' => 'contact']);

        $this->postJson('/api/v1/enquiries', $this->payload(['form_token' => \App\Support\SpamFilter::issueToken()]))->assertCreated();
        $this->postJson('/api/v1/enquiries', $this->payload(['form_token' => (time() - 40).'.'.str_repeat('a', 64)]))->assertCreated();

        $this->assertSame(2, Enquiry::where('delivery_status', 'spam')->count());
        Mail::assertNothingSent();
    }

    public function test_a_repeated_or_foreign_script_message_is_held(): void
    {
        Mail::fake();
        Setting::updateOrCreate(['key' => 'contact_email'], ['value' => 'office@example.com', 'group' => 'contact']);
        $long = str_repeat('We need two healthcare assistants for nights next week. ', 3);

        $this->postJson('/api/v1/enquiries', $this->payload(['message' => $long]))->assertCreated();
        $this->postJson('/api/v1/enquiries', $this->payload(['message' => $long]))->assertCreated();
        $this->postJson('/api/v1/enquiries', $this->payload(['message' => 'Здравствуйте, предлагаем услуги продвижения сайтов']))->assertCreated();

        $this->assertSame(['sent', 'spam', 'spam'], Enquiry::orderBy('id')->pluck('delivery_status')->all());
    }

    public function test_the_site_can_fetch_a_form_token(): void
    {
        $token = $this->getJson('/api/v1/form-token')->assertOk()->json('token');

        $this->assertSame(0, \App\Support\SpamFilter::tokenAge($token));
    }

    public function test_a_held_enquiry_can_be_released_from_the_dashboard(): void
    {
        Mail::fake();
        Setting::updateOrCreate(['key' => 'contact_email'], ['value' => 'office@example.com', 'group' => 'contact']);
        $this->postJson('/api/v1/enquiries', $this->payload(['form_token' => null]))->assertCreated();
        $enquiry = Enquiry::sole();
        Mail::assertNothingSent();

        $this->postJson("/api/v1/enquiries/{$enquiry->id}/release")->assertUnauthorized();

        Sanctum::actingAs(User::factory()->create());
        $this->postJson("/api/v1/enquiries/{$enquiry->id}/release")->assertOk()->assertJsonPath('delivery_status', 'sent');

        $this->assertNull($enquiry->fresh()->spam_reason);
        Mail::assertSent(EnquiryReceived::class, fn ($mail) => $mail->hasTo('office@example.com'));

        // Only held enquiries can be released, so nothing is emailed twice.
        $this->postJson("/api/v1/enquiries/{$enquiry->id}/release")->assertStatus(422);
    }

    public function test_the_dashboard_can_list_only_held_enquiries_and_count_them(): void
    {
        Mail::fake();
        Setting::updateOrCreate(['key' => 'contact_email'], ['value' => 'office@example.com', 'group' => 'contact']);
        $this->postJson('/api/v1/enquiries', $this->payload())->assertCreated();
        $this->postJson('/api/v1/enquiries', $this->payload(['form_token' => null, 'name' => 'Bot']))->assertCreated();

        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/v1/enquiries')->assertOk()->assertJsonPath('meta.total', 2)->assertJsonPath('meta.held', 1);
        $this->getJson('/api/v1/enquiries?held=1')->assertOk()
            ->assertJsonPath('meta.total', 1)->assertJsonPath('data.0.name', 'Bot');
    }

    public function test_old_held_spam_is_deleted_and_everything_else_is_kept(): void
    {
        $make = fn (string $name, string $status, int $daysOld) => Enquiry::forceCreate([
            'type' => 'contact', 'name' => $name, 'email' => 'a@example.com',
            'delivery_status' => $status, 'created_at' => now()->subDays($daysOld),
        ]);
        $make('Old spam', 'spam', 31);
        $make('Recent spam', 'spam', 5);
        $make('Old genuine', 'sent', 400);

        $this->artisan('spam:prune')->assertSuccessful();

        $this->assertSame(['Recent spam', 'Old genuine'], Enquiry::orderBy('id')->pluck('name')->all());
    }
}
