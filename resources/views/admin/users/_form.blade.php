@php $editing = isset($user); @endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4"
     x-data="{
         selectedRoles: @js(old('roles', $editing ? $user->roles->pluck('name')->all() : [])),
         rolePermMap: @js($rolePermissionsMap),
         viaRole(permission) {
             return this.selectedRoles.includes('Super Admin')
                 || this.selectedRoles.some(role => (this.rolePermMap[role] || []).includes(permission));
         },
     }">
    <!-- Account details -->
    <div class="lg:col-span-2 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 space-y-5">
        <div>
            <h3 class="font-bold text-brand-900">{{ __('Account Details') }}</h3>
            <p class="text-xs text-slate-400">{{ __('Basic information and login credentials') }}</p>
        </div>

        <div>
            <x-input-label for="name" :value="__('Full Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                          :value="old('name', $user->name ?? '')" required autofocus />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                          :value="old('email', $user->email ?? '')" required />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="password" :value="__('Password')" />
                <x-text-input id="password" name="password" type="password" class="mt-1 block w-full"
                              :required="! $editing" autocomplete="new-password" />
                @if ($editing)
                    <p class="mt-1 text-xs text-slate-400">{{ __('Leave blank to keep the current password.') }}</p>
                @endif
                <x-input-error class="mt-2" :messages="$errors->get('password')" />
            </div>
            <div>
                <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                <x-text-input id="password_confirmation" name="password_confirmation" type="password"
                              class="mt-1 block w-full" :required="! $editing" autocomplete="new-password" />
            </div>
        </div>
    </div>

    <!-- Roles -->
    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <div class="mb-4">
            <h3 class="font-bold text-brand-900">{{ __('Roles') }}</h3>
            <p class="text-xs text-slate-400">{{ __('Permissions follow the assigned role(s)') }}</p>
        </div>

        @php $checkedRoles = old('roles', $editing ? $user->roles->pluck('name')->all() : []); @endphp

        <div class="space-y-2">
            @foreach ($roles as $role)
            <label class="flex items-center gap-3 rounded-xl px-4 py-3 ring-1 ring-slate-200 hover:bg-slate-50 cursor-pointer has-[:checked]:ring-accent-500 has-[:checked]:bg-accent-500/5">
                <input type="checkbox" name="roles[]" value="{{ $role->name }}"
                       x-model="selectedRoles"
                       @checked(in_array($role->name, $checkedRoles))
                       class="rounded border-slate-300 text-brand-700 focus:ring-accent-500">
                <span>
                    <span class="block text-sm font-semibold text-slate-800">{{ $role->name }}</span>
                    <span class="block text-[11px] text-slate-400">
                        @if ($role->name === 'Super Admin')
                            {{ __('Full access — bypasses all checks') }}
                        @else
                            {{ __(':count permissions', ['count' => $role->permissions->count()]) }}
                        @endif
                    </span>
                </span>
            </label>
            @endforeach
        </div>
        <x-input-error class="mt-2" :messages="$errors->get('roles')" />
        <x-input-error class="mt-2" :messages="$errors->get('roles.*')" />
    </div>

    <!-- Sites -->
    <div class="lg:col-span-3 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200" x-data="{ checked: {{ json_encode(old('sites', $editing ? $user->sites->pluck('id')->all() : [])) }} }">
        <div class="mb-4">
            <h3 class="font-bold text-brand-900">{{ __('Site Access') }}</h3>
            <p class="text-xs text-slate-400">{{ __('Which site(s) this user can work in. Pick a Default Site when more than one is assigned.') }}</p>
        </div>

        @php
            $checkedSites = old('sites', $editing ? $user->sites->pluck('id')->all() : []);
            $defaultSiteId = old('default_site', $editing ? optional($user->sites->firstWhere('pivot.is_default', true))->id : null);
        @endphp

        @if ($sites->isEmpty())
            <p class="text-sm text-slate-400">{{ __('No sites have been created yet.') }} <a href="{{ route('sites.create') }}" class="text-accent-600 font-semibold hover:underline">{{ __('Add one first.') }}</a></p>
        @else
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
            @foreach ($sites as $site)
            <label class="flex items-center gap-3 rounded-xl px-4 py-3 ring-1 ring-slate-200 hover:bg-slate-50 cursor-pointer has-[:checked]:ring-accent-500 has-[:checked]:bg-accent-500/5">
                <input type="checkbox" name="sites[]" value="{{ $site->id }}"
                       x-model.number="checked"
                       @checked(in_array($site->id, $checkedSites))
                       class="rounded border-slate-300 text-brand-700 focus:ring-accent-500">
                <span class="flex-1">
                    <span class="block text-sm font-semibold text-slate-800">{{ $site->name }}</span>
                    <span class="block text-[11px] text-slate-400">{{ $site->type }} &middot; {{ $site->code }}</span>
                </span>
                <span class="text-[11px] font-medium text-slate-400" x-show="checked.includes({{ $site->id }})">
                    <label class="inline-flex items-center gap-1 cursor-pointer">
                        <input type="radio" name="default_site" value="{{ $site->id }}"
                               @checked($defaultSiteId === $site->id)
                               class="text-accent-600 focus:ring-accent-500">
                        {{ __('Default') }}
                    </label>
                </span>
            </label>
            @endforeach
        </div>
        @endif
        <x-input-error class="mt-2" :messages="$errors->get('sites')" />
        <x-input-error class="mt-2" :messages="$errors->get('sites.*')" />
    </div>

    <!-- Direct Permissions -->
    <div class="lg:col-span-3 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <div class="mb-4">
            <h3 class="font-bold text-brand-900">{{ __('Direct Permissions') }}</h3>
            <p class="text-xs text-slate-400">{{ __('Extra permissions for this user only, on top of whatever their role(s) already grant. Use this to fine-tune one person without creating a new role.') }}</p>
        </div>

        @php $checkedDirectPermissions = old('permissions', $editing ? $user->getDirectPermissions()->pluck('name')->all() : []); @endphp

        <x-input-error class="mb-3" :messages="$errors->get('permissions')" />
        <x-input-error class="mb-3" :messages="$errors->get('permissions.*')" />

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach ($permissions as $module => $modulePermissions)
            <div class="rounded-xl ring-1 ring-slate-200 overflow-hidden"
                 x-data="{
                     all() { return [...$el.querySelectorAll('.direct-perm-box')].every(c => c.checked) },
                     toggleAll(on) { $el.querySelectorAll('.direct-perm-box').forEach(c => c.checked = on) },
                 }">
                <label class="flex items-center gap-2.5 bg-brand-50/70 px-4 py-2.5 cursor-pointer border-b border-slate-200">
                    <input type="checkbox" class="rounded border-slate-300 text-brand-700 focus:ring-accent-500"
                           @change="toggleAll($event.target.checked)"
                           @checked(collect($modulePermissions)->every(fn ($p) => in_array($p->name, $checkedDirectPermissions)))>
                    <span class="text-sm font-bold uppercase tracking-wide text-brand-800">{{ ucfirst($module) }}</span>
                </label>
                <div class="px-4 py-3 space-y-2">
                    @foreach ($modulePermissions as $permission)
                    <template x-if="viaRole('{{ $permission->name }}')">
                        <label class="flex items-center gap-2.5 cursor-not-allowed" title="{{ __('Already granted via role') }}">
                            <input type="checkbox" checked disabled class="rounded border-slate-300 text-slate-400">
                            <span class="text-sm text-slate-400">{{ ucfirst(explode('.', $permission->name)[1]) }}</span>
                            <span class="text-[10px] font-semibold uppercase tracking-wide text-accent-600">{{ __('via role') }}</span>
                        </label>
                    </template>
                    <template x-if="!viaRole('{{ $permission->name }}')">
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                                   @checked(in_array($permission->name, $checkedDirectPermissions))
                                   class="direct-perm-box rounded border-slate-300 text-brand-700 focus:ring-accent-500">
                            <span class="text-sm text-slate-600">{{ ucfirst(explode('.', $permission->name)[1]) }}</span>
                        </label>
                    </template>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<div class="mt-5 flex items-center gap-3">
    <x-primary-button>{{ $editing ? __('Update User') : __('Create User') }}</x-primary-button>
    <a href="{{ route('users.index') }}" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50">{{ __('Cancel') }}</a>
</div>
