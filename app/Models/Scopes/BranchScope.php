<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Restricts a query to the authenticated user's Current Branch.
 *
 * Admin/Super Admin viewing "All Branches" (current_branch_id is null) bypass
 * the filter entirely — everyone else only ever sees their Current Branch's
 * rows, per the Branch-based Access Control architecture.
 */
class BranchScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (! auth()->check()) {
            return;
        }

        $user = auth()->user();

        if ($user->seesAllBranches() && $user->current_branch_id === null) {
            return;
        }

        $builder->where($model->getTable().'.branch_id', $user->current_branch_id);
    }
}
