<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Restricts a query to the authenticated user's own Tenant — the outer
 * boundary around everything a Branch-scoped query (see BranchScope)
 * already narrows further. A user with no tenant_id (platform staff, not
 * tied to any one restaurant) bypasses the filter entirely, same as
 * Admin/Super Admin viewing "All Branches" in BranchScope.
 */
class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (! auth()->check()) {
            return;
        }

        $tenantId = auth()->user()->tenant_id;

        if ($tenantId === null) {
            return;
        }

        $builder->where($model->getTable().'.tenant_id', $tenantId);
    }
}
