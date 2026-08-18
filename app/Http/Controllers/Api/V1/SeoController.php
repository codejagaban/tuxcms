<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\SeoResource;
use App\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * SEO metadata is always attached to a Page — the previous polymorphic
 * model_type API pointed at Post/Category models that no longer exist.
 */
class SeoController extends Controller
{
    public function show(Page $page): JsonResponse
    {
        return response()->json([
            'data' => new SeoResource($page->seo),
        ]);
    }

    public function update(Request $request, Page $page): JsonResponse
    {
        $validated = $request->validate([
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:500',
            'og_title' => 'nullable|string|max:255',
            'og_description' => 'nullable|string|max:500',
            'og_image' => 'nullable|string|max:500',
            'og_type' => 'nullable|string|max:50',
            'twitter_card' => 'nullable|string|max:50',
            'twitter_title' => 'nullable|string|max:255',
            'twitter_description' => 'nullable|string|max:500',
            'twitter_image' => 'nullable|string|max:500',
            'canonical_url' => 'nullable|url|max:500',
            'robots' => 'nullable|string|max:100',
            'schema_markup' => 'nullable|array',
            'custom_head' => 'nullable|string|max:5000',
        ]);

        $seo = $page->updateSeo($validated);

        return response()->json([
            'data' => new SeoResource($seo),
            'message' => 'SEO metadata updated successfully.',
        ]);
    }
}
