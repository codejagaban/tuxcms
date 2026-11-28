<?php

namespace Tests\Feature;

use App\Support\SectionTypes;
use Illuminate\Support\Facades\View;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/** The editor asks which section types a page may hold, and how to edit each. */
class SectionTypeEndpointTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        View::addLocation(base_path('tests/Fixtures/views'));
    }

    public function test_it_needs_an_editor(): void
    {
        $this->getJson('/api/v1/section-types')->assertUnauthorized();
    }

    public function test_it_lists_the_built_in_types_and_the_rendering_themes_own(): void
    {
        Sanctum::actingAs($this->editor());

        $response = $this->getJson('/api/v1/section-types?template=schematest')->assertOk();

        $response->assertJsonPath('data.theme', 'schematest');
        $types = collect($response->json('data.types'));
        $this->assertSame([...config('tuxcms.pages.section_types'), 'offers'], $types->pluck('type')->all());

        $offers = $types->firstWhere('type', 'offers');
        $this->assertSame('theme', $offers['source']);
        $this->assertSame('Offers', $offers['label']);
        $this->assertSame('Priced offers with what each includes.', $offers['blurb']);
        $this->assertSame('list', $offers['fields']['data.items']['type']);
        $this->assertSame('Offers', $offers['defaults']['title']);

        $hero = $types->firstWhere('type', 'hero');
        $this->assertSame('base', $hero['source']);
        $this->assertSame('Your headline goes here', $hero['defaults']['title']);
        $this->assertSame('select', $hero['fields']['data.alignment']['type']);
    }

    public function test_another_theme_or_the_base_views_get_the_built_in_types_only(): void
    {
        Sanctum::actingAs($this->editor());

        foreach (['crystal' => 'crystal', 'landing' => null, '' => null] as $template => $theme) {
            $response = $this->getJson('/api/v1/section-types?template='.$template)->assertOk();
            $response->assertJsonPath('data.theme', $theme);
            $this->assertSame(config('tuxcms.pages.section_types'), collect($response->json('data.types'))->pluck('type')->all());
        }

        // With no template of its own, the page is drawn by the site theme.
        config(['site.theme' => 'schematest']);
        $this->getJson('/api/v1/section-types?template=landing')->assertJsonPath('data.theme', 'schematest')
            ->assertJsonPath('data.types.11.type', 'offers');
    }

    public function test_an_unknown_template_is_refused(): void
    {
        Sanctum::actingAs($this->editor());

        $this->getJson('/api/v1/section-types?template=nope')->assertUnprocessable()->assertJsonValidationErrors('template');
    }

    public function test_the_built_in_schema_covers_exactly_the_built_in_types(): void
    {
        $this->assertSame(config('tuxcms.pages.section_types'), array_keys(SectionTypes::baseSchema()));
    }
}
