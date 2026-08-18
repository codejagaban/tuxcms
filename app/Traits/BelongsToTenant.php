<?php

namespace App\Traits;

use App\Models\Tenant;
use App\Scopes\TenantScope;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        // Auto-apply tenant scope to all queries
        static::addGlobalScope(new TenantScope());

        // Auto-set tenant_id when creating new records
        static::creating(function ($model) {
            $tenantId = app('current_tenant_id');

            if ($tenantId && !$model->tenant_id) {
                $model->tenant_id = $tenantId;
            }
        });
    }

    /**
     * Get the tenant that owns this model.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Scope query to a specific tenant (bypasses global scope for that query).
     */
    public function scopeForTenant($query, int $tenantId)
    {
        return $query->withoutGlobalScope(TenantScope::class)
            ->where($this->getTable() . '.tenant_id', $tenantId);
    }

    /**
     * Scope to query across all tenants (super admin use).
     */
    public function scopeWithoutTenantScope($query)
    {
        return $query->withoutGlobalScope(TenantScope::class);
    }
}
