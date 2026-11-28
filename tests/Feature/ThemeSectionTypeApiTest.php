<?php

namespace Tests\Feature;

use App\Models\Page;
use Illuminate\Support\Facades\View;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * A page may hold the section types its theme declares in theme.json, and
 * only on a page that theme renders. The fixture theme "schematest" declares
 * one type, "offers", with a list of priced items.
 */
class ThemeSectionTypeApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        View::addLocation(base_path('tests/Fixtures/views'));
        Sanctum::actingAs($this->editor());
    }

    private function offers(array $items = [['name' => 'Starter', 'price' => '£40', 'features' => ['One call']]]): array
    {
        return ['type' => 'offers', 'title' => 'Our offers', 'data' => ['intro' => '', 'layout' => 'grid', 'items' => $items]];
    }

    public function test_a_theme_type_is_accepted_on_a_page_its_theme_renders(): void
    {
        $this->postJson('/api/v1/pages', [
            'title' => 'Prices', 'template' => 'schematest', 'sections' => [$this->offers()],
        ])->assertCreated()->assertJsonPath('data.sections.0.data.items.0.name', 'Starter');
    }

    public function test_the_site_theme_counts_when_the_template_has_no_theme(): void
    {
        config(['site.theme' => 'schematest']);

        $this->postJson('/api/v1/pages', [
            'title' => 'Prices', 'template' => 'landing', 'sections' => [$this->offers()],
        ])->assertCreated();
    }

    public function test_a_theme_type_is_refused_on_a_page_another_theme_renders(): void
    {
        $this->postJson('/api/v1/pages', [
            'title' => 'Prices', 'template' => 'shopfront', 'sections' => [$this->offers()],
        ])->assertUnprocessable()->assertJsonValidationErrors([
            'sections.0.type' => 'The shopfront theme does not provide the section type "offers", so the section "Our offers" (offers) cannot be shown on this page.',
        ]);
    }

    public function test_a_theme_type_is_refused_on_a_page_the_base_views_render(): void
    {
        $this->postJson('/api/v1/pages', [
            'title' => 'Prices', 'template' => 'landing', 'sections' => [$this->offers()],
        ])->assertUnprocessable()->assertJsonValidationErrors(['sections.0.type' => 'The base site views do not provide']);
    }

    public function test_built_in_types_are_still_accepted_everywhere(): void
    {
        foreach (['schematest', 'shopfront', 'landing'] as $template) {
            $this->postJson('/api/v1/pages', [
                'title' => "Home {$template}", 'template' => $template, 'sections' => [['type' => 'hero', 'title' => 'Hi']],
            ])->assertCreated();
        }
    }

    public function test_the_schema_is_enforced(): void
    {
        $this->postJson('/api/v1/pages', [
            'title' => 'Prices', 'template' => 'schematest', 'sections' => [$this->offers([
                ['price' => '£40'],
                ['name' => 'Plus', 'price' => '£80', 'url' => 'not a link', 'seats' => 'many', 'featured' => 'perhaps', 'features' => 'one'],
            ])],
        ])->assertUnprocessable()->assertJsonValidationErrors([
            'sections.0.data.items.0.name' => 'The Offer 1 Name field is required.',
            'sections.0.data.items.1.url',
            'sections.0.data.items.1.seats',
            'sections.0.data.items.1.featured',
            'sections.0.data.items.1.features',
        ]);

        $this->postJson('/api/v1/pages', [
            'title' => 'Prices', 'template' => 'schematest', 'sections' => [['type' => 'offers', 'data' => ['layout' => 'carousel']]],
        ])->assertUnprocessable()->assertJsonValidationErrors(['sections.0.data.layout']);
    }

    public function test_links_may_be_web_addresses_or_paths_on_the_site(): void
    {
        foreach (['https://example.com/book', '/contact', '#prices', 'mailto:hi@example.com', 'tel:+441234'] as $url) {
            $this->postJson('/api/v1/pages', [
                'title' => 'Prices '.md5($url), 'template' => 'schematest',
                'sections' => [$this->offers([['name' => 'A', 'price' => '1', 'url' => $url]])],
            ])->assertCreated();
        }
    }

    public function test_a_template_switch_that_strands_sections_is_refused_and_names_them(): void
    {
        $page = Page::factory()->create(['template' => 'schematest']);
        $page->sections()->create([...$this->offers(), 'order' => 0]);
        $page->sections()->create(['type' => 'hero', 'title' => 'Welcome', 'order' => 1]);

        $this->putJson("/api/v1/pages/{$page->id}", ['template' => 'shopfront'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'template' => 'The shopfront theme does not provide every section this page holds. Remove or replace the section "Our offers" (offers) first.',
            ]);
        $this->assertSame('schematest', $page->fresh()->template);
        $this->assertSame(2, $page->sections()->count(), 'Nothing is dropped.');

        // Sent together with the sections, the stranded one is named in place.
        $this->putJson("/api/v1/pages/{$page->id}", [
            'template' => 'shopfront',
            'sections' => $page->sections()->orderBy('order')->get()->map->only(['id', 'type', 'title', 'data'])->all(),
        ])->assertUnprocessable()->assertJsonValidationErrors(['sections.0.type' => 'the section "Our offers" (offers)']);

        // A switch that keeps every section drawable goes through.
        $page->sections()->where('type', 'offers')->delete();
        $this->putJson("/api/v1/pages/{$page->id}", ['template' => 'shopfront'])->assertOk();
    }

    public function test_saving_without_touching_the_template_keeps_the_page_theme(): void
    {
        $page = Page::factory()->create(['template' => 'schematest']);

        $this->putJson("/api/v1/pages/{$page->id}", ['sections' => [$this->offers()]])->assertOk();
    }
}
