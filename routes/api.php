<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\FormController;
use App\Http\Controllers\Api\V1\MediaController;
use App\Http\Controllers\Api\V1\MenuController;
use App\Http\Controllers\Api\V1\PageController;
use App\Http\Controllers\Api\V1\SearchController;
use App\Http\Controllers\Api\V1\SeoController;
use App\Http\Controllers\Api\V1\SettingController;
use App\Http\Controllers\Api\V1\SitemapController;
use App\Http\Controllers\Api\V1\TenantController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // ── Health Check (no tenant needed) ────────────────────
    Route::get('health', fn() => response()->json(['status' => 'ok', 'version' => '1.0.0']));

    // ── Auth ───────────────────────────────────────────────
    // No public registration — accounts are made with `artisan user:create`.
    Route::post('auth/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1');

    // ════════════════════════════════════════════════════════
    // Public routes (tenant required via header or domain)
    // ════════════════════════════════════════════════════════
    Route::middleware(['tenant.required'])->group(function () {
        // Pages
        Route::get('pages', [PageController::class, 'index']);
        Route::get('pages/tree', [PageController::class, 'tree']);
        Route::get('pages/homepage', [PageController::class, 'homepage']);
        Route::get('pages/resolve', [PageController::class, 'resolveByPath']);
        Route::get('pages/{slugOrId}', [PageController::class, 'show']);

        // Menus
        Route::get('menus', [MenuController::class, 'index']);
        Route::get('menus/{slugOrId}', [MenuController::class, 'show']);

        // Forms (view schema + submit)
        Route::get('forms', [FormController::class, 'index']);
        Route::get('forms/{slugOrId}', [FormController::class, 'show']);
        Route::post('forms/{slugOrId}/submit', [FormController::class, 'submit']);

        // Settings (read)
        Route::get('settings', [SettingController::class, 'index']);
        Route::get('settings/grouped', [SettingController::class, 'getGrouped']);
        Route::get('settings/{key}', [SettingController::class, 'show']);

        // SEO
        Route::get('seo/meta-tags', [SeoController::class, 'getMetaTags']);

        // Search
        Route::get('search', [SearchController::class, 'search']);

        // Sitemap
        Route::get('sitemap.xml', [SitemapController::class, 'index']);

        // Media (read)
        Route::get('media', [MediaController::class, 'index']);
        Route::get('media/{media}', [MediaController::class, 'show']);
    });

    // ════════════════════════════════════════════════════════
    // Authenticated routes (tenant resolved from user)
    // ════════════════════════════════════════════════════════
    Route::middleware(['auth:sanctum'])->group(function () {
        // Auth
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('auth/me', [AuthController::class, 'me']);

        // My tenants (list tenants user belongs to)
        Route::get('tenants/mine', [TenantController::class, 'myTenants']);
        Route::get('tenants/current', [TenantController::class, 'current']);

        // ── Tenant-scoped management ───────────────────────
        Route::middleware(['tenant.required'])->group(function () {
            // Pages CRUD
            Route::post('pages', [PageController::class, 'store']);
            Route::put('pages/{page}', [PageController::class, 'update']);
            Route::delete('pages/{page}', [PageController::class, 'destroy']);

            // Menus CRUD
            Route::post('menus', [MenuController::class, 'store']);
            Route::put('menus/{menu}', [MenuController::class, 'update']);
            Route::delete('menus/{menu}', [MenuController::class, 'destroy']);

            // Forms CRUD
            Route::post('forms', [FormController::class, 'store']);
            Route::put('forms/{form}', [FormController::class, 'update']);
            Route::delete('forms/{form}', [FormController::class, 'destroy']);

            // Form Submissions
            Route::get('forms/{form}/submissions', [FormController::class, 'submissions']);
            Route::get('forms/{form}/submissions/{submission}', [FormController::class, 'showSubmission']);
            Route::delete('forms/{form}/submissions/{submission}', [FormController::class, 'destroySubmission']);

            // Settings (write)
            Route::post('settings', [SettingController::class, 'store']);
            Route::put('settings/{key}', [SettingController::class, 'update']);
            Route::delete('settings/{key}', [SettingController::class, 'destroy']);

            // SEO (write)
            Route::put('seo', [SeoController::class, 'updateSeo']);
            Route::get('seo', [SeoController::class, 'getSeo']);

            // Media (upload + delete)
            Route::post('media', [MediaController::class, 'uploadToModel']);
            Route::delete('media/{media}', [MediaController::class, 'destroy']);
        });

        // ── Super Admin routes ─────────────────────────────
        Route::prefix('admin')->middleware(['super.admin'])->group(function () {
            // Tenant management
            Route::get('tenants', [TenantController::class, 'index']);
            Route::post('tenants', [TenantController::class, 'store']);
            Route::get('tenants/{tenant}', [TenantController::class, 'show']);
            Route::put('tenants/{tenant}', [TenantController::class, 'update']);
            Route::delete('tenants/{tenant}', [TenantController::class, 'destroy']);
            Route::post('tenants/{tenant}/regenerate-key', [TenantController::class, 'regenerateKey']);
            Route::post('tenants/{tenant}/users', [TenantController::class, 'addUser']);
            Route::delete('tenants/{tenant}/users/{user}', [TenantController::class, 'removeUser']);
        });
    });
});
