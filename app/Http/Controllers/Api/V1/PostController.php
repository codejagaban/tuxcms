<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Services\Site\SiteBuilder;
use App\Support\PublishState;
use App\Support\RichText;
use Carbon\CarbonInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
        $data['published_at'] = $this->publishMoment($data['published_at'] ?? null, null, $data['status']);
        $post = Post::create(Arr::except($data, 'tags'));
        $this->syncTags($post, $data);

        return response()->json(['data' => new PostResource($post->load(['tags', 'author']))], 201);
    }

    public function update(PostRequest $request, Post $post): JsonResponse
    {
        $data = $request->validated();
        if (array_key_exists('published_at', $data) || $data['status'] === 'published') {
            $data['published_at'] = $this->publishMoment($data['published_at'] ?? null, $post->published_at, $data['status']);
        }
        $post->update(Arr::except($data, 'tags'));
        $this->syncTags($post, $data);

        return response()->json(['data' => new PostResource($post->fresh(['tags', 'author']))]);
    }

    /**
     * The post as the site would show it, drawn from what is in the editor
     * now, saved or not. The body is cleaned exactly as it is on save, since
     * the dashboard shows this HTML in a frame of its own.
     */
    public function preview(Request $request, SiteBuilder $builder): Response
    {
        $data = $request->validate([
            'id' => ['nullable', 'integer'],
            'title' => ['nullable', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'string', 'max:2048'],
            'cover_image_alt' => ['nullable', 'string', 'max:255'],
            'tags' => ['nullable', 'array', 'max:20'],
            'tags.*' => ['string', 'max:50'],
            'published_at' => ['nullable', 'date'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
        ]);

        $saved = isset($data['id']) ? Post::with('author')->find($data['id']) : null;

        $post = $saved ? $saved->replicate() : new Post;
        if ($saved) {
            $post->id = $saved->id;
        }
        $post->fill(Arr::only($data, ['excerpt', 'cover_image', 'cover_image_alt', 'meta_title', 'meta_description']));
        $post->title = filled($data['title'] ?? null) ? $data['title'] : 'Untitled post';
        $post->slug = filled($data['slug'] ?? null) ? $data['slug'] : 'preview';
        $post->body = app(RichText::class)->clean($data['body'] ?? null);
        $post->published_at = $this->publishMoment($data['published_at'] ?? null, $saved?->published_at, 'published');
        $post->updated_at = now();
        $post->setRelation('author', $saved?->author ?? $request->user());
        $post->setRelation('tags', collect($data['tags'] ?? [])->map(fn (string $name) => tap(new Tag, function (Tag $tag) use ($name) {
            $tag->type = Post::TAG_TYPE;
            $tag->setTranslation('name', app()->getLocale(), trim($name));
            $tag->setTranslation('slug', app()->getLocale(), Str::slug(trim($name)));
        }))->filter(fn (Tag $tag) => filled($tag->name))->values());

        $html = $builder->renderPost($post);

        return response($html)
            ->header('Content-Type', 'text/html; charset=UTF-8')
            ->header('X-Robots-Tag', 'noindex, nofollow')
            ->header('Cache-Control', 'no-store');
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

    /**
     * The moment a post goes live, from the date the editor chose.
     *
     * The editor chooses a day, not a time. Today means now, a later day
     * means the start of that day, and an earlier one is taken as the day
     * it was first published. A post keeps its exact moment while its day
     * is unchanged, so posts published the same day keep their order. A
     * full date and time, from another client, is used as it is. With no
     * date, a published post keeps the moment it has, or takes now, and a
     * draft has none.
     */
    private function publishMoment(?string $chosen, ?CarbonInterface $current, string $status): ?CarbonInterface
    {
        if (blank($chosen)) {
            return $status === 'published' ? ($current ?? now()) : null;
        }

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $chosen)) {
            return Carbon::parse($chosen);
        }

        $day = Carbon::parse($chosen)->startOfDay();

        return match (true) {
            $current?->isSameDay($day) === true => $current,
            $day->isToday() => now(),
            default => $day,
        };
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
