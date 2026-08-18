<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    /**
     * List all tenants (super admin).
     */
    public function index(Request $request): JsonResponse
    {
        $tenants = Tenant::query()
            ->with('owner:id,name,email')
            ->withCount('users', 'pages', 'forms')
            ->when($request->input('search'), function ($q, $search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('domain', 'like', "%{$search}%");
            })
            ->when($request->input('plan'), fn($q, $plan) => $q->where('plan', $plan))
            ->when($request->has('active'), fn($q) => $q->where('is_active', $request->boolean('active')))
            ->orderByDesc('created_at')
            ->paginate($request->input('per_page', 20));

        return response()->json([
            'data' => $tenants->items(),
            'meta' => [
                'current_page' => $tenants->currentPage(),
                'last_page' => $tenants->lastPage(),
                'per_page' => $tenants->perPage(),
                'total' => $tenants->total(),
            ],
        ]);
    }

    /**
     * Show a single tenant (super admin).
     */
    public function show(Tenant $tenant): JsonResponse
    {
        $tenant->load(['owner:id,name,email', 'users:id,name,email']);
        $tenant->loadCount('pages', 'forms', 'menus');

        return response()->json([
            'data' => $tenant,
        ]);
    }

    /**
     * Create a new tenant (super admin).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'nullable|string|max:255|unique:tenants,domain',
            'owner_email' => 'required|email|exists:users,email',
            'plan' => 'nullable|string|in:free,pro,enterprise',
            'settings' => 'nullable|array',
        ]);

        $owner = User::where('email', $validated['owner_email'])->firstOrFail();

        $tenant = Tenant::create([
            'name' => $validated['name'],
            'domain' => $validated['domain'] ?? null,
            'owner_id' => $owner->id,
            'plan' => $validated['plan'] ?? 'free',
            'settings' => $validated['settings'] ?? null,
        ]);

        // Attach owner to tenant with 'owner' role
        $tenant->users()->attach($owner->id, ['role' => 'owner']);

        $tenant->load('owner:id,name,email');

        return response()->json([
            'data' => $tenant,
            'message' => 'Tenant created successfully.',
        ], 201);
    }

    /**
     * Update a tenant (super admin).
     */
    public function update(Request $request, Tenant $tenant): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'domain' => 'nullable|string|max:255|unique:tenants,domain,' . $tenant->id,
            'plan' => 'nullable|string|in:free,pro,enterprise',
            'settings' => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ]);

        $tenant->update($validated);

        return response()->json([
            'data' => $tenant->fresh(['owner:id,name,email']),
            'message' => 'Tenant updated successfully.',
        ]);
    }

    /**
     * Delete a tenant (super admin).
     */
    public function destroy(Tenant $tenant): JsonResponse
    {
        $tenant->delete();

        return response()->json([
            'message' => 'Tenant deleted successfully.',
        ]);
    }

    /**
     * Regenerate a tenant's API key (super admin).
     */
    public function regenerateKey(Tenant $tenant): JsonResponse
    {
        $newKey = $tenant->regenerateApiKey();

        return response()->json([
            'data' => ['api_key' => $newKey],
            'message' => 'API key regenerated.',
        ]);
    }

    /**
     * Add a user to a tenant.
     */
    public function addUser(Request $request, Tenant $tenant): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
            'role' => 'required|string|in:owner,editor',
        ]);

        $user = User::where('email', $validated['email'])->firstOrFail();

        if ($tenant->users()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'User already belongs to this tenant.'], 422);
        }

        $tenant->users()->attach($user->id, ['role' => $validated['role']]);

        return response()->json([
            'message' => 'User added to tenant.',
        ]);
    }

    /**
     * Remove a user from a tenant.
     */
    public function removeUser(Tenant $tenant, User $user): JsonResponse
    {
        if ($tenant->owner_id === $user->id) {
            return response()->json(['message' => 'Cannot remove the tenant owner.'], 422);
        }

        $tenant->users()->detach($user->id);

        return response()->json([
            'message' => 'User removed from tenant.',
        ]);
    }

    // ── Tenant owner endpoints ─────────────────────────────

    /**
     * Get current tenant info (for tenant owners/editors).
     */
    public function current(): JsonResponse
    {
        $tenant = app('current_tenant');

        if (!$tenant) {
            return response()->json(['message' => 'No tenant context.'], 400);
        }

        $tenant->loadCount('pages', 'forms', 'menus');

        return response()->json([
            'data' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
                'domain' => $tenant->domain,
                'plan' => $tenant->plan,
                'settings' => $tenant->settings,
                'pages_count' => $tenant->pages_count,
                'forms_count' => $tenant->forms_count,
                'menus_count' => $tenant->menus_count,
            ],
        ]);
    }

    /**
     * List tenants the current user belongs to.
     */
    public function myTenants(Request $request): JsonResponse
    {
        $tenants = $request->user()->tenants()
            ->withCount('pages', 'forms')
            ->get()
            ->map(fn($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'slug' => $t->slug,
                'domain' => $t->domain,
                'plan' => $t->plan,
                'role' => $t->pivot->role,
                'pages_count' => $t->pages_count,
                'forms_count' => $t->forms_count,
            ]);

        return response()->json([
            'data' => $tenants,
        ]);
    }
}
