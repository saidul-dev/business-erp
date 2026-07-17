<x-app-layout>
    <x-slot name="title">{{ __('Sale Report') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('Sale Report') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ __('Sales summary and Paid/Due for a date range.') }}</p>
        </div>
    </x-slot>

    <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 mb-6">
        <form method="GET" action="{{ route('sales.report') }}" class="flex flex-nowrap items-end gap-4 overflow-x-auto">
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
            <div class="w-56 shrink-0">
                <x-input-label for="party_id" :value="__('Customer')" />
                <div class="mt-1">
                    <x-searchable-select name="party_id" :options="$customers" :selected="$partyId"
                                          placeholder="{{ __('All customers') }}" />
                </div>
            </div>
            @if ($ecommerceEnabled)
            <div class="w-40 shrink-0">
                <x-input-label for="channel" :value="__('Channel')" />
                <select name="channel" class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">
                    <option value="">{{ __('All channels') }}</option>
                    <option value="pos" @selected($channel === 'pos')>{{ __('POS') }}</option>
                    <option value="online" @selected($channel === 'online')>{{ __('Online') }}</option>
                </select>
            </div>
            @endif
            <button type="submit" class="shrink-0 rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">{{ __('Filter') }}</button>
            @if ($siteId || $partyId || $channel)
            <a href="{{ route('sales.report') }}" class="shrink-0 text-sm font-semibold text-slate-500 hover:text-slate-700">{{ __('Clear') }}</a>
            @endif
        </form>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
        <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Sales') }}</p>
            <p class="mt-1 text-xl font-bold text-brand-900">{{ number_format($totals->count) }}</p>
        </div>
        <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Subtotal') }}</p>
            <p class="mt-1 text-xl font-bold text-slate-700">{{ number_format($totals->subtotal, 2) }}</p>
        </div>
        <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Discount') }}</p>
            <p class="mt-1 text-xl font-bold text-slate-700">{{ number_format($totals->discount, 2) }}</p>
        </div>
        <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Total') }}</p>
            <p class="mt-1 text-xl font-bold text-brand-900">{{ number_format($totals->total, 2) }}</p>
        </div>
        <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Paid / Due') }}</p>
            <p class="mt-1 text-xl font-bold"><span class="text-emerald-600">{{ number_format($totalPaid, 2) }}</span> <span class="text-sm text-slate-400">/</span> <span class="text-rose-600">{{ number_format($totalDue, 2) }}</span></p>
        </div>
    </div>

    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full min-w-[960px] text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <th class="px-5 py-3 font-semibold">{{ __('Date') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Sale No.') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Customer') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Site') }}</th>
                    @if ($ecommerceEnabled)
                    <th class="px-5 py-3 font-semibold">{{ __('Channel') }}</th>
                    @endif
                    <th class="px-5 py-3 font-semibold text-right">{{ __('Total') }}</th>
                    <th class="px-5 py-3 font-semibold text-right">{{ __('Paid') }}</th>
                    <th class="px-5 py-3 font-semibold text-right">{{ __('Due') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Status') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($sales as $sale)
                @php $paid = (float) ($sale->paid_amount ?? 0); $due = (float) $sale->total_amount - $paid; @endphp
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-3 text-slate-600 whitespace-nowrap">{{ $sale->order_date->format('d M Y') }}</td>
                    <td class="px-5 py-3 font-semibold text-slate-800">
                        <a href="{{ route('sales.show', $sale) }}" class="hover:text-accent-700 hover:underline">{{ $sale->sale_no }}</a>
                    </td>
                    <td class="px-5 py-3 text-slate-600">{{ $sale->party->name }}</td>
                    <td class="px-5 py-3 text-slate-600">{{ $sale->site->name }}</td>
                    @if ($ecommerceEnabled)
                    <td class="px-5 py-3">
                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $sale->channel === 'online' ? 'bg-accent-100 text-accent-700' : 'bg-slate-100 text-slate-600' }}">
                            {{ $sale->channel === 'online' ? __('Online') : __('POS') }}
                        </span>
                    </td>
                    @endif
                    <td class="px-5 py-3 text-right tabular-nums font-semibold text-slate-800">{{ number_format($sale->total_amount, 2) }}</td>
                    <td class="px-5 py-3 text-right tabular-nums text-emerald-600">{{ number_format($paid, 2) }}</td>
                    <td class="px-5 py-3 text-right tabular-nums {{ $due > 0.004 ? 'text-rose-600 font-semibold' : 'text-slate-400' }}">{{ number_format($due, 2) }}</td>
                    <td class="px-5 py-3"><x-sale-status-badge :status="$sale->status" /></td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ $ecommerceEnabled ? 9 : 8 }}" class="px-5 py-10 text-center text-slate-400">
                        {{ __('No sales in this range.') }}
                    </td>
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
