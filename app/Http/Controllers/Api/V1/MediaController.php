<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\MediaResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Media::query();

        if ($request->has('collection')) {
            $query->where('collection_name', $request->collection);
        }

        if ($request->has('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $media = $query->paginate(15);

        return response()->json([
            'data' => MediaResource::collection($media),
            'meta' => [
                'total' => $media->total(),
                'per_page' => $media->perPage(),
                'current_page' => $media->currentPage(),
                'last_page' => $media->lastPage(),
            ],
        ]);
    }

    public function show(Media $media): MediaResource
    {
        return new MediaResource($media);
    }

    public function destroy(Media $media): JsonResponse
    {
        $media->delete();

        return response()->json([
            'message' => 'Media deleted successfully',
        ], 204);
    }

    public function uploadToModel(Request $request): JsonResponse
    {
        // Only these models may receive uploads. Never build a class name from
        // raw input — that would make every App\Models\* class reachable.
        $allowed = [
            'Page' => \App\Models\Page::class,
            'PageSection' => \App\Models\PageSection::class,
        ];

        $request->validate([
            'file' => [
                'required',
                'file',
                'max:' . config('tuxcms.media.max_file_size', 10240),
                'mimes:' . implode(',', config('tuxcms.media.allowed_extensions', ['jpg', 'jpeg', 'png', 'gif'])),
            ],
            'collection' => 'nullable|string',
            'model_type' => 'required|string|in:' . implode(',', array_keys($allowed)),
            'model_id' => 'required|integer',
            'alt_text' => 'nullable|string',
            'caption' => 'nullable|string',
        ]);

        $model = $allowed[$request->model_type]::findOrFail($request->model_id);

        $collection = $request->collection ?? 'default';

        $media = $model->addMediaFromRequest('file')
            ->withCustomProperties([
                'alt_text' => $request->alt_text,
                'caption' => $request->caption,
            ])
            ->toMediaCollection($collection);

        return response()->json([
            'data' => new MediaResource($media),
            'message' => 'File uploaded successfully',
        ], 201);
    }
}
