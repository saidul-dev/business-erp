<x-app-layout>
    <x-slot name="title">{{ $purchase->purchase_no }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ $purchase->purchase_no }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ __('Purchase detail') }}</p>
        </div>
    </x-slot>

    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 p-6 mb-4">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3 text-sm">
                <span class="font-semibold text-slate-800">{{ $purchase->party->name }}</span>
                <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                <span class="font-semibold text-slate-800">{{ $purchase->site->name }}</span>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('purchases.print', $purchase) }}" target="_blank"
                   class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z"/></svg>
                    {{ __('Print') }}
                </a>
                <x-purchase-status-badge :status="$purchase->status" class="!px-3 !py-1" />
            </div>
        </div>

        <div class="mt-5 grid grid-cols-1 sm:grid-cols-4 gap-4 text-sm">
            <div>
                <span class="block text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ __('Order Date') }}</span>
                <span class="block text-slate-700">{{ $purchase->order_date->format('d M, Y') }}</span>
            </div>
            <div>
                <span class="block text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ __('Created By') }}</span>
                <span class="block text-slate-700">{{ $purchase->creator->name ?? '—' }}</span>
            </div>
            <div>
                <span class="block text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ __('Total') }}</span>
                <span class="block font-semibold text-slate-800">{{ number_format($purchase->total_amount, 2) }}</span>
                @if ($purchase->discount_amount > 0)
                <span class="block text-xs text-slate-400">{{ __('Discount') }}: {{ number_format($purchase->discount_amount, 2) }}</span>
                @endif
            </div>
            <div>
                <span class="block text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ __('Supplier Due') }}</span>
                <span class="block font-semibold {{ $purchase->party->payableBalance() > 0 ? 'text-amber-600' : 'text-slate-700' }}">
                    {{ number_format($purchase->party->payableBalance(), 2) }}
                </span>
            </div>
            @if ($purchase->note)
            <div>
                <span class="block text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ __('Note') }}</span>
                <span class="block text-slate-700">{{ $purchase->note }}</span>
            </div>
            @endif
        </div>

        <div class="mt-5 flex flex-wrap gap-3 border-t border-slate-100 pt-5">
            @if (in_array($purchase->status, ['pending', 'partial']))
            @can('sourcing.approve')
            <a href="{{ route('purchases.receive.create', $purchase) }}"
               class="inline-flex items-center gap-2 rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                {{ __('Receive Items') }}
            </a>
            @endcan
            @can('sourcing.edit')
            <form method="POST" action="{{ route('purchases.cancel', $purchase) }}"
                  onsubmit="return confirm('{{ __('Cancel :no? Only the remaining un-received quantity is affected.', ['no' => $purchase->purchase_no]) }}');">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-100">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                    {{ __('Cancel Purchase') }}
                </button>
            </form>
            @endcan
            @endif
            @can('sourcing.approve')
            @if ($purchase->items->sum(fn ($item) => $item->returnable()) > 0)
            <a href="{{ route('purchases.return.create', $purchase) }}"
               class="inline-flex items-center gap-2 rounded-lg border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-100">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3"/></svg>
                {{ __('Return Items') }}
            </a>
            @endif
            @endcan
            @can('accounts.create')
            <a href="{{ route('payments.create', ['party_id' => $purchase->party_id]) }}"
               class="inline-flex items-center gap-2 rounded-lg border border-brand-200 bg-brand-50 px-4 py-2 text-sm font-semibold text-brand-800 hover:bg-brand-100">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-9-8.25h19.5v9.75a1.5 1.5 0 0 1-1.5 1.5h-16.5a1.5 1.5 0 0 1-1.5-1.5V6.75a1.5 1.5 0 0 1 1.5-1.5Z"/></svg>
                {{ __('Pay Supplier') }}
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
                    <th class="px-5 py-3 font-semibold text-right">{{ __('Received') }}</th>
                    <th class="px-5 py-3 font-semibold text-right">{{ __('Returned') }}</th>
                    <th class="px-5 py-3 font-semibold text-right">{{ __('Remaining') }}</th>
                    <th class="px-5 py-3 font-semibold text-right">{{ __('Unit Cost') }}</th>
                    <th class="px-5 py-3 font-semibold text-right">{{ __('Subtotal') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($purchase->items as $item)
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-3">
                        <span class="block font-semibold text-slate-800">
                            {{ $item->product->name }}{{ $item->productVariant ? ' — '.$item->productVariant->label : '' }}
                        </span>
                        <span class="block text-xs text-slate-400">{{ $item->productVariant->sku ?? $item->product->sku }}</span>
                    </td>
                    <td class="px-5 py-3 text-right text-slate-800">{{ rtrim(rtrim(number_format($item->quantity, 4), '0'), '.') ?: '0' }} {{ $item->product->stockUnit?->short_name }}</td>
                    <td class="px-5 py-3 text-right text-slate-600">{{ rtrim(rtrim(number_format($item->received_quantity, 4), '0'), '.') ?: '0' }}</td>
                    <td class="px-5 py-3 text-right {{ $item->returned_quantity > 0 ? 'text-rose-600 font-semibold' : 'text-slate-400' }}">{{ rtrim(rtrim(number_format($item->returned_quantity, 4), '0'), '.') ?: '0' }}</td>
                    <td class="px-5 py-3 text-right {{ $item->remaining() > 0 ? 'text-amber-600 font-semibold' : 'text-slate-400' }}">{{ rtrim(rtrim(number_format($item->remaining(), 4), '0'), '.') ?: '0' }}</td>
                    <td class="px-5 py-3 text-right text-slate-600">{{ number_format($item->unit_cost, 2) }}</td>
                    <td class="px-5 py-3 text-right font-semibold text-slate-800">{{ number_format($item->subtotal, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>

    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-100">
            <h3 class="font-bold text-brand-900">{{ __('Goods Receipts') }}</h3>
        </div>
        @forelse ($purchase->receipts as $receipt)
        <div class="px-5 py-4 border-b border-slate-100 last:border-0">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <span class="font-semibold text-slate-800">{{ $receipt->receipt_no }}</span>
                <div class="flex items-center gap-3">
                    <span class="text-xs text-slate-400">{{ $receipt->received_date->format('d M, Y') }} {{ __('by') }} {{ $receipt->receivedBy->name ?? '—' }}</span>
                    <a href="{{ route('purchases.receipts.print', $receipt) }}" target="_blank"
                       class="text-xs font-semibold text-brand-800 hover:text-brand-700">{{ __('Print') }}</a>
                </div>
            </div>
            <div class="mt-2 flex flex-wrap gap-x-6 gap-y-1 text-sm text-slate-600">
                @foreach ($receipt->items as $ri)
                <span>
                    {{ $ri->purchaseItem->product->name }}:
                    <span class="font-semibold text-slate-800">{{ rtrim(rtrim(number_format($ri->quantity, 4), '0'), '.') ?: '0' }}</span>
                    @if ($ri->batch_no || $ri->expiry_date || $ri->serial_no)
                        <span class="text-xs text-slate-400">({{ collect([$ri->batch_no, $ri->expiry_date?->format('d M, Y'), $ri->serial_no])->filter()->implode(' · ') }})</span>
                    @endif
                </span>
                @endforeach
            </div>
            @if ($receipt->note)
            <p class="mt-1 text-xs text-slate-400">{{ $receipt->note }}</p>
            @endif
        </div>
        @empty
        <p class="px-5 py-10 text-center text-slate-400">{{ __('Nothing received yet.') }}</p>
        @endforelse
    </div>

    <div class="mt-4 rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-100">
            <h3 class="font-bold text-brand-900">{{ __('Returns') }}</h3>
        </div>
        @forelse ($purchase->returns as $return)
        <div class="px-5 py-4 border-b border-slate-100 last:border-0">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <span class="font-semibold text-slate-800">{{ $return->return_no }}</span>
                <div class="flex items-center gap-3">
                    <span class="text-xs text-slate-400">{{ $return->return_date->format('d M, Y') }} {{ __('by') }} {{ $return->returnedBy->name ?? '—' }}</span>
                    <a href="{{ route('purchases.returns.print', $return) }}" target="_blank"
                       class="text-xs font-semibold text-brand-800 hover:text-brand-700">{{ __('Print') }}</a>
                </div>
            </div>
            <div class="mt-2 flex flex-wrap gap-x-6 gap-y-1 text-sm text-slate-600">
                @foreach ($return->items as $ri)
                <span>
                    {{ $ri->purchaseItem->product->name }}:
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
        @empty
        <p class="px-5 py-10 text-center text-slate-400">{{ __('Nothing returned yet.') }}</p>
        @endforelse
    </div>
</x-app-layout>
