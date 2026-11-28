<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PageResource;
use App\Models\Page;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2|max:200',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $query = $request->input('q');
        $limit = $request->input('limit', 15);

        $pages = Page::published()
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('content', 'like', "%{$query}%")
                    ->orWhere('excerpt', 'like', "%{$query}%");
            })
            ->with(['seo', 'media'])
            ->orderByDesc('updated_at')
            ->paginate($limit);

        // Published blog posts that match, newest first, in a list of their own
        // so the page results keep their shape.
        $posts = Post::published()
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('excerpt', 'like', "%{$query}%")
                    ->orWhere('body', 'like', "%{$query}%");
            })
            ->with('tags')
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get();

        return response()->json([
            'data' => PageResource::collection($pages),
            'posts' => $posts->map(fn (Post $post) => [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'path' => $post->path(),
                'summary' => $post->summary(),
                'cover_image' => $post->cover_image,
                'tags' => $post->tagNames(),
                'published_at' => $post->published_at?->toIso8601String(),
            ])->values(),
            'meta' => [
                'query' => $query,
                'total' => $pages->total(),
                'current_page' => $pages->currentPage(),
                'last_page' => $pages->lastPage(),
            ],
        ]);
    }
}
