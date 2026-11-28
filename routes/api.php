<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\EnquiryController;
use App\Http\Controllers\Api\V1\JobPostController;
use App\Http\Controllers\Api\V1\PostController;
use App\Http\Controllers\Api\V1\JobApplicationController;
use App\Http\Controllers\Api\V1\MediaController;
use App\Http\Controllers\Api\V1\PageController;
use App\Http\Controllers\Api\V1\SearchController;
use App\Http\Controllers\Api\V1\SectionTypeController;
use App\Http\Controllers\Api\V1\SeoController;
use App\Http\Controllers\Api\V1\SettingController;
use App\Http\Controllers\Api\V1\SiteBuildController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // ── Health ─────────────────────────────────────────────
    Route::get('health', fn () => response()->json(['status' => 'ok', 'version' => '1.0.0']));

    // ── Auth ───────────────────────────────────────────────
    // No public registration — accounts are made with `artisan user:create`.
    Route::post('auth/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1');

    // ════════════════════════════════════════════════════════
    // Public reads
    // ════════════════════════════════════════════════════════
    Route::get('pages', [PageController::class, 'index']);
    Route::get('pages/tree', [PageController::class, 'tree']);
    Route::get('pages/homepage', [PageController::class, 'homepage']);
    Route::get('pages/resolve', [PageController::class, 'resolveByPath']);
    Route::get('pages/{slugOrId}', [PageController::class, 'show']);

    Route::get('search', [SearchController::class, 'search']);
    // Signed proof that a form page was opened; see App\Support\SpamFilter.
    Route::get('form-token', fn () => response()->json(['token' => \App\Support\SpamFilter::issueToken()])
        ->header('Cache-Control', 'no-store'))
        ->middleware('throttle:30,1');
    Route::post('job-applications', [JobApplicationController::class, 'store'])
        ->middleware('throttle:5,1');
    Route::post('enquiries', [EnquiryController::class, 'store'])
        ->middleware('throttle:5,1');

    // ════════════════════════════════════════════════════════
    // Authenticated management
    // ════════════════════════════════════════════════════════
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('auth/me', [AuthController::class, 'me']);

        // Pages
        Route::post('pages', [PageController::class, 'store']);
        Route::get('pages/{page}/revisions', [PageController::class, 'revisions']);
        Route::get('pages/{page}/preview', [PageController::class, 'preview']);
        Route::put('pages/{page}', [PageController::class, 'update']);
        Route::delete('pages/{page}', [PageController::class, 'destroy']);

        // The section types a page may hold, and their fields, for its template.
        Route::get('section-types', [SectionTypeController::class, 'index']);

        // Jobs
        Route::get('jobs', [JobPostController::class, 'index']);
        Route::get('jobs/{job}', [JobPostController::class, 'show']);
        Route::post('jobs', [JobPostController::class, 'store']);
        Route::put('jobs/{job}', [JobPostController::class, 'update']);
        Route::delete('jobs/{job}', [JobPostController::class, 'destroy']);
        Route::get('job-applications', [JobApplicationController::class, 'index']);
        Route::get('job-applications/{application}/cv', [JobApplicationController::class, 'download']);
        Route::get('enquiries', [EnquiryController::class, 'index']);
        Route::get('enquiry-kinds', [EnquiryController::class, 'kinds']);
        // Held by the spam filter by mistake: send it on.
        Route::post('enquiries/{enquiry}/release', [EnquiryController::class, 'release']);
        Route::post('job-applications/{application}/release', [JobApplicationController::class, 'release']);

        // Blog
        Route::get('posts', [PostController::class, 'index']);
        Route::get('post-tags', [PostController::class, 'tags']);
        Route::put('post-tags/{slug}', [PostController::class, 'renameTag']);
        Route::delete('post-tags/{slug}', [PostController::class, 'deleteTag']);
        // The post as the site would show it, from the editor's unsaved form.
        Route::post('posts/preview', [PostController::class, 'preview']);
        Route::get('posts/{post}', [PostController::class, 'show']);
        Route::get('posts/{post}/revisions', [PostController::class, 'revisions']);
        Route::post('posts', [PostController::class, 'store']);
        Route::put('posts/{post}', [PostController::class, 'update']);
        Route::delete('posts/{post}', [PostController::class, 'destroy']);

        // Per-page SEO
        Route::get('pages/{page}/seo', [SeoController::class, 'show']);
        Route::put('pages/{page}/seo', [SeoController::class, 'update']);

        // Settings
        Route::get('settings', [SettingController::class, 'index']);
        Route::get('settings/grouped', [SettingController::class, 'getGrouped']);
        Route::get('theme-settings', [SettingController::class, 'themeSettings']);
        Route::get('settings/{key}', [SettingController::class, 'show']);
        Route::put('settings', [SettingController::class, 'updateMany']);
        Route::post('settings', [SettingController::class, 'store']);
        Route::put('settings/{key}', [SettingController::class, 'update']);
        Route::delete('settings/{key}', [SettingController::class, 'destroy']);

        // Media
        Route::get('media', [MediaController::class, 'index']);
        Route::get('media/{media}', [MediaController::class, 'show']);
        Route::post('media', [MediaController::class, 'uploadToModel']);
        Route::delete('media/{media}', [MediaController::class, 'destroy']);

        // Publishing — regenerates the static site
        Route::get('site/status', [SiteBuildController::class, 'status']);
        Route::post('site/publish', [SiteBuildController::class, 'store'])
            ->middleware('throttle:10,1');
    });
});
