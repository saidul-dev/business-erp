<x-app-layout>
    <x-slot name="title">{{ __('Roles & Permissions') }}</x-slot>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-brand-900">{{ __('Roles & Permissions') }}</h2>
                <p class="text-sm text-slate-500 mt-0.5">{{ __('Module-wise access matrix — Super Admin bypasses all checks') }}</p>
            </div>
            @can('roles.create')
            <a href="{{ route('roles.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 focus:ring-2 focus:ring-accent-500 focus:ring-offset-2">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                {{ __('Add Role') }}
            </a>
            @endcan
        </div>
    </x-slot>

    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <th class="sticky left-0 bg-slate-50 px-5 py-3 font-semibold">{{ __('Module / Permission') }}</th>
                    @foreach ($roles as $role)
                    <th class="px-4 py-3 text-center font-semibold whitespace-nowrap">
                        <span class="block">{{ $role->name }}</span>
                        <span class="block text-[10px] font-normal normal-case text-slate-400">{{ __(':count user(s)', ['count' => $role->users_count]) }}</span>
                        @if ($role->name !== 'Super Admin')
                        <span class="mt-1 flex items-center justify-center gap-0.5">
                            @can('roles.edit')
                            <a href="{{ route('roles.edit', $role) }}" title="{{ __('Edit role') }}"
                               class="rounded p-1 text-slate-400 hover:bg-brand-50 hover:text-brand-700">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"/></svg>
                            </a>
                            @endcan
                            @can('roles.delete')
                            <form method="POST" action="{{ route('roles.destroy', $role) }}"
                                  onsubmit="return confirm('{{ __('Delete role :name?', ['name' => $role->name]) }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" title="{{ __('Delete role') }}"
                                        class="rounded p-1 text-slate-400 hover:bg-rose-50 hover:text-rose-600">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                </button>
                            </form>
                            @endcan
                        </span>
                        @endif
                    </th>
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
