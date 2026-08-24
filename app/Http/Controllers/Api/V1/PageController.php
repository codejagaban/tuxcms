<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePageRequest;
use App\Http\Requests\UpdatePageRequest;
use App\Http\Resources\PageResource;
use App\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class PageController extends Controller
{
    /**
     * Read routes are public so the site can be built and previewed without a
     * token, but an authenticated editor must still see drafts. These routes
     * carry no auth middleware, so $request->user() is always null — the
     * Sanctum guard has to be asked directly.
     */
    private function isEditor(Request $request): bool
    {
        return (bool) ($request->user() ?: Auth::guard('sanctum')->user());
    }

    /**
     * List all published pages (public) or all pages (authenticated).
     */
    public function index(Request $request): JsonResponse
    {
        $query = QueryBuilder::for(Page::class)
            ->allowedFilters([
                AllowedFilter::exact('status'),
                AllowedFilter::exact('template'),
                AllowedFilter::exact('parent_id'),
                AllowedFilter::exact('is_homepage'),
                'title',
            ])
            ->allowedSorts(['title', 'order', 'created_at', 'updated_at', 'published_at'])
            ->allowedIncludes(['sections', 'seo', 'author', 'children', 'parent', 'media'])
            // The listing shows an author column, so load it by default rather
            // than relying on the caller to request the include.
            ->with('author')
            ->defaultSort('order');

        // Search by title or slug
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        // Public requests only see published pages
        if (!$this->isEditor($request)) {
            $query->published();
        }

        $pages = $query->paginate($request->input('per_page', 15));

        return response()->json([
            'data' => PageResource::collection($pages),
            'meta' => [
                'current_page' => $pages->currentPage(),
                'last_page' => $pages->lastPage(),
                'per_page' => $pages->perPage(),
                'total' => $pages->total(),
            ],
        ]);
    }

    /**
     * Get a single page by slug or ID.
     */
    public function show(Request $request, string $slugOrId): JsonResponse
    {
        // Authenticated editors need every section (including hidden ones) so a
        // save doesn't silently drop them; public visitors only see visible.
        $query = Page::with(['sections' => function ($q) use ($request) {
            if (!$this->isEditor($request)) {
                $q->visible();
            }
            $q->orderBy('order');
        }, 'seo', 'author', 'children', 'media']);

        if (is_numeric($slugOrId)) {
            $page = $query->findOrFail($slugOrId);
        } else {
            $page = $query->where('slug', $slugOrId)->firstOrFail();
        }

        // Non-authenticated users can only view published pages
        if (!$this->isEditor($request) && !$page->isPublished()) {
            abort(404);
        }

        return response()->json([
            'data' => new PageResource($page),
        ]);
    }

    /**
     * Resolve a page by its full URL path (e.g., /services/web-development).
     */
    public function resolveByPath(Request $request): JsonResponse
    {
        $path = ltrim($request->input('path', ''), '/');
        $segments = explode('/', $path);

        $page = null;
        $parentId = null;

        foreach ($segments as $slug) {
            $query = Page::where('slug', $slug);

            if ($parentId === null) {
                $query->whereNull('parent_id');
            } else {
                $query->where('parent_id', $parentId);
            }

            $page = $query->first();

            if (!$page) {
                abort(404, 'Page not found for path: /' . $path);
            }

            $parentId = $page->id;
        }

        $page->load(['sections' => function ($q) use ($request) {
            if (!$this->isEditor($request)) {
                $q->visible();
            }
            $q->orderBy('order');
        }, 'seo', 'author', 'children', 'media']);

        if (!$this->isEditor($request) && !$page->isPublished()) {
            abort(404);
        }

        return response()->json([
            'data' => new PageResource($page),
        ]);
    }

    /**
     * Get the page tree structure (all pages nested).
     */
    public function tree(Request $request): JsonResponse
    {
        $query = Page::with('children.children')
            ->topLevel()
            ->orderBy('order');

        if (!$this->isEditor($request)) {
            $query->published();
        }

        $pages = $query->get();

        return response()->json([
            'data' => PageResource::collection($pages),
        ]);
    }

    /**
     * Get the homepage.
     */
    public function homepage(): JsonResponse
    {
        $page = Page::where('is_homepage', true)
            ->published()
            ->with(['sections' => function ($q) {
                $q->visible()->orderBy('order');
            }, 'seo', 'media'])
            ->firstOrFail();

        return response()->json([
            'data' => new PageResource($page),
        ]);
    }

    /**
     * Create a new page.
     */
    public function store(StorePageRequest $request): JsonResponse
    {
        $page = Page::create([
            ...$request->validated(),
            'author_id' => $request->user()->id,
        ]);

        // Handle sections if provided
        if ($request->has('sections')) {
            foreach ($request->input('sections') as $index => $sectionData) {
                $page->sections()->create([
                    ...$sectionData,
                    'order' => $sectionData['order'] ?? $index,
                ]);
            }
        }

        // Handle SEO if provided
        if ($request->has('seo')) {
            $page->seo()->create($request->input('seo'));
        }

        $page->load(['sections', 'seo', 'author']);

        return response()->json([
            'data' => new PageResource($page),
            'message' => 'Page created successfully.',
        ], 201);
    }

    /**
     * Update an existing page.
     */
    public function update(UpdatePageRequest $request, Page $page): JsonResponse
    {
        DB::transaction(function () use ($request, $page) {
            $page->update($request->validated());

            if ($request->has('sections')) {
                $this->syncSections($page, $request->input('sections'));
            }

            if ($request->has('seo')) {
                $page->seo()->updateOrCreate([], $request->input('seo'));
            }
        });

        $page->load(['sections', 'seo', 'author']);

        return response()->json([
            'data' => new PageResource($page),
            'message' => 'Page updated successfully.',
        ]);
    }

    /**
     * Reconcile a page's sections against the incoming list.
     *
     * Matches on section id so existing rows are updated in place. The previous
     * delete-and-recreate approach churned ids on every save and — because a
     * mass delete fires no model events — left every attached media file
     * orphaned in storage, pointing at a section that no longer existed.
     *
     * @param array<int, array<string, mixed>> $incoming
     */
    private function syncSections(Page $page, array $incoming): void
    {
        $existing = $page->sections()->get()->keyBy('id');
        $keptIds = [];

        foreach ($incoming as $index => $data) {
            $attributes = [
                'key' => $data['key'] ?? null,
                'type' => $data['type'] ?? 'text',
                'title' => $data['title'] ?? null,
                'content' => $data['content'] ?? null,
                'data' => $data['data'] ?? null,
                'order' => $data['order'] ?? $index,
                'is_visible' => $data['is_visible'] ?? true,
            ];

            // Only reuse an id that genuinely belongs to this page — a client
            // must not be able to hijack another page's section by id.
            $section = isset($data['id']) ? $existing->get($data['id']) : null;

            if ($section) {
                $section->update($attributes);
            } else {
                $section = $page->sections()->create($attributes);
            }

            $keptIds[] = $section->id;
        }

        // Delete as models, not via a mass delete, so media-library's deleting
        // hook runs and the section's files are removed with it.
        $existing->except($keptIds)->each->delete();
    }

    /**
     * Delete a page (soft delete).
     */
    public function destroy(Page $page): JsonResponse
    {
        $page->delete();

        return response()->json([
            'message' => 'Page deleted successfully.',
        ]);
    }
}
