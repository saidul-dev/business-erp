<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Nobody can hand out more access than they themselves hold. Super Admin
 * is unrestricted (its power comes from Gate::before, not the permission
 * tables); everyone else — including Admin — can only grant permissions
 * and roles that are already within their own effective permission set.
 *
 * Used by both UserController (assigning roles/direct permissions to a
 * user) and RoleController (defining what a role contains) so the second
 * one can't be used to route around the first.
 */
trait RestrictsPermissionGrants
{
    protected function grantablePermissionNames(): Collection
    {
        $actingUser = auth()->user();

        if ($actingUser->hasRole('Super Admin')) {
            return Permission::pluck('name');
        }

        return $actingUser->getAllPermissions()->pluck('name');
    }

    protected function grantableRoles(): Collection
    {
        $actingUser = auth()->user();

        if ($actingUser->hasRole('Super Admin')) {
            return Role::with('permissions')->get();
        }

        $grantable = $this->grantablePermissionNames();

        return Role::with('permissions')->get()
            ->reject(fn ($role) => $role->name === 'Super Admin')
            ->filter(fn ($role) => $role->permissions->pluck('name')->diff($grantable)->isEmpty())
            ->values();
    }

    /**
     * Reconciles a restricted user's submission with whatever the target
     * already had outside their own grantable scope, so editing a user or
     * role never silently strips access the editor can't even see.
     */
    protected function mergeRestrictedSelection(Collection $current, array $submitted, Collection $grantable): array
    {
        if (auth()->user()->hasRole('Super Admin')) {
            return $submitted;
        }

        $outsideScope = $current->diff($grantable);
        $withinScope = collect($submitted)->intersect($grantable);

        return $outsideScope->merge($withinScope)->unique()->values()->all();
    }
}
