<?php

namespace App\Models\Concerns;

use App\Models\Scopes\TenantScope;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * For every tenant-owned model — the "master data" tables with no branch_id
 * to inherit isolation through (Category, Brand, Unit, Attribute, Product,
 * Party, Department, Designation, LeaveType, LedgerAccount,
 * LedgerTransaction) plus Branch/User/CompanySetting themselves. Add a
 * `tenant_id` foreign key to the model's migration, `use BelongsToTenant`,
 * and:
 *   - reads are auto-filtered to the current user's own Tenant
 *   - `tenant_id` is auto-filled from the current user's Tenant on create
 * No manual `where('tenant_id', ...)` needed anywhere else in the app.
 */
trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function ($model) {
            if (! $model->tenant_id && auth()->check() && auth()->user()->tenant_id) {
                $model->tenant_id = auth()->user()->tenant_id;
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
