<?php

namespace Tests\Feature;

use App\Models\MediaLibrary;
use App\Services\Media\CrystalMediaImporter;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Tests\TestCase;

class CrystalMediaImporterTest extends TestCase
{
    public function test_it_imports_referenced_crystal_images_once_without_moving_the_sources(): void
    {
        Storage::fake('public');

        $importer = app(CrystalMediaImporter::class);
        $images = $importer->discover();

        $this->assertArrayHasKey('/themes/crystal/images/intro/hero.png', $images);
        $this->assertArrayHasKey('/themes/crystal/images/salon/braiding.jpg', $images);
        $this->assertArrayNotHasKey('/themes/crystal/images/logo/favicon.png', $images);

        $first = $importer->import();
        $second = $importer->import();

        $this->assertGreaterThan(0, $first['imported']);
        $this->assertSame(0, $first['missing']);
        $this->assertSame(0, $second['imported']);
        $this->assertSame($first['imported'], $second['skipped']);
        $this->assertSame($first['imported'], Media::query()->where('collection_name', 'library')->count());
        $this->assertFileExists(public_path('themes/crystal/images/intro/hero.png'));

        $hero = Media::query()
            ->where('model_type', MediaLibrary::class)
            ->get()
            ->first(fn (Media $media) => $media->getCustomProperty('source_path') === '/themes/crystal/images/intro/hero.png');

        $this->assertNotNull($hero);
        $this->assertSame('crystal', $hero->getCustomProperty('imported_from_theme'));
    }
}
