<?php

namespace App\Services\Media;

use App\Models\MediaLibrary;
use App\Models\Page;
use Illuminate\Support\Str;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class CrystalMediaImporter
{
    /** @return array{imported: int, skipped: int, missing: int} */
    public function import(): array
    {
        $library = MediaLibrary::singleton();
        $existing = Media::query()
            ->where('model_type', MediaLibrary::class)
            ->where('model_id', $library->getKey())
            ->get()
            ->mapWithKeys(fn (Media $media) => [$media->getCustomProperty('source_path') => true]);

        $result = ['imported' => 0, 'skipped' => 0, 'missing' => 0];

        foreach ($this->discover() as $sourcePath => $altText) {
            if ($existing->has($sourcePath)) {
                $result['skipped']++;

                continue;
            }

            $absolutePath = public_path(ltrim($sourcePath, '/'));
            if (! is_file($absolutePath)) {
                $result['missing']++;

                continue;
            }

            $name = Str::headline(pathinfo($absolutePath, PATHINFO_FILENAME));
            $library->addMedia($absolutePath)
                ->preservingOriginal()
                ->usingName($name)
                ->withCustomProperties([
                    'alt_text' => $altText ?: $name,
                    'source_path' => $sourcePath,
                    'imported_from_theme' => 'crystal',
                ])
                ->toMediaCollection('library');

            $existing->put($sourcePath, true);
            $result['imported']++;
        }

        return $result;
    }

    /** @return array<string, string> */
    public function discover(): array
    {
        $images = [];

        Page::query()->where('template', 'crystal')->with('sections')->get()
            ->each(function (Page $page) use (&$images): void {
                foreach ($page->sections as $section) {
                    $this->collectSectionImages($section->data ?? [], $images);
                }
            });

        $directory = resource_path('views/site/themes/crystal/original');
        if (is_dir($directory)) {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
            foreach ($files as $file) {
                if (! $file->isFile() || $file->getExtension() !== 'php') {
                    continue;
                }
                preg_match_all(
                    '#/themes/crystal/images/[^"\'\s>]+\.(?:jpe?g|png|webp|gif)#i',
                    file_get_contents($file->getPathname()),
                    $matches
                );
                foreach (array_unique($matches[0]) as $path) {
                    if ($this->isContentImage($path)) {
                        $images[$path] ??= Str::headline(pathinfo($path, PATHINFO_FILENAME));
                    }
                }
            }
        }

        ksort($images);

        return $images;
    }

    /** @param array<string, string> $images */
    private function collectSectionImages(mixed $value, array &$images): void
    {
        if (! is_array($value)) {
            return;
        }

        foreach ($value as $key => $child) {
            if (is_string($child) && in_array($key, ['image', 'image_url', 'background_image', 'src'], true) && $this->isContentImage($child)) {
                $images[$child] = (string) ($value['image_alt'] ?? $value['title'] ?? pathinfo($child, PATHINFO_FILENAME));
            } elseif (is_array($child)) {
                $this->collectSectionImages($child, $images);
            }
        }
    }

    private function isContentImage(string $path): bool
    {
        if (! preg_match('#^/themes/crystal/images/.+\.(?:jpe?g|png|webp|gif)$#i', $path)) {
            return false;
        }

        return ! preg_match('#/(?:logo|logotypes|originals|showcases|intro-sections|design-concepts)/|/(?:decoration-|bg-shape-|favicon)#i', $path);
    }
}
