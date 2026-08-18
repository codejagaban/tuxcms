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
        $this->authorize('delete', $media);

        $media->delete();

        return response()->json([
            'message' => 'Media deleted successfully',
        ], 204);
    }

    public function uploadToModel(Request $request): JsonResponse
    {
        $this->authorize('upload', Media::class);

        $request->validate([
            'file' => 'required|file|max:10240',
            'collection' => 'nullable|string',
            'model_type' => 'required|string',
            'model_id' => 'required|integer',
            'alt_text' => 'nullable|string',
            'caption' => 'nullable|string',
        ]);

        $modelClass = "App\\Models\\{$request->model_type}";
        if (!class_exists($modelClass)) {
            return response()->json(['error' => 'Invalid model type'], 400);
        }

        $model = $modelClass::findOrFail($request->model_id);

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
