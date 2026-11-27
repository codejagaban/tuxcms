<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\PostCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PostCategoryController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => PostCategory::query()->withCount('posts')->orderBy('name')->get()
            ->map(fn (PostCategory $category) => $this->present($category))]);
    }

    public function store(Request $request): JsonResponse
    {
        $category = PostCategory::create($this->validated($request));

        return response()->json(['data' => $this->present($category)], 201);
    }

    public function update(Request $request, PostCategory $category): JsonResponse
    {
        $category->update($this->validated($request, $category));

        return response()->json(['data' => $this->present($category->loadCount('posts'))]);
    }

    /** Posts in a deleted category stay, without a category. */
    public function destroy(PostCategory $category): JsonResponse
    {
        $category->delete();

        return response()->json(null, 204);
    }

    /** The address is made from the name unless one is given. */
    private function validated(Request $request, ?PostCategory $category = null): array
    {
        $request->merge(['slug' => Str::slug($request->input('slug') ?: (string) $request->input('name'))]);

        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'string', 'max:100', Rule::unique('post_categories', 'slug')->ignore($category)],
        ], ['slug.unique' => 'A category with that name already exists.']);
    }

    private function present(PostCategory $category): array
    {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'posts_count' => (int) ($category->posts_count ?? 0),
        ];
    }
}
