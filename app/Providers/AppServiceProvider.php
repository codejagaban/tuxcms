<?php

namespace App\Providers;

use App\Models\Page;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Default tenant bindings (overridden by ResolveTenant middleware)
        $this->app->singleton('current_tenant', fn() => null);
        $this->app->singleton('current_tenant_id', fn() => null);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Pages bind by slug for pretty public URLs, but the management
        // endpoints (PUT/DELETE /pages/{page}) are called with a numeric id.
        // Resolve {page} by id when numeric, otherwise by slug. Runs after
        // ResolveTenant, so the tenant scope still applies.
        Route::bind('page', function ($value) {
            $query = is_numeric($value)
                ? Page::where('id', $value)
                : Page::where('slug', $value);

            return $query->firstOrFail();
        });
    }
}
