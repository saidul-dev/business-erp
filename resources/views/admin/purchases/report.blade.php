<x-app-layout>
    <x-slot name="title">{{ __('Purchase Report') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('Purchase Report') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ __('Purchases summary for a date range.') }}</p>
        </div>
    </x-slot>

    <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 mb-6">
        <form method="GET" action="{{ route('purchases.report') }}" class="flex flex-nowrap items-end gap-4 overflow-x-auto">
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
                <x-input-label for="party_id" :value="__('Supplier')" />
                <div class="mt-1">
                    <x-searchable-select name="party_id" :options="$suppliers" :selected="$partyId"
                                          placeholder="{{ __('All suppliers') }}" />
                </div>
            </div>
            <button type="submit" class="shrink-0 rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">{{ __('Filter') }}</button>
            @if ($siteId || $partyId)
            <a href="{{ route('purchases.report') }}" class="shrink-0 text-sm font-semibold text-slate-500 hover:text-slate-700">{{ __('Clear') }}</a>
            @endif
        </form>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Purchases') }}</p>
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
    </div>

    <p class="mb-4 text-xs text-slate-400">
        {{ __('Payments are recorded against a supplier\'s overall balance, not one purchase — see') }}
        @can('accounts.view')
        <a href="{{ route('due-report.index') }}" class="font-semibold text-accent-600 hover:underline">{{ __('Due Report') }}</a>
        @else
        {{ __('Due Report') }}
        @endcan
        {{ __('for supplier-wise Paid/Due.') }}
    </p>

    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full min-w-[800px] text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <th class="px-5 py-3 font-semibold">{{ __('Date') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Purchase No.') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Supplier') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Site') }}</th>
                    <th class="px-5 py-3 font-semibold text-right">{{ __('Subtotal') }}</th>
                    <th class="px-5 py-3 font-semibold text-right">{{ __('Discount') }}</th>
                    <th class="px-5 py-3 font-semibold text-right">{{ __('Total') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Status') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($purchases as $purchase)
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-3 text-slate-600 whitespace-nowrap">{{ $purchase->order_date->format('d M Y') }}</td>
                    <td class="px-5 py-3 font-semibold text-slate-800">
                        <a href="{{ route('purchases.show', $purchase) }}" class="hover:text-accent-700 hover:underline">{{ $purchase->purchase_no }}</a>
                    </td>
                    <td class="px-5 py-3 text-slate-600">{{ $purchase->party->name }}</td>
                    <td class="px-5 py-3 text-slate-600">{{ $purchase->site->name }}</td>
                    <td class="px-5 py-3 text-right tabular-nums text-slate-600">{{ number_format($purchase->subtotal_amount, 2) }}</td>
                    <td class="px-5 py-3 text-right tabular-nums text-slate-600">{{ number_format($purchase->discount_amount, 2) }}</td>
                    <td class="px-5 py-3 text-right tabular-nums font-semibold text-slate-800">{{ number_format($purchase->total_amount, 2) }}</td>
                    <td class="px-5 py-3"><x-purchase-status-badge :status="$purchase->status" /></td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-5 py-10 text-center text-slate-400">
                        {{ __('No purchases in this range.') }}
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>

        @if ($purchases->hasPages())
        <div class="border-t border-slate-100 px-5 py-4">
            {{ $purchases->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
