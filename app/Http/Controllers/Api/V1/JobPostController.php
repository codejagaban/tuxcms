<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\JobPostRequest;
use App\Http\Resources\JobPostResource;
use App\Models\JobPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobPostController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = JobPost::query()->latest('updated_at');
        if ($search = $request->string('search')->trim()->value()) {
            $query->where(fn ($q) => $q->where('title', 'like', "%{$search}%")->orWhere('location', 'like', "%{$search}%"));
        }
        $jobs = $query->paginate(min(max($request->integer('per_page', 10), 1), 50));

        return response()->json(['data' => JobPostResource::collection($jobs), 'meta' => [
            'current_page' => $jobs->currentPage(), 'last_page' => $jobs->lastPage(),
            'per_page' => $jobs->perPage(), 'total' => $jobs->total(),
        ]]);
    }

    public function show(JobPost $job): JsonResponse
    {
        return response()->json(['data' => new JobPostResource($job)]);
    }

    public function store(JobPostRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['author_id'] = $request->user()->id;
        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }
        $job = JobPost::create($data);

        return response()->json(['data' => new JobPostResource($job)], 201);
    }

    public function update(JobPostRequest $request, JobPost $job): JsonResponse
    {
        $data = $request->validated();
        if ($data['status'] === 'published' && ! $job->published_at && empty($data['published_at'])) {
            $data['published_at'] = now();
        }
        $job->update($data);

        return response()->json(['data' => new JobPostResource($job->fresh())]);
    }

    public function destroy(JobPost $job): JsonResponse
    {
        $job->delete();

        return response()->json(null, 204);
    }
}
