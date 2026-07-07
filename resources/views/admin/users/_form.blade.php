@php $editing = isset($user); @endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    <!-- Account details -->
    <div class="lg:col-span-2 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 space-y-5">
        <div>
            <h3 class="font-bold text-brand-900">Account Details</h3>
            <p class="text-xs text-slate-400">Basic information and login credentials</p>
        </div>

        <div>
            <x-input-label for="name" value="Full Name" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                          :value="old('name', $user->name ?? '')" required autofocus />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                          :value="old('email', $user->email ?? '')" required />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="password" value="Password" />
                <x-text-input id="password" name="password" type="password" class="mt-1 block w-full"
                              :required="! $editing" autocomplete="new-password" />
                @if ($editing)
                    <p class="mt-1 text-xs text-slate-400">Leave blank to keep the current password.</p>
                @endif
                <x-input-error class="mt-2" :messages="$errors->get('password')" />
            </div>
            <div>
                <x-input-label for="password_confirmation" value="Confirm Password" />
                <x-text-input id="password_confirmation" name="password_confirmation" type="password"
                              class="mt-1 block w-full" :required="! $editing" autocomplete="new-password" />
            </div>
        </div>
    </div>

    <!-- Roles -->
    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <div class="mb-4">
            <h3 class="font-bold text-brand-900">Roles</h3>
            <p class="text-xs text-slate-400">Permissions follow the assigned role(s)</p>
        </div>

        @php $checkedRoles = old('roles', $editing ? $user->roles->pluck('name')->all() : []); @endphp

        <div class="space-y-2">
            @foreach ($roles as $role)
            <label class="flex items-center gap-3 rounded-xl px-4 py-3 ring-1 ring-slate-200 hover:bg-slate-50 cursor-pointer has-[:checked]:ring-accent-500 has-[:checked]:bg-accent-500/5">
                <input type="checkbox" name="roles[]" value="{{ $role->name }}"
                       @checked(in_array($role->name, $checkedRoles))
                       class="rounded border-slate-300 text-brand-700 focus:ring-accent-500">
                <span>
                    <span class="block text-sm font-semibold text-slate-800">{{ $role->name }}</span>
                    <span class="block text-[11px] text-slate-400">
                        @if ($role->name === 'Super Admin')
                            Full access — bypasses all checks
                        @else
                            {{ $role->permissions->count() }} permissions
                        @endif
                    </span>
                </span>
            </label>
            @endforeach
        </div>
        <x-input-error class="mt-2" :messages="$errors->get('roles')" />
        <x-input-error class="mt-2" :messages="$errors->get('roles.*')" />
    </div>
</div>

<div class="mt-5 flex items-center gap-3">
    <x-primary-button>{{ $editing ? 'Update User' : 'Create User' }}</x-primary-button>
    <a href="{{ route('users.index') }}" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50">Cancel</a>
</div>
