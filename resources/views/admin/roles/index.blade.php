<x-app-layout>
    <x-slot name="title">Roles &amp; Permissions</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">Roles &amp; Permissions</h2>
            <p class="text-sm text-slate-500 mt-0.5">Module-wise access matrix — Super Admin bypasses all checks</p>
        </div>
    </x-slot>

    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <th class="sticky left-0 bg-slate-50 px-5 py-3 font-semibold">Module / Permission</th>
                    @foreach ($roles as $role)
                    <th class="px-4 py-3 text-center font-semibold whitespace-nowrap">{{ $role->name }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($permissions as $module => $modulePermissions)
                <tr class="bg-brand-50/60">
                    <td colspan="{{ $roles->count() + 1 }}" class="px-5 py-2 text-xs font-bold uppercase tracking-wider text-brand-800">
                        {{ ucfirst($module) }}
                    </td>
                </tr>
                    @foreach ($modulePermissions as $permission)
                    <tr class="hover:bg-slate-50">
                        <td class="sticky left-0 bg-white px-5 py-2.5 text-slate-600">
                            {{ ucfirst(explode('.', $permission->name)[1]) }}
                            <span class="ml-1 text-[11px] text-slate-400">({{ $permission->name }})</span>
                        </td>
                        @foreach ($roles as $role)
                        <td class="px-4 py-2.5 text-center">
                            @if ($role->name === 'Super Admin' || $role->hasPermissionTo($permission))
                                <span class="inline-grid h-5 w-5 place-items-center rounded-full bg-accent-500/15 text-accent-600">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                </span>
                            @else
                                <span class="text-slate-300">&mdash;</span>
                            @endif
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
</x-app-layout>
