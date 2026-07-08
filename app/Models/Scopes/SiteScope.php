<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Restricts a query to the authenticated user's Current Site.
 *
 * Owner/Super Admin viewing "All Sites" (current_site_id is null) bypass
 * the filter entirely — everyone else only ever sees their Current Site's
 * rows, per the Site-based Access Control architecture.
 */
class SiteScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (! auth()->check()) {
            return;
        }

        $user = auth()->user();

        if ($user->seesAllSites() && $user->current_site_id === null) {
            return;
        }

        $builder->where($model->getTable().'.site_id', $user->current_site_id);
    }
}
