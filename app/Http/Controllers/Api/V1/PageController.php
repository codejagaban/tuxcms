<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePageRequest;
use App\Http\Requests\UpdatePageRequest;
use App\Http\Resources\PageResource;
use App\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class PageController extends Controller
{
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
            ->defaultSort('order');

        // Search by title or slug
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        // Public requests only see published pages
        if (!$request->user()) {
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
            if (!$request->user()) {
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
        if (!$request->user() && !$page->isPublished()) {
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

        $page->load(['sections' => function ($q) {
            $q->visible()->orderBy('order');
        }, 'seo', 'author', 'children', 'media']);

        if (!$request->user() && !$page->isPublished()) {
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

        if (!$request->user()) {
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
        $page->update($request->validated());

        // Sync sections if provided
        if ($request->has('sections')) {
            $page->sections()->delete();

            foreach ($request->input('sections') as $index => $sectionData) {
                $page->sections()->create([
                    ...$sectionData,
                    'order' => $sectionData['order'] ?? $index,
                ]);
            }
        }

        // Sync SEO if provided
        if ($request->has('seo')) {
            $page->seo()->updateOrCreate([], $request->input('seo'));
        }

        $page->load(['sections', 'seo', 'author']);

        return response()->json([
            'data' => new PageResource($page),
            'message' => 'Page updated successfully.',
        ]);
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
