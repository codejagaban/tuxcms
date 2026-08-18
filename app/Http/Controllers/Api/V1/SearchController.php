<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PageResource;
use App\Models\Page;
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

        return response()->json([
            'data' => PageResource::collection($pages),
            'meta' => [
                'query' => $query,
                'total' => $pages->total(),
                'current_page' => $pages->currentPage(),
                'last_page' => $pages->lastPage(),
            ],
        ]);
    }
}
