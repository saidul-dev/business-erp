<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\AttributeSeeder;
use Database\Seeders\BrandSeeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\CompanyProfileSeeder;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\DesignationSeeder;
use Database\Seeders\HeroImageSeeder;
use Database\Seeders\LeaveTypeSeeder;
use Database\Seeders\LedgerAccountSeeder;
use Database\Seeders\PartySeeder;
use Database\Seeders\UnitSeeder;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Register a brand-new restaurant: creates its Tenant, owner User
     * (Admin role), first Branch, a 30-day trial Subscription, and the
     * same "sensible defaults" (chart of accounts, units, departments...)
     * a demo tenant gets from DatabaseSeeder — everything scoped to this
     * one new tenant_id. No demo menu items though (see ProductSeeder) —
     * a real restaurant builds its own menu, it doesn't inherit ours.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'restaurant_name' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = DB::transaction(function () use ($request) {
            $tenant = Tenant::create([
                'name' => $request->restaurant_name,
                'slug' => $this->uniqueSlug($request->restaurant_name),
                'status' => 'active',
            ]);

            $trialPlan = Plan::where('slug', 'free-trial')->firstOrFail();

            Subscription::create([
                'tenant_id' => $tenant->id,
                'plan_id' => $trialPlan->id,
                'status' => 'trialing',
                'trial_ends_at' => now()->addDays($trialPlan->trial_days),
            ]);

            $branch = Branch::create([
                'tenant_id' => $tenant->id,
                'name' => $request->restaurant_name,
                'code' => 'MAIN',
                'type' => 'Outlet',
                'status' => true,
            ]);

            $user = User::create([
                'tenant_id' => $tenant->id,
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password,
                'current_branch_id' => $branch->id,
            ]);
            $user->assignRole('Admin');
            $user->branches()->attach($branch->id, ['is_default' => true]);

            $tenant->update(['owner_user_id' => $user->id]);

            $this->seedTenantDefaults($tenant->id);

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }

    /**
     * The same starting data a demo tenant gets — chart of accounts, units,
     * categories, departments/designations, leave types, walk-in/supplier
     * placeholders, and non-empty public-website copy. Deliberately skips
     * ProductSeeder (a real restaurant's menu is theirs to build) and
     * BranchSeeder (this tenant already got its one Branch above).
     */
    protected function seedTenantDefaults(int $tenantId): void
    {
        (new DepartmentSeeder)->run($tenantId);
        (new DesignationSeeder)->run($tenantId);
        (new LeaveTypeSeeder)->run($tenantId);
        (new LedgerAccountSeeder)->run($tenantId);
        (new UnitSeeder)->run($tenantId);
        (new CategorySeeder)->run($tenantId);
        (new BrandSeeder)->run($tenantId);
        (new AttributeSeeder)->run($tenantId);
        (new PartySeeder)->run($tenantId);
        (new HeroImageSeeder)->run($tenantId);
        (new CompanyProfileSeeder)->run($tenantId);
    }

    protected function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'restaurant';
        $slug = $base;
        $suffix = 1;

        while (Tenant::where('slug', $slug)->exists()) {
            $slug = "{$base}-".++$suffix;
        }

        return $slug;
    }
}
