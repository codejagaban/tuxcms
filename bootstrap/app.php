<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'resolve.tenant' => \App\Http\Middleware\ResolveTenant::class,
            'tenant.required' => \App\Http\Middleware\EnsureTenantResolved::class,
            'super.admin' => \App\Http\Middleware\EnsureSuperAdmin::class,
            'json.response' => \App\Http\Middleware\EnsureJsonResponse::class,
        ]);

        // Apply tenant resolution to all API routes
        $middleware->api(prepend: [
            \App\Http\Middleware\EnsureJsonResponse::class,
            \App\Http\Middleware\ResolveTenant::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
