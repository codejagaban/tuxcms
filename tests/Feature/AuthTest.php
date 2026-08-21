<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('');
    }

    public function test_a_user_can_log_in(): void
    {
        User::factory()->create(['email' => 'editor@example.test', 'password' => Hash::make('secret-password')]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'editor@example.test',
            'password' => 'secret-password',
        ]);

        $response->assertOk()->assertJsonStructure(['data', 'token', 'message']);
    }

    public function test_login_response_carries_no_tenant_keys(): void
    {
        User::factory()->create(['email' => 'editor@example.test', 'password' => Hash::make('secret-password')]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'editor@example.test',
            'password' => 'secret-password',
        ]);

        $response->assertJsonMissingPath('tenants')->assertJsonMissingPath('is_super_admin');
    }

    public function test_bad_credentials_are_rejected_without_leaking_detail(): void
    {
        User::factory()->create(['email' => 'editor@example.test', 'password' => Hash::make('secret-password')]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'editor@example.test',
            'password' => 'wrong-password',
        ]);

        $response->assertUnauthorized()->assertJson(['message' => 'Invalid credentials']);
    }

    /** Public registration was removed — accounts come from `artisan user:create`. */
    public function test_there_is_no_registration_endpoint(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Intruder',
            'email' => 'intruder@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertNotFound();
        $this->assertDatabaseMissing('users', ['email' => 'intruder@example.test']);
    }

    public function test_login_is_rate_limited(): void
    {
        User::factory()->create(['email' => 'editor@example.test', 'password' => Hash::make('secret-password')]);

        foreach (range(1, 5) as $attempt) {
            $this->postJson('/api/v1/auth/login', ['email' => 'editor@example.test', 'password' => 'wrong-password']);
        }

        $this->postJson('/api/v1/auth/login', ['email' => 'editor@example.test', 'password' => 'wrong-password'])
            ->assertStatus(429);
    }

    public function test_me_requires_authentication(): void
    {
        $this->getJson('/api/v1/auth/me')->assertUnauthorized();

        Sanctum::actingAs($this->editor());
        $this->getJson('/api/v1/auth/me')->assertOk();
    }

    public function test_is_super_admin_cannot_be_mass_assigned(): void
    {
        $user = User::create([
            'name' => 'Editor',
            'email' => 'editor@example.test',
            'password' => Hash::make('secret-password'),
            'is_super_admin' => true,
        ]);

        $this->assertArrayNotHasKey('is_super_admin', $user->getAttributes());
    }
}
