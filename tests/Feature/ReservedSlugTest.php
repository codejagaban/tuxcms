<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * The dashboard ships at public/admin and the build writes into the same
 * docroot, so a page slugged `admin` would overwrite it on the next publish.
 */
class ReservedSlugTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Sanctum::actingAs(User::factory()->create());
    }

    public function test_it_rejects_a_reserved_top_level_slug(): void
    {
        $this->postJson('/api/v1/pages', ['title' => 'Control Panel', 'slug' => 'admin'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('slug');

        $this->assertDatabaseMissing('pages', ['slug' => 'admin']);
    }

    public function test_it_rejects_a_title_that_would_generate_a_reserved_slug(): void
    {
        $this->postJson('/api/v1/pages', ['title' => 'Admin'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('slug');
    }

    public function test_it_allows_a_reserved_word_beneath_a_parent(): void
    {
        $parent = Page::factory()->create(['slug' => 'about', 'parent_id' => null]);

        $this->postJson('/api/v1/pages', [
            'title' => 'Admin',
            'slug' => 'admin',
            'parent_id' => $parent->id,
        ])->assertStatus(201);

        $this->assertDatabaseHas('pages', ['slug' => 'admin', 'path' => '/about/admin']);
    }

    public function test_it_rejects_moving_a_page_onto_a_reserved_slug(): void
    {
        $page = Page::factory()->create(['slug' => 'contact', 'parent_id' => null]);

        $this->putJson("/api/v1/pages/{$page->id}", ['slug' => 'api'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('slug');
    }

    public function test_renaming_a_page_to_a_reserved_word_is_allowed(): void
    {
        // The model sets doNotGenerateSlugsOnUpdate(), so the slug is untouched
        // by a rename and there is nothing to collide.
        $page = Page::factory()->create(['title' => 'Contact', 'slug' => 'contact']);

        $this->putJson("/api/v1/pages/{$page->id}", ['title' => 'Admin'])
            ->assertStatus(200);

        $this->assertDatabaseHas('pages', ['id' => $page->id, 'slug' => 'contact']);
    }
}
