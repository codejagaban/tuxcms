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
            'title' => 'Home', 'slug' => 'home', 'template' => 'crystal', 'status' => 'published',
            'user_id' => User::factory()->create()->id,
        ]);
        $page->sections()->create(['type' => 'hero', 'order' => 1, 'data' => [
            'image' => '/themes/crystal/images/intro/hero.png',
            'image_alt' => 'A carer with a resident',
            'cards' => [['image' => '/themes/crystal/images/salon/braiding.jpg'], ['image' => '/themes/other/images/x.webp']],
        ]]);

        $importer = app(ThemeMediaImporter::class);

        $this->assertSame(
            ['/themes/crystal/images/intro/hero.png' => 'A carer with a resident', '/themes/crystal/images/salon/braiding.jpg' => ''],
            $importer->discover('crystal')
        );

        $this->assertSame(['imported' => 2, 'skipped' => 0, 'missing' => 0], $importer->import('crystal'));
        $this->assertSame(['imported' => 0, 'skipped' => 2, 'missing' => 0], $importer->import('crystal'));

        $hero = Media::all()->first(fn (Media $media) => $media->getCustomProperty('source_path') === '/themes/crystal/images/intro/hero.png');
        $this->assertSame('A carer with a resident', $hero->getCustomProperty('alt_text'));
        $this->assertFileExists(public_path('themes/crystal/images/intro/hero.png'));
    }
}
