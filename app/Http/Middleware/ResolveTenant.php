<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    /**
     * Resolve the current tenant from one of four strategies:
     *
     * 1. X-Tenant-Key header (API key — recommended for headless frontends)
     * 2. Domain/subdomain matching
     * 3. X-Tenant-Id header with Sanctum auth (for dashboard)
     * 4. Authenticated user's default tenant
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = null;

        // Strategy 1: API Key header
        if ($apiKey = $request->header('X-Tenant-Key')) {
            $tenant = Tenant::where('api_key', $apiKey)->active()->first();

            if (!$tenant) {
                return response()->json([
                    'message' => 'Invalid or inactive tenant API key.',
                ], 401);
            }
        }

        // Strategy 2: Domain/subdomain matching
        if (!$tenant) {
            $host = $request->getHost();
            $tenant = Tenant::where('domain', $host)->active()->first();

            // Try subdomain matching (e.g., acme.tuxcms.com)
            if (!$tenant) {
                $baseDomain = config('tuxcms.base_domain');

                if ($baseDomain && str_ends_with($host, '.' . $baseDomain)) {
                    $subdomain = str_replace('.' . $baseDomain, '', $host);
                    $tenant = Tenant::where('slug', $subdomain)->active()->first();
                }
            }
        }

        // Strategy 3 & 4: Authenticated user's tenant (use Sanctum guard explicitly)
        if (!$tenant) {
            $user = Auth::guard('sanctum')->user();

            if ($user) {
                $tenantId = $request->header('X-Tenant-Id');

                // Super admins can specify any tenant via header
                if ($user->is_super_admin && $tenantId) {
                    $tenant = Tenant::find($tenantId);
                }

                // Regular users — validate they belong to the tenant
                if (!$tenant && !$user->is_super_admin) {
                    if ($tenantId) {
                        $tenant = $user->tenants()->where('tenants.id', $tenantId)->first();
                    } else {
                        $tenant = $user->tenants()->first();
                    }

                    if (!$tenant) {
                        return response()->json([
                            'message' => 'You do not belong to any tenant.',
                        ], 403);
                    }
                }

                // Super admin without X-Tenant-Id — try their first tenant
                if (!$tenant && $user->is_super_admin) {
                    $tenant = $user->tenants()->first();
                }
            }
        }

        // Bind the tenant to the container
        if ($tenant) {
            app()->instance('current_tenant', $tenant);
            app()->instance('current_tenant_id', $tenant->id);
        } else {
            app()->instance('current_tenant', null);
            app()->instance('current_tenant_id', null);
        }

        return $next($request);
    }
}
