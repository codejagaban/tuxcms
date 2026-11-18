<?php

namespace App\Services\Media;

use App\Models\MediaLibrary;
use App\Models\Page;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * A theme ships its photos as files, which the Media page cannot see. This
 * copies every photo the theme's pages use into the media library, so they
 * can be browsed there and picked again in the editor. The originals stay
 * where they are and pages keep pointing at them, so nothing on the site
 * changes. Running it twice imports nothing twice.
 */
class ThemeMediaImporter
{
    /** @return array{imported: int, skipped: int, missing: int} */
    public function import(string $theme): array
    {
        $library = MediaLibrary::singleton();
        $existing = Media::query()
            ->where('model_type', MediaLibrary::class)
            ->where('model_id', $library->getKey())
            ->get()
            ->mapWithKeys(fn (Media $media) => [$media->getCustomProperty('source_path') => true]);

        $result = ['imported' => 0, 'skipped' => 0, 'missing' => 0];

        foreach ($this->discover($theme) as $sourcePath => $altText) {
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
                    'imported_from_theme' => $theme,
                ])
                ->toMediaCollection('library');

            $existing->put($sourcePath, true);
            $result['imported']++;
        }

        return $result;
    }

    /**
     * Photos used by the theme's pages, with the description each was given.
     *
     * @return array<string, string>
     */
    public function discover(string $theme): array
    {
        $images = [];
        $prefix = "/themes/{$theme}/images/";

        Page::query()->where('template', $theme)->with('sections')->get()
            ->each(function (Page $page) use (&$images, $prefix): void {
                foreach ($page->sections as $section) {
                    $this->collect($section->data ?? [], $images, $prefix);
                }
            });

        ksort($images);

        return $images;
    }

    /** @param array<string, string> $images */
    private function collect(mixed $value, array &$images, string $prefix): void
    {
        if (! is_array($value)) {
            return;
        }

        foreach ($value as $key => $child) {
            if (is_array($child)) {
                $this->collect($child, $images, $prefix);
            } elseif (is_string($child) && str_starts_with($child, $prefix)
                && preg_match('/\.(?:jpe?g|png|webp|gif)$/i', $child)) {
                // The description sits beside the photo: `image` + `image_alt`.
                $alt = (string) ($value[$key.'_alt'] ?? (in_array($key, ['image', 'src'], true) ? ($value['alt'] ?? '') : ''));
                // Keep the first real description if the photo is used twice.
                $images[$child] = ($images[$child] ?? '') ?: $alt;
            }
        }
    }
}
