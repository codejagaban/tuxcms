<?php

namespace Tests\Feature;

use App\Models\Enquiry;
use App\Models\Page;
use App\Models\User;
use App\Support\EnquiryKinds;
use App\Support\InvalidThemeManifest;
use App\Support\SpamFilter;
use App\Support\ThemeManifest;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * The kinds of message the inbox takes come from the themes a site uses, so
 * the core names no client's forms. Every site has `contact`.
 */
class EnquiryKindsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        View::addLocation(base_path('tests/Fixtures/views'));
    }

    private function send(string $type)
    {
        return $this->postJson('/api/v1/enquiries', [
            'type' => $type, 'name' => 'Sam', 'email' => 'sam@example.com', 'consent' => '1',
            'message' => 'Hello there, a question about your services.',
            'form_token' => SpamFilter::issueToken(time() - 40),
        ]);
    }

    public function test_a_site_with_no_declared_kinds_takes_only_contact(): void
    {
        config(['site.theme' => null]);

        $this->assertSame(['contact'], array_keys(EnquiryKinds::declared()));
        $this->send('contact')->assertCreated();
        $this->send('staff_request')->assertUnprocessable()->assertJsonValidationErrors('type');
    }

    public function test_the_site_theme_adds_its_kinds_and_can_rename_contact(): void
    {
        config(['site.theme' => 'schematest']);

        $this->send('quote_request')->assertCreated();
        $this->assertSame('Quote request', Enquiry::sole()->typeLabel());
        $this->assertSame(['label' => 'Message', 'plural' => 'Messages'], EnquiryKinds::declared()['contact']);
    }

    public function test_a_theme_the_pages_use_adds_its_kinds_too(): void
    {
        config(['site.theme' => null]);
        Page::create(['title' => 'Quotes', 'slug' => 'quotes', 'template' => 'schematest', 'status' => 'published']);

        $this->send('quote_request')->assertCreated();
    }

    public function test_an_installed_theme_the_site_does_not_use_adds_nothing(): void
    {
        config(['site.theme' => 'shopfront']);

        // Crystal is installed and declares staff requests, but this site does not use it.
        $this->assertArrayNotHasKey('staff_request', EnquiryKinds::declared());
        $this->send('staff_request')->assertUnprocessable();
    }

    public function test_the_inbox_lists_the_kinds_and_keeps_older_ones_that_hold_messages(): void
    {
        config(['site.theme' => 'schematest']);
        Enquiry::create(['type' => 'old_form', 'name' => 'A', 'email' => 'a@example.com']);
        Sanctum::actingAs(User::factory()->create());

        $kinds = $this->getJson('/api/v1/enquiry-kinds')->assertOk()->json('data');

        $this->assertSame(['contact', 'quote_request', 'old_form'], array_column($kinds, 'value'));
        $this->assertSame('Quote requests', $kinds[1]['plural']);
        $this->assertSame('enquiry_email_quote_request', $kinds[1]['setting']);
        $this->assertSame('Old form', $kinds[2]['label']);
        $this->assertSame($kinds, $this->getJson('/api/v1/enquiries')->json('meta.kinds'));
    }

    public function test_the_kinds_endpoint_needs_a_sign_in(): void
    {
        $this->getJson('/api/v1/enquiry-kinds')->assertUnauthorized();
    }

    public function test_a_badly_declared_kind_is_named(): void
    {
        foreach ([
            [['Quote' => ['label' => 'Quote']], 'enquiry kind "Quote": a kind must be'],
            [['quote' => []], 'enquiry kind "quote": "label" is required'],
            [['quote' => ['label' => 'Quote', 'colour' => 'red']], 'enquiry kind "quote" has unknown keys: colour'],
            [['quote', 'review'], '"enquiries" must be an object keyed by kind'],
        ] as [$kinds, $message]) {
            try {
                ThemeManifest::parseEnquiries('acme', $kinds);
                $this->fail('The kinds were accepted: '.json_encode($kinds));
            } catch (InvalidThemeManifest $e) {
                $this->assertStringContainsString('Theme "acme": '.$message, $e->getMessage());
            }
        }
    }
}
