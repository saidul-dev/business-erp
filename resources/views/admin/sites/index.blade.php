<x-app-layout>
    <x-slot name="title">{{ __('Sites') }}</x-slot>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-brand-900">{{ __('Sites') }}</h2>
                <p class="text-sm text-slate-500 mt-0.5">{{ __('Branches, warehouses, outlets and other business locations') }}</p>
            </div>
            @can('sites.create')
            <a href="{{ route('sites.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 focus:ring-2 focus:ring-accent-500 focus:ring-offset-2">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                {{ __('Add Site') }}
            </a>
            @endcan
        </div>
    </x-slot>

    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full min-w-[720px] text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <th class="px-5 py-3 font-semibold">{{ __('Site') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Code') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Type') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Users') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Status') }}</th>
                    <th class="px-5 py-3 font-semibold text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($sites as $site)
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-3">
                        <div>
                            <span class="block font-semibold text-slate-800">{{ $site->name }}</span>
                            @if ($site->address)
                                <span class="block text-xs text-slate-400">{{ Str::limit($site->address, 50) }}</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-5 py-3 text-slate-600">{{ $site->code }}</td>
                    <td class="px-5 py-3">
                        <span class="inline-flex rounded-full bg-brand-50 px-2.5 py-0.5 text-xs font-semibold text-brand-700 ring-1 ring-brand-200">{{ $site->type }}</span>
                    </td>
                    <td class="px-5 py-3">
                        @if ($site->users_count > 0)
                        <div x-data="{
                                open: false,
                                top: 0,
                                left: 0,
                                toggle() {
                                    this.open = ! this.open;
                                    if (this.open) {
                                        const r = $refs.trigger.getBoundingClientRect();
                                        this.top = r.bottom + window.scrollY + 8;
                                        this.left = r.left + window.scrollX;
                                    }
                                },
                            }" class="inline-block">
                            <button type="button" x-ref="trigger" @click="toggle()"
                                    class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600 hover:bg-slate-200">
                                {{ $site->users_count }}
                                <svg class="h-3 w-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                            </button>

                            <template x-teleport="body">
                                <div x-show="open" @click.outside="open = false" x-transition
                                     :style="`top: ${top}px; left: ${left}px;`"
                                     class="fixed z-50 w-56 rounded-xl bg-white py-1.5 shadow-lg ring-1 ring-slate-200"
                                     style="display: none;">
                                    <p class="px-4 pt-1 pb-2 text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ __('Assigned Users') }}</p>
                                    @foreach ($site->users as $user)
                                    <div class="px-4 py-1.5">
                                        <span class="block text-sm font-medium text-slate-700">{{ $user->name }}</span>
                                        <span class="block text-[11px] text-slate-400">{{ $user->roles->pluck('name')->join(', ') ?: __('No role') }}</span>
                                    </div>
                                    @endforeach
                                </div>
                            </template>
                        </div>
                        @else
                        <span class="text-slate-400">0</span>
                        @endif
                    </td>
                    <td class="px-5 py-3">
                        @can('sites.edit')
                        <form method="POST" action="{{ route('sites.toggle-status', $site) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit"
                                    class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1
                                    {{ $site->status ? 'bg-emerald-50 text-emerald-600 ring-emerald-200' : 'bg-slate-100 text-slate-500 ring-slate-200' }}">
                                {{ $site->status ? __('Active') : __('Inactive') }}
                            </button>
                        </form>
                        @else
                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1
                            {{ $site->status ? 'bg-emerald-50 text-emerald-600 ring-emerald-200' : 'bg-slate-100 text-slate-500 ring-slate-200' }}">
                            {{ $site->status ? 'Active' : 'Inactive' }}
                        </span>
                        @endcan
                    </td>
                    <td class="px-5 py-3">
                        <div class="flex items-center justify-end gap-1">
                            @can('sites.edit')
                            <a href="{{ route('sites.edit', $site) }}" title="{{ __('Edit') }}"
                               class="rounded-lg p-2 text-slate-400 hover:bg-brand-50 hover:text-brand-700">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg>
                            </a>
                            @endcan
                            @can('sites.delete')
                            <form method="POST" action="{{ route('sites.destroy', $site) }}"
                                  onsubmit="return confirm('{{ __('Delete site :name? This cannot be undone.', ['name' => $site->name]) }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" title="{{ __('Delete') }}"
                                        class="rounded-lg p-2 text-slate-400 hover:bg-rose-50 hover:text-rose-600">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                </button>
                            </form>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-5 py-10 text-center text-slate-400">{{ __('No sites yet — add your first branch, warehouse or outlet.') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>

        @if ($sites->hasPages())
        <div class="border-t border-slate-100 px-5 py-4">
            {{ $sites->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
