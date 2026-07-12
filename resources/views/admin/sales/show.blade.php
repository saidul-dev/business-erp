<x-app-layout>
    <x-slot name="title">{{ $sale->sale_no }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ $sale->sale_no }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ __('Sale detail') }}</p>
        </div>
    </x-slot>

    @php
        $statusAccent = [
            'pending' => 'border-slate-300',
            'partial' => 'border-amber-400',
            'delivered' => 'border-emerald-400',
            'cancelled' => 'border-rose-400',
        ][$sale->status] ?? 'border-slate-300';
        $due = $sale->party->receivableBalance();
    @endphp

    <div class="rounded-2xl border-l-4 {{ $statusAccent }} bg-white shadow-sm ring-1 ring-slate-200 p-6 mb-4">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3 text-sm">
                <span class="font-semibold text-slate-800">{{ $sale->party->name }}</span>
                <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                <span class="font-semibold text-slate-800">{{ $sale->site->name }}</span>
                <x-sale-status-badge :status="$sale->status" />
            </div>
            <a href="{{ route('sales.print', $sale) }}" target="_blank"
               class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z"/></svg>
                {{ __('Print') }}
            </a>
        </div>

        <div class="mt-5 grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div class="rounded-xl bg-slate-50 p-3">
                <div class="flex items-center gap-2 text-slate-400">
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                    <span class="text-[11px] font-semibold uppercase tracking-wide">{{ __('Order Date') }}</span>
                </div>
                <span class="mt-1.5 block text-sm font-semibold text-slate-700">{{ $sale->order_date->format('d M, Y') }}</span>
            </div>
            <div class="rounded-xl bg-slate-50 p-3">
                <div class="flex items-center gap-2 text-slate-400">
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                    <span class="text-[11px] font-semibold uppercase tracking-wide">{{ __('Created By') }}</span>
                </div>
                <span class="mt-1.5 block text-sm font-semibold text-slate-700">{{ $sale->creator->name ?? '—' }}</span>
            </div>
            <div class="rounded-xl bg-brand-50/70 ring-1 ring-brand-100 p-3">
                <div class="flex items-center gap-2 text-brand-400">
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                    <span class="text-[11px] font-semibold uppercase tracking-wide">{{ __('Total') }}</span>
                </div>
                <span class="mt-1.5 block text-xl font-bold text-brand-900">{{ number_format($sale->total_amount, 2) }}</span>
                @if ($sale->discount_amount > 0)
                <span class="block text-xs text-brand-400">{{ __('Discount') }}: {{ number_format($sale->discount_amount, 2) }}</span>
                @endif
            </div>
            <div class="rounded-xl p-3 {{ $due > 0 ? 'bg-amber-50 ring-1 ring-amber-200' : 'bg-slate-50' }}">
                <div class="flex items-center gap-2 {{ $due > 0 ? 'text-amber-500' : 'text-slate-400' }}">
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
                    <span class="text-[11px] font-semibold uppercase tracking-wide">{{ __('Customer Due') }}</span>
                </div>
                <span class="mt-1.5 block text-xl font-bold {{ $due > 0 ? 'text-amber-600' : 'text-slate-700' }}">{{ number_format($due, 2) }}</span>
            </div>
        </div>

        @if ($sale->note)
        <p class="mt-3 flex items-start gap-1.5 text-sm text-slate-500">
            <svg class="mt-0.5 h-4 w-4 shrink-0 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375m-13.5 8.25h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v13.5a1.5 1.5 0 0 0 1.5 1.5Z"/></svg>
            <span class="italic">{{ $sale->note }}</span>
        </p>
        @endif

        @if ($sale->channel === 'online')
        <div class="mt-4 rounded-xl bg-accent-50/60 ring-1 ring-accent-100 p-4">
            <div class="flex items-center gap-2 text-accent-600">
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                <span class="text-[11px] font-semibold uppercase tracking-wide">{{ __('Online Order — Delivery Details') }}</span>
            </div>
            <p class="mt-2 text-sm font-semibold text-slate-700">{{ $sale->shipping_name }} &middot; {{ $sale->shipping_phone }}</p>
            <p class="text-sm text-slate-600">{{ $sale->shipping_address }}</p>
        </div>
        @endif

        <div class="mt-5 flex flex-wrap items-center gap-3 border-t border-slate-100 pt-5">
            @if ($sale->status === 'pending')
            @can('sales.edit')
            <a href="{{ route('sales.edit', $sale) }}"
               class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg>
                {{ __('Edit') }}
            </a>
            @endcan
            @endif
            @if (in_array($sale->status, ['pending', 'partial']))
            @can('sales.approve')
            <a href="{{ route('sales.deliver.create', $sale) }}"
               class="inline-flex items-center gap-2 rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                {{ __('Deliver Items') }}
            </a>
            @endcan
            @can('sales.edit')
            <form method="POST" action="{{ route('sales.cancel', $sale) }}"
                  onsubmit="return confirm('{{ __('Cancel :no? Only the remaining un-delivered quantity is affected.', ['no' => $sale->sale_no]) }}');">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-100">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                    {{ __('Cancel Sale') }}
                </button>
            </form>
            @endcan
            @endif
            @can('sales.approve')
            @if ($sale->items->sum(fn ($item) => $item->returnable()) > 0)
            <a href="{{ route('sales.return.create', $sale) }}"
               class="inline-flex items-center gap-2 rounded-lg border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-100">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3"/></svg>
                {{ __('Return Items') }}
            </a>
            @endif
            @endcan
            @can('accounts.create')
            <a href="{{ route('collections.create', ['party_id' => $sale->party_id]) }}"
               class="inline-flex items-center gap-2 rounded-lg border border-brand-200 bg-brand-50 px-4 py-2 text-sm font-semibold text-brand-800 hover:bg-brand-100 sm:ml-auto">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-9-8.25h19.5v9.75a1.5 1.5 0 0 1-1.5 1.5h-16.5a1.5 1.5 0 0 1-1.5-1.5V6.75a1.5 1.5 0 0 1 1.5-1.5Z"/></svg>
                {{ __('Collect from Customer') }}
            </a>
            @endcan
        </div>
    </div>

    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 overflow-hidden mb-4">
        <div class="overflow-x-auto">
        <table class="w-full min-w-[760px] text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <th class="px-5 py-3 font-semibold">{{ __('Item') }}</th>
                    <th class="px-5 py-3 font-semibold text-right">{{ __('Ordered') }}</th>
                    <th class="px-5 py-3 font-semibold text-right">{{ __('Delivered') }}</th>
                    <th class="px-5 py-3 font-semibold text-right">{{ __('Returned') }}</th>
                    <th class="px-5 py-3 font-semibold text-right">{{ __('Remaining') }}</th>
                    <th class="px-5 py-3 font-semibold text-right">{{ __('Unit Price') }}</th>
                    <th class="px-5 py-3 font-semibold text-right">{{ __('Subtotal') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($sale->items as $item)
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-3">
                        <span class="block font-semibold text-slate-800">
                            {{ $item->product->name }}{{ $item->productVariant ? ' — '.$item->productVariant->label : '' }}
                        </span>
                        <span class="block text-xs text-slate-400">{{ $item->productVariant->sku ?? $item->product->sku }}</span>
                    </td>
                    <td class="px-5 py-3 text-right tabular-nums text-slate-500">{{ rtrim(rtrim(number_format($item->quantity, 4), '0'), '.') ?: '0' }} {{ $item->product->stockUnit?->short_name }}</td>
                    <td class="px-5 py-3 text-right tabular-nums text-slate-500">{{ rtrim(rtrim(number_format($item->delivered_quantity, 4), '0'), '.') ?: '0' }}</td>
                    <td class="px-5 py-3 text-right tabular-nums {{ $item->returned_quantity > 0 ? 'text-rose-600 font-semibold' : 'text-slate-300' }}">{{ rtrim(rtrim(number_format($item->returned_quantity, 4), '0'), '.') ?: '0' }}</td>
                    <td class="px-5 py-3 text-right tabular-nums {{ $item->remaining() > 0 ? 'text-amber-600 font-semibold' : 'text-slate-300' }}">{{ rtrim(rtrim(number_format($item->remaining(), 4), '0'), '.') ?: '0' }}</td>
                    <td class="px-5 py-3 text-right tabular-nums text-slate-500">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="px-5 py-3 text-right tabular-nums font-semibold text-slate-900">{{ number_format($item->subtotal, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>

    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-100">
            <h3 class="font-bold text-brand-900">{{ __('Deliveries') }}</h3>
        </div>
        @forelse ($sale->deliveries as $delivery)
        <div class="flex gap-3 border-l-4 border-blue-300 border-b border-b-slate-100 last:border-b-0 pl-4 pr-5 py-4">
            <span class="mt-0.5 grid h-7 w-7 shrink-0 place-items-center rounded-full bg-blue-50 text-blue-600">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.25" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
            </span>
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <span class="font-semibold text-slate-800">{{ $delivery->delivery_no }}</span>
                        @if ($delivery->consignment)
                            @can('delivery.view')
                            <a href="{{ route('courier-consignments.show', $delivery->consignment) }}"
                               class="inline-flex items-center gap-1 rounded-full bg-violet-50 px-2.5 py-0.5 text-xs font-semibold text-violet-700 ring-1 ring-violet-200 hover:bg-violet-100">
                                {{ $delivery->consignment->deliveryPartner->name }}
                                <x-courier-consignment-status-badge :status="$delivery->consignment->status" class="!bg-transparent !ring-0 !px-0 !py-0" />
                            </a>
                            @else
                            <span class="inline-flex items-center gap-1 rounded-full bg-violet-50 px-2.5 py-0.5 text-xs font-semibold text-violet-700 ring-1 ring-violet-200">
                                {{ $delivery->consignment->deliveryPartner->name }}
                            </span>
                            @endcan
                        @else
                        <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-500 ring-1 ring-slate-200">{{ __('Self Delivery') }}</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs text-slate-400">{{ $delivery->delivered_date->format('d M, Y') }} {{ __('by') }} {{ $delivery->deliveredBy->name ?? '—' }}</span>
                        <a href="{{ route('sales.deliveries.print', $delivery) }}" target="_blank"
                           class="text-xs font-semibold text-brand-800 hover:text-brand-700">{{ __('Print') }}</a>
                    </div>
                </div>
                <div class="mt-2 flex flex-wrap gap-x-6 gap-y-1 text-sm text-slate-600">
                    @foreach ($delivery->items as $di)
                    <span>
                        {{ $di->saleItem->product->name }}:
                        <span class="font-semibold text-slate-800">{{ rtrim(rtrim(number_format($di->quantity, 4), '0'), '.') ?: '0' }}</span>
                        @if ($di->batch_no || $di->expiry_date || $di->serial_no)
                            <span class="text-xs text-slate-400">({{ collect([$di->batch_no, $di->expiry_date?->format('d M, Y'), $di->serial_no])->filter()->implode(' · ') }})</span>
                        @endif
                    </span>
                    @endforeach
                </div>
                @if ($delivery->note)
                <p class="mt-1 text-xs text-slate-400">{{ $delivery->note }}</p>
                @endif
            </div>
        </div>
        @empty
        <div class="flex flex-col items-center gap-2 px-5 py-10 text-center">
            <svg class="h-8 w-8 text-slate-200" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5V13.5"/></svg>
            <p class="text-sm text-slate-400">{{ __('Nothing delivered yet.') }}</p>
            <p class="text-xs text-slate-300">{{ __('Use "Deliver Items" above once it\'s ready to go out.') }}</p>
        </div>
        @endforelse
    </div>

    <div class="mt-4 rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-100">
            <h3 class="font-bold text-brand-900">{{ __('Returns') }}</h3>
        </div>
        @forelse ($sale->returns as $return)
        <div class="flex gap-3 border-l-4 border-rose-300 border-b border-b-slate-100 last:border-b-0 pl-4 pr-5 py-4">
            <span class="mt-0.5 grid h-7 w-7 shrink-0 place-items-center rounded-full bg-rose-50 text-rose-600">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.25" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3"/></svg>
            </span>
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <span class="font-semibold text-slate-800">{{ $return->return_no }}</span>
                    <div class="flex items-center gap-3">
                        <span class="text-xs text-slate-400">{{ $return->return_date->format('d M, Y') }} {{ __('by') }} {{ $return->returnedBy->name ?? '—' }}</span>
                        <a href="{{ route('sales.returns.print', $return) }}" target="_blank"
                           class="text-xs font-semibold text-brand-800 hover:text-brand-700">{{ __('Print') }}</a>
                    </div>
                </div>
                <div class="mt-2 flex flex-wrap gap-x-6 gap-y-1 text-sm text-slate-600">
                    @foreach ($return->items as $ri)
                    <span>
                        {{ $ri->saleItem->product->name }}:
                        <span class="font-semibold text-slate-800">{{ rtrim(rtrim(number_format($ri->quantity, 4), '0'), '.') ?: '0' }}</span>
                        @if ($ri->batch_no || $ri->expiry_date || $ri->serial_no)
                            <span class="text-xs text-slate-400">({{ collect([$ri->batch_no, $ri->expiry_date?->format('d M, Y'), $ri->serial_no])->filter()->implode(' · ') }})</span>
                        @endif
                    </span>
                    @endforeach
                </div>
                @if ($return->note)
                <p class="mt-1 text-xs text-slate-400">{{ $return->note }}</p>
                @endif
            </div>
        </div>
        @empty
        <div class="flex flex-col items-center gap-2 px-5 py-10 text-center">
            <svg class="h-8 w-8 text-slate-200" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3"/></svg>
            <p class="text-sm text-slate-400">{{ __('Nothing returned yet.') }}</p>
        </div>
        @endforelse
    </div>
</x-app-layout>
