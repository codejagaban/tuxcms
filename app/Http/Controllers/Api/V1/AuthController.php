<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'data' => new UserResource($user),
            'token' => $token,
            'message' => 'User registered successfully',
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        try {
            $token = $user->createToken('auth_token')->plainTextToken;

            // Include the user's tenants in the login response
            $tenants = $user->tenants()->get()->map(fn($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'slug' => $t->slug,
                'domain' => $t->domain,
                'role' => $t->pivot->role,
            ]);

            return response()->json([
                'data' => new UserResource($user),
                'token' => $token,
                'tenants' => $tenants,
                'is_super_admin' => (bool) $user->is_super_admin,
                'message' => 'Login successful',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Login processing error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function logout(): JsonResponse
    {
        auth()->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    public function me(): JsonResponse
    {
        $user = auth()->user();
        $tenants = $user->tenants()->get()->map(fn($t) => [
            'id' => $t->id,
            'name' => $t->name,
            'slug' => $t->slug,
            'domain' => $t->domain,
            'role' => $t->pivot->role,
        ]);

        return response()->json([
            'data' => new UserResource($user),
            'tenants' => $tenants,
            'is_super_admin' => $user->is_super_admin,
            'current_tenant' => app('current_tenant') ? [
                'id' => app('current_tenant')->id,
                'name' => app('current_tenant')->name,
            ] : null,
        ]);
    }
}
