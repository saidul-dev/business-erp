<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->orderBy('id')->get();

        // Group permissions by module for the matrix: "sales.view" => module "sales", action "view"
        $permissions = Permission::orderBy('name')->get()
            ->groupBy(fn ($permission) => explode('.', $permission->name)[0]);

        return view('admin.roles.index', compact('roles', 'permissions'));
    }
}
