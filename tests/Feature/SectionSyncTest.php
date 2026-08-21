<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\PageSection;
use Illuminate\Http\UploadedFile;
use Laravel\Sanctum\Sanctum;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Tests\TestCase;

/**
 * Saving a page reconciles its sections rather than deleting and recreating
 * them. The old approach churned ids on every save and, because a mass delete
 * fires no model events, left attached media orphaned in storage.
 */
class SectionSyncTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Sanctum::actingAs($this->editor());
    }

    private function payload(array $sections): array
    {
        return ['sections' => $sections];
    }

    private function sectionPayload(PageSection $section, array $overrides = []): array
    {
        return array_merge([
            'id' => $section->id,
            'key' => $section->key,
            'type' => $section->type,
            'title' => $section->title,
            'content' => $section->content,
            'data' => $section->data ?? [],
            'order' => $section->order,
            'is_visible' => $section->is_visible,
        ], $overrides);
    }

    public function test_section_ids_are_stable_across_a_save(): void
    {
        $page = Page::factory()->create();
        $a = $page->sections()->create(['key' => 'hero', 'type' => 'hero', 'title' => 'One', 'order' => 0]);
        $b = $page->sections()->create(['key' => 'cta', 'type' => 'cta', 'title' => 'Two', 'order' => 1]);

        $this->putJson("/api/v1/pages/{$page->id}", $this->payload([
            $this->sectionPayload($a, ['title' => 'One edited']),
            $this->sectionPayload($b),
        ]))->assertOk();

        $ids = $page->fresh()->sections->pluck('id')->all();

        $this->assertSame([$a->id, $b->id], $ids, 'Existing sections should be updated in place.');
        $this->assertSame('One edited', $a->fresh()->title);
    }

    public function test_media_attached_to_a_section_survives_a_save(): void
    {
        $page = Page::factory()->create();
        $section = $page->sections()->create(['key' => 'hero', 'type' => 'hero', 'order' => 0]);

        $section->addMedia(UploadedFile::fake()->image('bg.jpg'))
            ->toMediaCollection('section_background');

        $this->assertSame(1, $section->fresh()->getMedia('section_background')->count());

        $this->putJson("/api/v1/pages/{$page->id}", $this->payload([
            $this->sectionPayload($section->fresh(), ['title' => 'Changed']),
        ]))->assertOk();

        $this->assertSame(
            1,
            $section->fresh()->getMedia('section_background')->count(),
            'Editing a page must not detach its section images.'
        );
    }

    public function test_removing_a_section_deletes_its_media(): void
    {
        $page = Page::factory()->create();
        $keep = $page->sections()->create(['key' => 'hero', 'type' => 'hero', 'order' => 0]);
        $drop = $page->sections()->create(['key' => 'gallery', 'type' => 'gallery', 'order' => 1]);

        $drop->addMedia(UploadedFile::fake()->image('old.jpg'))->toMediaCollection('section_images');
        $this->assertSame(1, Media::count());

        $this->putJson("/api/v1/pages/{$page->id}", $this->payload([
            $this->sectionPayload($keep),
        ]))->assertOk();

        $this->assertCount(1, $page->fresh()->sections);
        $this->assertSame(0, Media::count(), 'A removed section should take its media with it.');
    }

    public function test_new_sections_are_created(): void
    {
        $page = Page::factory()->create();
        $existing = $page->sections()->create(['key' => 'hero', 'type' => 'hero', 'order' => 0]);

        $this->putJson("/api/v1/pages/{$page->id}", $this->payload([
            $this->sectionPayload($existing),
            ['id' => null, 'key' => 'cta', 'type' => 'cta', 'title' => 'Fresh', 'data' => [], 'order' => 1, 'is_visible' => true],
        ]))->assertOk();

        $sections = $page->fresh()->sections;

        $this->assertCount(2, $sections);
        $this->assertSame('Fresh', $sections->last()->title);
    }

    public function test_reordering_preserves_ids(): void
    {
        $page = Page::factory()->create();
        $a = $page->sections()->create(['key' => 'a', 'type' => 'hero', 'order' => 0]);
        $b = $page->sections()->create(['key' => 'b', 'type' => 'cta', 'order' => 1]);

        $this->putJson("/api/v1/pages/{$page->id}", $this->payload([
            $this->sectionPayload($b, ['order' => 0]),
            $this->sectionPayload($a, ['order' => 1]),
        ]))->assertOk();

        $ordered = $page->fresh()->sections;

        $this->assertSame([$b->id, $a->id], $ordered->pluck('id')->all());
        $this->assertSame(['b', 'a'], $ordered->pluck('key')->all());
    }

    /** A client must not be able to hijack another page's section by id. */
    public function test_a_section_id_from_another_page_is_not_adopted(): void
    {
        $pageA = Page::factory()->create();
        $pageB = Page::factory()->create();
        $foreign = $pageB->sections()->create(['key' => 'theirs', 'type' => 'text', 'title' => 'Theirs', 'order' => 0]);

        $this->putJson("/api/v1/pages/{$pageA->id}", $this->payload([
            ['id' => $foreign->id, 'key' => 'mine', 'type' => 'hero', 'title' => 'Mine', 'data' => [], 'order' => 0, 'is_visible' => true],
        ]))->assertOk();

        // The foreign section is untouched and still belongs to page B.
        $foreign->refresh();
        $this->assertSame('Theirs', $foreign->title);
        $this->assertSame($pageB->id, $foreign->page_id);

        // Page A got a brand new section instead.
        $this->assertCount(1, $pageA->fresh()->sections);
        $this->assertNotSame($foreign->id, $pageA->fresh()->sections->first()->id);
    }

    public function test_sending_an_empty_section_list_clears_them(): void
    {
        $page = Page::factory()->create();
        $page->sections()->create(['key' => 'hero', 'type' => 'hero', 'order' => 0]);

        $this->putJson("/api/v1/pages/{$page->id}", $this->payload([]))->assertOk();

        $this->assertCount(0, $page->fresh()->sections);
    }

    public function test_omitting_sections_leaves_them_untouched(): void
    {
        $page = Page::factory()->create();
        $page->sections()->create(['key' => 'hero', 'type' => 'hero', 'order' => 0]);

        $this->putJson("/api/v1/pages/{$page->id}", ['title' => 'Renamed only'])->assertOk();

        $this->assertCount(1, $page->fresh()->sections);
    }
}
