<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Support\PublishState;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Spatie\Tags\Tag;

class PostController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Post::query()->with(['tags', 'author'])
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

        if ($tag = $request->string('tag')->trim()->value()) {
            $query->whereHas('tags', fn ($q) => $q->where('type', Post::TAG_TYPE)->where('slug->'.app()->getLocale(), $tag));
        }

        $posts = $query->paginate(min(max($request->integer('per_page', 10), 1), 50));

        return response()->json(['data' => PostResource::collection($posts), 'meta' => [
            'current_page' => $posts->currentPage(), 'last_page' => $posts->lastPage(),
            'per_page' => $posts->perPage(), 'total' => $posts->total(),
        ]]);
    }

    public function show(Post $post): JsonResponse
    {
        return response()->json(['data' => new PostResource($post->load(['tags', 'author']))]);
    }

    public function store(PostRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['author_id'] = $request->user()->id;
        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }
        $post = Post::create(Arr::except($data, 'tags'));
        $this->syncTags($post, $data);

        return response()->json(['data' => new PostResource($post->load(['tags', 'author']))], 201);
    }

    public function update(PostRequest $request, Post $post): JsonResponse
    {
        $data = $request->validated();
        if ($data['status'] === 'published' && ! $post->published_at && empty($data['published_at'])) {
            $data['published_at'] = now();
        }
        $post->update(Arr::except($data, 'tags'));
        $this->syncTags($post, $data);

        return response()->json(['data' => new PostResource($post->fresh(['tags', 'author']))]);
    }

    /** Every blog tag in use, with how many posts carry it, for the editor to offer. */
    public function tags(): JsonResponse
    {
        $counts = DB::table('taggables')
            ->join('posts', 'posts.id', '=', 'taggables.taggable_id')
            ->where('taggables.taggable_type', (new Post)->getMorphClass())
            ->whereNull('posts.deleted_at')
            ->groupBy('taggables.tag_id')
            ->pluck(DB::raw('count(*)'), 'taggables.tag_id');

        $tags = Tag::getWithType(Post::TAG_TYPE)
            ->filter(fn (Tag $tag) => isset($counts[$tag->id]))
            ->sortBy(fn (Tag $tag) => mb_strtolower($tag->name))
            ->map(fn (Tag $tag) => ['name' => $tag->name, 'slug' => $tag->slug, 'posts_count' => (int) $counts[$tag->id]])
            ->values();

        return response()->json(['data' => $tags]);
    }

    /** Tags are only changed when the request names them. */
    private function syncTags(Post $post, array $data): void
    {
        if (array_key_exists('tags', $data)) {
            $post->syncTagsWithType(array_values(array_filter(array_map('trim', $data['tags'] ?? []))), Post::TAG_TYPE);
            PublishState::markChanged();
        }
    }

    public function destroy(Post $post): JsonResponse
    {
        $post->delete();

        return response()->json(null, 204);
    }
}
