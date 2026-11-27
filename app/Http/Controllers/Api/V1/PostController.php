<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Post::query()->with(['category', 'author'])
            // Newest first by the date a reader would see; drafts by last edit.
            ->orderByRaw('COALESCE(published_at, updated_at) DESC');

        if ($search = $request->string('search')->trim()->value()) {
            $query->where(fn ($q) => $q->where('title', 'like', "%{$search}%")->orWhere('excerpt', 'like', "%{$search}%"));
        }

        match ($request->string('status')->value()) {
            'draft' => $query->where('status', 'draft'),
            'published' => $query->published(),
            'scheduled' => $query->scheduled(),
            default => null,
        };

        if ($category = $request->integer('category')) {
            $query->where('post_category_id', $category);
        }

        $posts = $query->paginate(min(max($request->integer('per_page', 10), 1), 50));

        return response()->json(['data' => PostResource::collection($posts), 'meta' => [
            'current_page' => $posts->currentPage(), 'last_page' => $posts->lastPage(),
            'per_page' => $posts->perPage(), 'total' => $posts->total(),
        ]]);
    }

    public function show(Post $post): JsonResponse
    {
        return response()->json(['data' => new PostResource($post->load(['category', 'author']))]);
    }

    public function store(PostRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['author_id'] = $request->user()->id;
        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }
        $post = Post::create($data);

        return response()->json(['data' => new PostResource($post->load(['category', 'author']))], 201);
    }

    public function update(PostRequest $request, Post $post): JsonResponse
    {
        $data = $request->validated();
        if ($data['status'] === 'published' && ! $post->published_at && empty($data['published_at'])) {
            $data['published_at'] = now();
        }
        $post->update($data);

        return response()->json(['data' => new PostResource($post->fresh(['category', 'author']))]);
    }

    public function destroy(Post $post): JsonResponse
    {
        $post->delete();

        return response()->json(null, 204);
    }
}
