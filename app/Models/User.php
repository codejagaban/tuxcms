<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_super_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_super_admin' => 'boolean',
        ];
    }

    // ── Relationships ──────────────────────────────────────

    public function tenants()
    {
        return $this->belongsToMany(Tenant::class, 'tenant_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function ownedTenants()
    {
        return $this->hasMany(Tenant::class, 'owner_id');
    }

    // ── Helpers ────────────────────────────────────────────

    public function isSuperAdmin(): bool
    {
        return $this->is_super_admin === true;
    }

    public function belongsToTenant(int $tenantId): bool
    {
        return $this->tenants()->where('tenants.id', $tenantId)->exists();
    }

    public function getRoleForTenant(int $tenantId): ?string
    {
        $tenant = $this->tenants()->where('tenants.id', $tenantId)->first();
        return $tenant?->pivot?->role;
    }

    public function isOwnerOfTenant(int $tenantId): bool
    {
        return $this->getRoleForTenant($tenantId) === 'owner';
    }
}
