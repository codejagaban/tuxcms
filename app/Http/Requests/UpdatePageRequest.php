<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ChecksReservedSlugs;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdatePageRequest extends FormRequest
{
    use ChecksReservedSlugs;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|max:255',
            'content' => 'nullable|string',
            'excerpt' => 'nullable|string|max:1000',
            'template' => 'nullable|string|max:100',
            'status' => 'nullable|string|in:draft,published,archived',
            'parent_id' => 'nullable|integer|exists:pages,id',
            'order' => 'nullable|integer|min:0',
            'is_homepage' => 'nullable|boolean',
            'show_in_nav' => 'nullable|boolean',
            'nav_label' => 'nullable|string|max:255',
            'custom_fields' => 'nullable|array',
            'published_at' => 'nullable|date',

            'sections' => 'nullable|array',
            'sections.*.id' => 'nullable|integer',
            'sections.*.key' => 'nullable|string|max:100',
            'sections.*.type' => 'required_with:sections|string|max:50',
            'sections.*.title' => 'nullable|string|max:255',
            'sections.*.content' => 'nullable|string',
            'sections.*.data' => 'nullable|array',
            'sections.*.order' => 'nullable|integer|min:0',
            'sections.*.is_visible' => 'nullable|boolean',

            'seo' => 'nullable|array',
            'seo.meta_title' => 'nullable|string|max:255',
            'seo.meta_description' => 'nullable|string|max:500',
            'seo.meta_keywords' => 'nullable|string|max:500',
            'seo.og_title' => 'nullable|string|max:255',
            'seo.og_description' => 'nullable|string|max:500',
            'seo.og_image' => 'nullable|string|max:500',
            'seo.og_type' => 'nullable|string|max:50',
            'seo.twitter_card' => 'nullable|string|max:50',
            'seo.twitter_title' => 'nullable|string|max:255',
            'seo.twitter_description' => 'nullable|string|max:500',
            'seo.twitter_image' => 'nullable|string|max:500',
            'seo.canonical_url' => 'nullable|url|max:500',
            'seo.robots' => 'nullable|string|max:100',
            'seo.schema_markup' => 'nullable|array',
            'seo.custom_head' => 'nullable|string|max:5000',
        ];
    }

    public function after(): array
    {
        return [
            fn (Validator $validator) => $this->failOnReservedSlug(
                $validator,
                // Renaming a page leaves its slug alone, so only an explicit
                // slug can introduce a collision here.
                $this->input('slug'),
                // A partial update need not resend parent_id; fall back to the
                // page's current parent so a nested page is not falsely flagged.
                $this->filled('parent_id')
                    ? (int) $this->input('parent_id')
                    : $this->route('page')?->parent_id,
            ),
        ];
    }
}
