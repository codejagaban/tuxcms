<?php

namespace App\Http\Requests\Concerns;

use Illuminate\Support\Str;
use Illuminate\Validation\Validator;

/**
 * Rejects a top-level slug that collides with a real directory in the docroot.
 *
 * The generated site is written into Laravel's own public/, so a page slugged
 * `admin` would silently overwrite the dashboard's index.html on the next
 * publish. Only top-level pages can collide — /about/admin is a subdirectory
 * and harmless.
 *
 * Callers pass the value that will actually become the slug. That differs by
 * verb: on create an omitted slug is generated from the title, but the model
 * sets doNotGenerateSlugsOnUpdate(), so on update only an explicit slug counts
 * and a rename must not be flagged.
 */
trait ChecksReservedSlugs
{
    protected function failOnReservedSlug(Validator $validator, ?string $candidate, ?int $parentId): void
    {
        if ($candidate === null || $parentId !== null) {
            return;
        }

        $slug = Str::slug($candidate);

        if (in_array($slug, config('site.reserved_slugs', []), true)) {
            $validator->errors()->add(
                'slug',
                "The slug \"{$slug}\" is reserved by the CMS. Choose another."
            );
        }
    }
}
