@php
    $editing = isset($role);
    $checkedPermissions = old('permissions', $editing ? $role->permissions->pluck('name')->all() : []);
@endphp

<div class="space-y-4">
    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <div class="max-w-md">
            <x-input-label for="name" value="Role Name" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                          :value="old('name', $role->name ?? '')" required autofocus />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>
    </div>

    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <div class="mb-4">
            <h3 class="font-bold text-brand-900">Permissions</h3>
            <p class="text-xs text-slate-400">Module-wise access — use the module checkbox to toggle all of its actions</p>
        </div>
        <x-input-error class="mb-3" :messages="$errors->get('permissions')" />
        <x-input-error class="mb-3" :messages="$errors->get('permissions.*')" />

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach ($permissions as $module => $modulePermissions)
            <div class="rounded-xl ring-1 ring-slate-200 overflow-hidden"
                 x-data="{
                     all() { return [...$el.querySelectorAll('.perm-box')].every(c => c.checked) },
                     toggleAll(on) { $el.querySelectorAll('.perm-box').forEach(c => c.checked = on) },
                 }">
                <label class="flex items-center gap-2.5 bg-brand-50/70 px-4 py-2.5 cursor-pointer border-b border-slate-200">
                    <input type="checkbox" class="rounded border-slate-300 text-brand-700 focus:ring-accent-500"
                           @change="toggleAll($event.target.checked)"
                           @checked(collect($modulePermissions)->every(fn ($p) => in_array($p->name, $checkedPermissions)))>
                    <span class="text-sm font-bold uppercase tracking-wide text-brand-800">{{ ucfirst($module) }}</span>
                </label>
                <div class="px-4 py-3 space-y-2">
                    @foreach ($modulePermissions as $permission)
                    <label class="flex items-center gap-2.5 cursor-pointer">
                        <input type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                               @checked(in_array($permission->name, $checkedPermissions))
                               class="perm-box rounded border-slate-300 text-brand-700 focus:ring-accent-500">
                        <span class="text-sm text-slate-600">{{ ucfirst(explode('.', $permission->name)[1]) }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<div class="mt-5 flex items-center gap-3">
    <x-primary-button>{{ $editing ? 'Update Role' : 'Create Role' }}</x-primary-button>
    <a href="{{ route('roles.index') }}" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50">Cancel</a>
</div>
