<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Site\SiteBuilder;
use App\Support\PublishState;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

/**
 * Drives the Publish button in the dashboard.
 *
 * The build runs synchronously: a small business site renders in well under a
 * second, and shared hosting rarely has a reliable queue worker, so waiting is
 * both simpler and more honest than reporting "queued" and hoping.
 */
class SiteBuildController extends Controller
{
    public function store(SiteBuilder $builder): JsonResponse
    {
        $lock = Cache::lock('site:build', 600);

        if (! $lock->get()) {
            return response()->json([
                'message' => 'A build is already running.',
            ], 409);
        }

        $buildStarted = now();

        try {
            $result = $builder->build();
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Build failed: '.$e->getMessage(),
            ], 500);
        } finally {
            $lock->release();
        }

        PublishState::markPublished($buildStarted);

        return response()->json([
            'data' => [
                'pages' => $result['pages'],
                'files' => $result['files'],
                'pruned' => $result['pruned'],
                'last_published_at' => $buildStarted->toAtomString(),
            ],
            'message' => 'Site published.',
        ]);
    }

    /** Whether anything has changed since the last publish. */
    public function status(SiteBuilder $builder): JsonResponse
    {
        $lastPublishedAt = PublishState::lastPublishedAt();

        $hasOutput = File::exists($builder->outputPath().'/index.html');

        return response()->json([
            'data' => [
                'last_published_at' => $lastPublishedAt?->toAtomString(),
                'pending_changes' => PublishState::pendingCount(),
                'has_output' => $hasOutput,
            ],
        ]);
    }
}
