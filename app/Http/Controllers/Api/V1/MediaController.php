<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\MediaResource;
use App\Models\MediaLibrary;
use App\Models\Page;
use App\Models\PageSection;
use App\Support\PublishState;
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

        $perPage = min(max((int) $request->input('per_page', 15), 1), 100);
        $media = $query->paginate($perPage);

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
        PublishState::markChanged();

        return response()->json([
            'message' => 'Media deleted successfully',
        ], 204);
    }

    /**
     * Upload a file.
     *
     * With `model_type`/`model_id` the file attaches to that page or section.
     * Without them it goes to the site media library, which is how the Media
     * screen uploads reusable assets.
     */
    public function uploadToModel(Request $request): JsonResponse
    {
        // Only these models may receive uploads. Never build a class name from
        // raw input — that would make every App\Models\* class reachable.
        $allowed = [
            'Page' => Page::class,
            'PageSection' => PageSection::class,
        ];

        $request->validate([
            'file' => [
                'required',
                'file',
                'max:'.config('tuxcms.media.max_file_size', 10240),
                'mimes:'.implode(',', config('tuxcms.media.allowed_extensions', ['jpg', 'jpeg', 'png', 'gif'])),
            ],
            'collection' => 'nullable|string',
            'model_type' => 'nullable|string|in:'.implode(',', array_keys($allowed)),
            // Required only when attaching to a specific model.
            'model_id' => 'required_with:model_type|integer',
            'alt_text' => 'nullable|string',
            'caption' => 'nullable|string',
        ]);

        if ($request->filled('model_type')) {
            $model = $allowed[$request->model_type]::findOrFail($request->model_id);
            $collection = $request->collection ?? 'default';
        } else {
            $model = MediaLibrary::singleton();
            $collection = $request->collection ?? 'library';
        }

        $media = $model->addMediaFromRequest('file')
            ->withCustomProperties([
                'alt_text' => $request->alt_text,
                'caption' => $request->caption,
            ])
            ->toMediaCollection($collection);

        PublishState::markChanged();

        return response()->json([
            'data' => new MediaResource($media),
            'message' => 'File uploaded successfully',
        ], 201);
    }
}
