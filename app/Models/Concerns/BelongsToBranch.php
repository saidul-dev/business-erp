<?php

namespace App\Models\Concerns;

use App\Models\Branch;
use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * For every branch-aware model (Purchase, Sale, Inventory, Payroll, Voucher...
 * — see the ERP Architecture Guideline v1.0). Add a `branch_id` foreign key
 * to the model's migration, `use BelongsToBranch`, and:
 *   - reads are auto-filtered to the current user's Current Branch
 *   - `branch_id` is auto-filled from the current user's Current Branch on create
 * No manual `where('branch_id', ...)` needed anywhere else in the app.
 */
trait BelongsToBranch
{
    protected static function bootBelongsToBranch(): void
    {
        static::addGlobalScope(new BranchScope);

        static::creating(function ($model) {
            if (! $model->branch_id && auth()->check()) {
                $model->branch_id = auth()->user()->current_branch_id;
            }
        });
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
