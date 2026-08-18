<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\SeoResource;
use App\Models\Seo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SeoController extends Controller
{
    public function getSeo(Request $request): SeoResource
    {
        $request->validate([
            'model_type' => 'required|string|in:App\Models\Post,App\Models\Category',
            'model_id' => 'required|integer',
        ]);

        $seo = Seo::where('seoable_type', $request->model_type)
            ->where('seoable_id', $request->model_id)
            ->firstOrFail();

        return new SeoResource($seo);
    }

    public function updateSeo(Request $request): SeoResource
    {
        $request->validate([
            'model_type' => 'required|string|in:App\Models\Post,App\Models\Category',
            'model_id' => 'required|integer',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string',
            'og_title' => 'nullable|string|max:255',
            'og_description' => 'nullable|string|max:500',
            'og_image' => 'nullable|string',
            'og_type' => 'nullable|string|max:50',
            'twitter_card' => 'nullable|string|max:50',
            'twitter_title' => 'nullable|string|max:255',
            'twitter_description' => 'nullable|string|max:500',
            'twitter_image' => 'nullable|string',
            'canonical_url' => 'nullable|url',
            'robots' => 'nullable|string|max:100',
            'schema_markup' => 'nullable|json',
            'custom_head' => 'nullable|string',
        ]);

        $seo = Seo::updateOrCreate(
            [
                'seoable_type' => $request->model_type,
                'seoable_id' => $request->model_id,
            ],
            $request->except('model_type', 'model_id')
        );

        return new SeoResource($seo);
    }

    public function getMetaTags(Request $request): JsonResponse
    {
        $request->validate([
            'slug' => 'required|string',
            'type' => 'required|string|in:post,category',
        ]);

        $model = match ($request->type) {
            'post' => \App\Models\Post::where('slug', $request->slug)->firstOrFail(),
            'category' => \App\Models\Category::where('slug', $request->slug)->firstOrFail(),
        };

        return response()->json([
            'meta_tags' => $model->getMetaTags(),
            'og_tags' => $model->getOgTags(),
            'twitter_tags' => $model->getTwitterTags(),
            'schema_markup' => $model->getSchemaMarkup(),
        ]);
    }
}
