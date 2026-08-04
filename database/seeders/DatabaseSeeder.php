<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database. Roles/Permissions and Plans are
     * platform-wide (no tenant_id); everything else belongs to one demo
     * Tenant, seeded the same way a real restaurant's registration flow
     * provisions a brand-new tenant's defaults (see
     * RegisteredUserController::store()).
     */
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);
        $this->call(PlanSeeder::class);

        $tenant = Tenant::firstOrCreate(
            ['slug' => 'demo-restaurant'],
            ['name' => 'Demo Restaurant', 'status' => 'active']
        );

        $trialPlan = Plan::where('slug', 'free-trial')->first();

        Subscription::firstOrCreate(
            ['tenant_id' => $tenant->id],
            [
                'plan_id' => $trialPlan->id,
                'status' => 'trialing',
                'trial_ends_at' => now()->addDays($trialPlan->trial_days),
            ]
        );

        $this->call(DepartmentSeeder::class, false, ['tenantId' => $tenant->id]);
        $this->call(DesignationSeeder::class, false, ['tenantId' => $tenant->id]);
        $this->call(LeaveTypeSeeder::class, false, ['tenantId' => $tenant->id]);
        $this->call(LedgerAccountSeeder::class, false, ['tenantId' => $tenant->id]);
        $this->call(UnitSeeder::class, false, ['tenantId' => $tenant->id]);
        $this->call(CategorySeeder::class, false, ['tenantId' => $tenant->id]);
        $this->call(BrandSeeder::class, false, ['tenantId' => $tenant->id]);
        $this->call(AttributeSeeder::class, false, ['tenantId' => $tenant->id]);
        $this->call(ProductSeeder::class, false, ['tenantId' => $tenant->id]);
        $this->call(PartySeeder::class, false, ['tenantId' => $tenant->id]);
        $this->call(HeroImageSeeder::class, false, ['tenantId' => $tenant->id]);
        $this->call(CompanyProfileSeeder::class, false, ['tenantId' => $tenant->id]);

        $admin = User::firstOrCreate(
            ['email' => 'admin@businesserp.test'],
            ['tenant_id' => $tenant->id, 'name' => 'Super Admin', 'password' => 'password']
        );
        $admin->syncRoles('Super Admin');

        User::firstOrCreate(
            ['email' => 'owner@businesserp.test'],
            ['tenant_id' => $tenant->id, 'name' => 'Company Admin', 'password' => 'password']
        )->syncRoles('Admin');

        User::firstOrCreate(
            ['email' => 'manager@businesserp.test'],
            ['tenant_id' => $tenant->id, 'name' => 'Branch Manager', 'password' => 'password']
        )->syncRoles('Manager');

        User::firstOrCreate(
            ['email' => 'hr@businesserp.test'],
            ['tenant_id' => $tenant->id, 'name' => 'HR Executive', 'password' => 'password']
        )->syncRoles('HR');

        User::firstOrCreate(
            ['email' => 'accountant@businesserp.test'],
            ['tenant_id' => $tenant->id, 'name' => 'Head Accountant', 'password' => 'password']
        )->syncRoles('Accountant');

        $tenant->update(['owner_user_id' => $admin->id]);

        $this->call(BranchSeeder::class, false, ['tenantId' => $tenant->id]);
    }
}
