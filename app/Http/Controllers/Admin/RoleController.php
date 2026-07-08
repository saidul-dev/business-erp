<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller implements HasMiddleware
{
    /**
     * Display order for role lists — not creation order, just presentation.
     */
    protected const ROLE_ORDER = ['Super Admin', 'Admin', 'Manager', 'HR', 'Accountant', 'Store-keeper', 'Sales'];

    public static function middleware(): array
    {
        return [
            new Middleware('permission:roles.view', only: ['index']),
            new Middleware('permission:roles.create', only: ['create', 'store']),
            new Middleware('permission:roles.edit', only: ['edit', 'update']),
            new Middleware('permission:roles.delete', only: ['destroy']),
        ];
    }

    public function index()
    {
        $roles = Role::with('permissions')->withCount('users')->get()
            ->sortBy(function ($role) {
                $index = array_search($role->name, self::ROLE_ORDER);

                return $index === false ? 999 : $index;
            })
            ->values();

        $permissions = $this->permissionsByModule();

        return view('admin.roles.index', compact('roles', 'permissions'));
    }

    public function create()
    {
        return view('admin.roles.create', ['permissions' => $this->permissionsByModule()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:125', 'unique:roles,name'],
            'permissions' => ['array'],
            'permissions.*' => ['exists:permissions,name'],
        ]);

        $role = Role::create(['name' => $validated['name']]);
        $role->syncPermissions($validated['permissions'] ?? []);

        return redirect()->route('roles.index')->with('success', "Role \"{$role->name}\" created.");
    }

    public function edit(Role $role)
    {
        $this->guardSuperAdminRole($role);

        return view('admin.roles.edit', [
            'role' => $role,
            'permissions' => $this->permissionsByModule(),
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $this->guardSuperAdminRole($role);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:125', Rule::unique('roles', 'name')->ignore($role->id)],
            'permissions' => ['array'],
            'permissions.*' => ['exists:permissions,name'],
        ]);

        $role->update(['name' => $validated['name']]);
        $role->syncPermissions($validated['permissions'] ?? []);

        return redirect()->route('roles.index')->with('success', "Role \"{$role->name}\" updated.");
    }

    public function destroy(Role $role)
    {
        $this->guardSuperAdminRole($role);

        if ($role->users()->exists()) {
            return back()->with('error', "Role \"{$role->name}\" still has users assigned — reassign them first.");
        }

        $role->delete();

        return redirect()->route('roles.index')->with('success', "Role \"{$role->name}\" deleted.");
    }

    /**
     * "sales.view" => grouped under "sales". Matrix and forms render per module.
     */
    protected function permissionsByModule()
    {
        return Permission::orderBy('name')->get()
            ->groupBy(fn ($permission) => explode('.', $permission->name)[0]);
    }

    protected function guardSuperAdminRole(Role $role): void
    {
        abort_if(
            $role->name === 'Super Admin',
            403,
            'The Super Admin role is protected and cannot be modified.'
        );
    }
}
