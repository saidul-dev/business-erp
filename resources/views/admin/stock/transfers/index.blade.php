<x-app-layout>
    <x-slot name="title">{{ __('Stock Transfers') }}</x-slot>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-brand-900">{{ __('Stock Transfers') }}</h2>
                <p class="text-sm text-slate-500 mt-0.5">{{ __('Dispatch history between Sites.') }}</p>
            </div>
            @can('inventory.create')
            <a href="{{ route('stock.transfers.create') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                {{ __('New Transfer') }}
            </a>
            @endcan
        </div>
    </x-slot>

    <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 mb-6">
        <form method="GET" action="{{ route('stock.transfers.index') }}" class="flex flex-nowrap items-end gap-4 overflow-x-auto">
            <div class="w-56 shrink-0">
                <x-input-label for="site_id" :value="__('Site')" />
                <div class="mt-1">
                    <x-searchable-select name="site_id" :options="$sites" :selected="$siteId"
                                          placeholder="{{ __('All sites') }}" :auto-submit="true" />
                </div>
            </div>
            <div class="w-48 shrink-0">
                <x-input-label for="status" :value="__('Status')" />
                <select name="status" onchange="this.form.submit()"
                        class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">
                    <option value="">{{ __('All statuses') }}</option>
                    @foreach (['in_transit' => __('In Transit'), 'received' => __('Received'), 'cancelled' => __('Cancelled')] as $key => $label)
                        <option value="{{ $key }}" @selected($status === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full min-w-[860px] text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <th class="px-5 py-3 font-semibold">{{ __('Transfer') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Route') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Dispatched') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Received') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Status') }}</th>
                    <th class="px-5 py-3 font-semibold"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($transfers as $transfer)
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-3 font-semibold text-slate-800">{{ $transfer->transfer_no }}</td>
                    <td class="px-5 py-3 text-slate-600">{{ $transfer->fromSite->name }} → {{ $transfer->toSite->name }}</td>
                    <td class="px-5 py-3 text-slate-600">
                        {{ $transfer->dispatched_at->format('d M, Y') }}
                        <span class="block text-xs text-slate-400">{{ $transfer->dispatchedBy->name ?? '—' }}</span>
                    </td>
                    <td class="px-5 py-3 text-slate-600">
                        @if ($transfer->received_at)
                            {{ $transfer->received_at->format('d M, Y') }}
                            <span class="block text-xs text-slate-400">{{ $transfer->receivedBy->name ?? '—' }}</span>
                        @else
                            —
                        @endif
                    </td>
                    <td class="px-5 py-3">
                        @if ($transfer->status === 'in_transit')
                            <span class="inline-flex rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-600 ring-1 ring-amber-200">{{ __('In Transit') }}</span>
                        @elseif ($transfer->status === 'received')
                            <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-600 ring-1 ring-emerald-200">{{ __('Received') }}</span>
                        @else
                            <span class="inline-flex rounded-full bg-rose-50 px-2.5 py-0.5 text-xs font-semibold text-rose-600 ring-1 ring-rose-200">{{ __('Cancelled') }}</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-right">
                        <a href="{{ route('stock.transfers.show', $transfer) }}" class="font-semibold text-accent-600 hover:text-accent-800">{{ __('View') }}</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-5 py-10 text-center text-slate-400">{{ __('No transfers yet.') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>

        @if ($transfers->hasPages())
        <div class="border-t border-slate-100 px-5 py-4">
            {{ $transfers->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
