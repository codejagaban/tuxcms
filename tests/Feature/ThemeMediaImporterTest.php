<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\User;
use App\Services\Media\ThemeMediaImporter;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Tests\TestCase;

class ThemeMediaImporterTest extends TestCase
{
    public function test_it_copies_the_photos_a_themes_pages_use_into_the_library_once(): void
    {
        Storage::fake('public');

        $page = Page::create([
            'title' => 'Home', 'slug' => 'home', 'template' => 'fineflow', 'status' => 'published',
            'user_id' => User::factory()->create()->id,
        ]);
        $page->sections()->create(['type' => 'hero', 'order' => 1, 'data' => [
            'image' => '/themes/fineflow/images/hero-welcome.webp',
            'image_alt' => 'A carer with a resident',
            'cards' => [['image' => '/themes/fineflow/images/kitchen.webp'], ['image' => '/themes/other/images/x.webp']],
        ]]);

        $importer = app(ThemeMediaImporter::class);

        $this->assertSame(
            ['/themes/fineflow/images/hero-welcome.webp' => 'A carer with a resident', '/themes/fineflow/images/kitchen.webp' => ''],
            $importer->discover('fineflow')
        );

        $this->assertSame(['imported' => 2, 'skipped' => 0, 'missing' => 0], $importer->import('fineflow'));
        $this->assertSame(['imported' => 0, 'skipped' => 2, 'missing' => 0], $importer->import('fineflow'));

        $hero = Media::all()->first(fn (Media $media) => $media->getCustomProperty('source_path') === '/themes/fineflow/images/hero-welcome.webp');
        $this->assertSame('A carer with a resident', $hero->getCustomProperty('alt_text'));
        $this->assertFileExists(public_path('themes/fineflow/images/hero-welcome.webp'));
    }
}
