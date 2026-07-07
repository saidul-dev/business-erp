<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class UserController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:users.view', only: ['index']),
            new Middleware('permission:users.create', only: ['create', 'store']),
            new Middleware('permission:users.edit', only: ['edit', 'update']),
            new Middleware('permission:users.delete', only: ['destroy']),
        ];
    }

    public function index()
    {
        $users = User::with('roles')->orderBy('name')->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create', ['roles' => $this->assignableRoles()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'roles' => ['array'],
            'roles.*' => [Rule::in($this->assignableRoles()->pluck('name'))],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        $user->syncRoles($validated['roles'] ?? []);

        return redirect()->route('users.index')->with('success', "User \"{$user->name}\" created.");
    }

    public function edit(User $user)
    {
        $this->guardSuperAdminTarget($user);

        return view('admin.users.edit', ['user' => $user, 'roles' => $this->assignableRoles()]);
    }

    public function update(Request $request, User $user)
    {
        $this->guardSuperAdminTarget($user);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'roles' => ['array'],
            'roles.*' => [Rule::in($this->assignableRoles()->pluck('name'))],
        ]);

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if (! empty($validated['password'])) {
            $user->password = $validated['password'];
        }

        $user->save();
        $user->syncRoles($validated['roles'] ?? []);

        return redirect()->route('users.index')->with('success', "User \"{$user->name}\" updated.");
    }

    public function destroy(User $user)
    {
        $this->guardSuperAdminTarget($user);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', "User \"{$user->name}\" deleted.");
    }

    /**
     * Only Super Admins may hand out or touch the Super Admin role.
     */
    protected function assignableRoles()
    {
        return Role::orderBy('name')->get()
            ->when(! auth()->user()->hasRole('Super Admin'),
                fn ($roles) => $roles->reject(fn ($role) => $role->name === 'Super Admin'));
    }

    protected function guardSuperAdminTarget(User $user): void
    {
        abort_if(
            $user->hasRole('Super Admin') && ! auth()->user()->hasRole('Super Admin'),
            403,
            'Only a Super Admin can manage a Super Admin account.'
        );
    }
}
