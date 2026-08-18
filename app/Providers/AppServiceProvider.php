<?php

namespace App\Providers;

use App\Models\Page;
use App\Observers\PageObserver;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Page::observe(PageObserver::class);

        // Pages bind by slug for pretty public URLs, but the management
        // endpoints (PUT/DELETE /pages/{page}) are called with a numeric id.
        // Resolve {page} by id when numeric, otherwise by slug.
        Route::bind('page', function ($value) {
            $query = is_numeric($value)
                ? Page::where('id', $value)
                : Page::where('slug', $value);

            return $query->firstOrFail();
        });
    }
}
