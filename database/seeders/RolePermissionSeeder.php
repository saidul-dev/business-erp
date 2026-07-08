<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * ERP modules (README §3) and the actions available on each.
     * Permission names follow the "module.action" convention.
     */
    protected array $modules = [
        'sourcing'  => ['view', 'create', 'edit', 'delete', 'approve'],
        'sales'     => ['view', 'create', 'edit', 'delete', 'approve'],
        'inventory' => ['view', 'create', 'edit', 'delete', 'approve'],
        'contacts'  => ['view', 'create', 'edit', 'delete'],
        'accounts'  => ['view', 'create', 'edit', 'delete', 'approve'],
        'delivery'  => ['view', 'create', 'edit', 'delete'],
        'tasks'     => ['view', 'create', 'edit', 'delete'],
        'hrm'       => ['view', 'create', 'edit', 'delete', 'approve'],
        'reports'   => ['view'],
        'settings'  => ['view', 'edit'],
        'users'     => ['view', 'create', 'edit', 'delete'],
        'roles'     => ['view', 'create', 'edit', 'delete'],
        'sites'     => ['view', 'create', 'edit', 'delete'],
    ];

    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach ($this->modules as $module => $actions) {
            foreach ($actions as $action) {
                Permission::findOrCreate("$module.$action");
            }
        }

        // Super Admin gets everything through Gate::before (see AppServiceProvider)
        Role::findOrCreate('Super Admin');

        Role::findOrCreate('Owner')
            ->syncPermissions(Permission::all());

        Role::findOrCreate('Manager')->syncPermissions([
            'sourcing.view', 'sourcing.create', 'sourcing.edit', 'sourcing.approve',
            'sales.view', 'sales.create', 'sales.edit', 'sales.approve',
            'inventory.view', 'inventory.create', 'inventory.edit', 'inventory.approve',
            'contacts.view', 'contacts.create', 'contacts.edit',
            'delivery.view', 'delivery.create', 'delivery.edit',
            'tasks.view', 'tasks.create', 'tasks.edit',
            'reports.view',
        ]);

        Role::findOrCreate('Accountant')->syncPermissions([
            'accounts.view', 'accounts.create', 'accounts.edit', 'accounts.approve',
            'sourcing.view',
            'sales.view',
            'contacts.view',
            'reports.view',
        ]);

        Role::findOrCreate('Store-keeper')->syncPermissions([
            'inventory.view', 'inventory.create', 'inventory.edit',
            'sourcing.view',
            'delivery.view', 'delivery.create', 'delivery.edit',
        ]);

        Role::findOrCreate('Sales')->syncPermissions([
            'sales.view', 'sales.create', 'sales.edit',
            'contacts.view', 'contacts.create', 'contacts.edit',
            'inventory.view',
            'delivery.view',
            'reports.view',
        ]);

        Role::findOrCreate('HR')->syncPermissions([
            'hrm.view', 'hrm.create', 'hrm.edit', 'hrm.approve',
            'tasks.view', 'tasks.create', 'tasks.edit',
            'contacts.view',
            'reports.view',
        ]);

        // Starter template for a per-site manager — everything needed to run
        // one site day-to-day, minus company-wide admin (users/roles/settings/sites).
        // Admin can clone this into narrower roles (e.g. "Factory Site Manager"
        // with only production/inventory) per Site type from Roles & Permissions.
        Role::findOrCreate('Site Manager')->syncPermissions([
            'sourcing.view', 'sourcing.create', 'sourcing.edit', 'sourcing.approve',
            'sales.view', 'sales.create', 'sales.edit', 'sales.approve',
            'inventory.view', 'inventory.create', 'inventory.edit', 'inventory.approve',
            'contacts.view', 'contacts.create', 'contacts.edit',
            'accounts.view', 'accounts.create', 'accounts.edit',
            'delivery.view', 'delivery.create', 'delivery.edit',
            'tasks.view', 'tasks.create', 'tasks.edit',
            'hrm.view', 'hrm.create', 'hrm.edit',
            'reports.view',
        ]);
    }
}
