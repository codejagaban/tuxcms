<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantResolved
{
    /**
     * Ensure a tenant has been resolved before allowing access.
     * Used on public routes that require tenant context.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!app('current_tenant_id')) {
            return response()->json([
                'message' => 'Tenant could not be identified. Provide an X-Tenant-Key header or use a tenant domain.',
            ], 400);
        }

        return $next($request);
    }
}
