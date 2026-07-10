<x-app-layout>
    <x-slot name="title">{{ __('Sales') }}</x-slot>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-brand-900">{{ __('Sales') }}</h2>
                <p class="text-sm text-slate-500 mt-0.5">{{ __('Sales orders and deliveries to customers') }}</p>
            </div>
            @can('sales.create')
            <a href="{{ route('sales.create') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                {{ __('New Sale') }}
            </a>
            @endcan
        </div>
    </x-slot>

    <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 mb-6">
        <form method="GET" action="{{ route('sales.index') }}" class="flex flex-nowrap items-end gap-4 overflow-x-auto">
            <div class="w-56 shrink-0">
                <x-input-label for="q" :value="__('Search')" />
                <input type="search" id="q" name="q" value="{{ $q }}" placeholder="{{ __('Sale no. or customer…') }}"
                       class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">
            </div>
            <div class="w-40 shrink-0">
                <x-input-label for="from" :value="__('From')" />
                <x-text-input id="from" name="from" type="date" class="mt-1 block w-full" :value="$from" />
            </div>
            <div class="w-40 shrink-0">
                <x-input-label for="to" :value="__('To')" />
                <x-text-input id="to" name="to" type="date" class="mt-1 block w-full" :value="$to" />
            </div>
            <div class="w-56 shrink-0">
                <x-input-label for="site_id" :value="__('Site')" />
                <div class="mt-1">
                    <x-searchable-select name="site_id" :options="$sites" :selected="$siteId"
                                          placeholder="{{ __('All sites') }}" />
                </div>
            </div>
            <div class="w-48 shrink-0">
                <x-input-label for="status" :value="__('Status')" />
                <select name="status"
                        class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">
                    <option value="">{{ __('All statuses') }}</option>
                    @foreach (['pending' => __('Pending'), 'partial' => __('Partially Delivered'), 'delivered' => __('Delivered'), 'cancelled' => __('Cancelled')] as $key => $label)
                        <option value="{{ $key }}" @selected($status === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="shrink-0 rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">{{ __('Filter') }}</button>
            @if ($q || $from || $to || $siteId || $status)
            <a href="{{ route('sales.index') }}" class="shrink-0 text-sm font-semibold text-slate-500 hover:text-slate-700">{{ __('Clear') }}</a>
            @endif
        </form>
    </div>

    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full min-w-[860px] text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <th class="px-5 py-3 font-semibold">{{ __('Sale') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Customer') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Site') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Order Date') }}</th>
                    <th class="px-5 py-3 font-semibold text-right">{{ __('Total') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Status') }}</th>
                    <th class="px-5 py-3 font-semibold"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($sales as $sale)
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-3 font-semibold text-slate-800">{{ $sale->sale_no }}</td>
                    <td class="px-5 py-3 text-slate-600">{{ $sale->party->name }}</td>
                    <td class="px-5 py-3 text-slate-600">{{ $sale->site->name }}</td>
                    <td class="px-5 py-3 text-slate-600">{{ $sale->order_date->format('d M, Y') }}</td>
                    <td class="px-5 py-3 text-right font-semibold text-slate-800">{{ number_format($sale->total_amount, 2) }}</td>
                    <td class="px-5 py-3">
                        <x-sale-status-badge :status="$sale->status" />
                    </td>
                    <td class="px-5 py-3 text-right">
                        <a href="{{ route('sales.show', $sale) }}" class="font-semibold text-accent-600 hover:text-accent-800">{{ __('View') }}</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-5 py-10 text-center text-slate-400">{{ __('No sales yet.') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>

        @if ($sales->hasPages())
        <div class="border-t border-slate-100 px-5 py-4">
            {{ $sales->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
