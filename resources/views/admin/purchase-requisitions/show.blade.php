<x-app-layout>
    <x-slot name="title">{{ $purchaseRequisition->requisition_no }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ $purchaseRequisition->requisition_no }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ __('Purchase requisition detail') }}</p>
        </div>
    </x-slot>

    @php
        $statusAccent = [
            'pending' => 'border-slate-300',
            'approved' => 'border-emerald-400',
            'rejected' => 'border-rose-400',
            'converted' => 'border-blue-400',
            'cancelled' => 'border-rose-400',
        ][$purchaseRequisition->status] ?? 'border-slate-300';
    @endphp

    @if (session('success'))
    <div class="mb-4 rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-700 ring-1 ring-emerald-200">{{ session('success') }}</div>
    @endif
    @if (session('error'))
    <div class="mb-4 rounded-xl bg-rose-50 px-4 py-3 text-sm text-rose-700 ring-1 ring-rose-200">{{ session('error') }}</div>
    @endif

    <div class="rounded-2xl border-l-4 {{ $statusAccent }} bg-white shadow-sm ring-1 ring-slate-200 p-6 mb-4">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3 text-sm">
                <span class="font-semibold text-slate-800">{{ $purchaseRequisition->site->name }}</span>
                @if ($purchaseRequisition->party)
                <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                <span class="font-semibold text-slate-800">{{ $purchaseRequisition->party->name }}</span>
                @endif
                <x-purchase-requisition-status-badge :status="$purchaseRequisition->status" />
            </div>
            <a href="{{ route('purchase-requisitions.print', $purchaseRequisition) }}" target="_blank"
               class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                {{ __('Print') }}
            </a>
        </div>

        <div class="mt-5 grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div class="rounded-xl bg-slate-50 p-3">
                <span class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ __('Request Date') }}</span>
                <span class="mt-1.5 block text-sm font-semibold text-slate-700">{{ $purchaseRequisition->request_date->format('d M, Y') }}</span>
            </div>
            <div class="rounded-xl bg-slate-50 p-3">
                <span class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ __('Required By') }}</span>
                <span class="mt-1.5 block text-sm font-semibold text-slate-700">{{ $purchaseRequisition->required_by_date?->format('d M, Y') ?? '—' }}</span>
            </div>
            <div class="rounded-xl bg-slate-50 p-3">
                <span class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ __('Requested By') }}</span>
                <span class="mt-1.5 block text-sm font-semibold text-slate-700">{{ $purchaseRequisition->creator->name ?? '—' }}</span>
            </div>
            <div class="rounded-xl bg-brand-50/70 ring-1 ring-brand-100 p-3">
                <span class="text-[11px] font-semibold uppercase tracking-wide text-brand-400">{{ __('Est. Total') }}</span>
                <span class="mt-1.5 block text-xl font-bold text-brand-900">{{ number_format($purchaseRequisition->estimated_total_amount, 2) }}</span>
            </div>
        </div>

        @if ($purchaseRequisition->status === 'rejected' && $purchaseRequisition->rejection_reason)
        <p class="mt-3 text-sm text-rose-600"><strong>{{ __('Rejection reason') }}:</strong> {{ $purchaseRequisition->rejection_reason }}</p>
        @endif

        @if ($purchaseRequisition->reviewer)
        <p class="mt-3 text-xs text-slate-400">
            {{ __('Reviewed by') }} {{ $purchaseRequisition->reviewer->name }} {{ __('on') }} {{ $purchaseRequisition->reviewed_at?->format('d M, Y H:i') }}
        </p>
        @endif

        @if ($purchaseRequisition->note)
        <p class="mt-3 text-sm text-slate-500 italic">{{ $purchaseRequisition->note }}</p>
        @endif

        <div class="mt-5 flex flex-wrap items-center gap-3 border-t border-slate-100 pt-5">
            @if ($purchaseRequisition->status === 'pending')
                @can('purchase-requisitions.approve')
                <form method="POST" action="{{ route('purchase-requisitions.approve', $purchaseRequisition) }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                        {{ __('Approve') }}
                    </button>
                </form>
                <button type="button" onclick="document.getElementById('reject-form').classList.toggle('hidden')"
                        class="inline-flex items-center gap-2 rounded-lg border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-100">
                    {{ __('Reject') }}
                </button>
                @endcan
                @can('purchase-requisitions.edit')
                <a href="{{ route('purchase-requisitions.edit', $purchaseRequisition) }}"
                   class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                    {{ __('Edit') }}
                </a>
                @endcan
            @endif

            @if (in_array($purchaseRequisition->status, ['pending', 'approved']))
                @can('purchase-requisitions.edit')
                <form method="POST" action="{{ route('purchase-requisitions.cancel', $purchaseRequisition) }}"
                      onsubmit="return confirm('{{ __('Cancel :no?', ['no' => $purchaseRequisition->requisition_no]) }}');">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 rounded-lg border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-100">
                        {{ __('Cancel Requisition') }}
                    </button>
                </form>
                @endcan
            @endif

            @if ($purchaseRequisition->status === 'approved' && ! $purchaseRequisition->purchase)
                @can('sourcing.create')
                <a href="{{ route('purchases.create', ['from_requisition' => $purchaseRequisition->id]) }}"
                   class="inline-flex items-center gap-2 rounded-lg bg-accent-600 px-4 py-2 text-sm font-semibold text-white hover:bg-accent-700 sm:ml-auto">
                    {{ __('Convert to Purchase Order') }}
                </a>
                @endcan
            @endif

            @if ($purchaseRequisition->purchase)
                <a href="{{ route('purchases.show', $purchaseRequisition->purchase) }}"
                   class="inline-flex items-center gap-2 rounded-lg border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-100 sm:ml-auto">
                    {{ __('View Purchase Order') }} {{ $purchaseRequisition->purchase->purchase_no }}
                </a>
            @endif
        </div>

        @can('purchase-requisitions.approve')
        <form id="reject-form" method="POST" action="{{ route('purchase-requisitions.reject', $purchaseRequisition) }}" class="hidden mt-4 border-t border-slate-100 pt-4">
            @csrf
            <x-input-label for="rejection_reason" :value="__('Rejection reason (optional)')" />
            <textarea id="rejection_reason" name="rejection_reason" rows="2"
                      class="mt-1 block w-full max-w-xl rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500"></textarea>
            <button type="submit" class="mt-2 inline-flex items-center gap-2 rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700">
                {{ __('Confirm Rejection') }}
            </button>
        </form>
        @endcan
    </div>

    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full min-w-[600px] text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <th class="px-5 py-3 font-semibold">{{ __('Item') }}</th>
                    <th class="px-5 py-3 font-semibold text-right">{{ __('Quantity') }}</th>
                    <th class="px-5 py-3 font-semibold text-right">{{ __('Est. Unit Cost') }}</th>
                    <th class="px-5 py-3 font-semibold text-right">{{ __('Est. Subtotal') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($purchaseRequisition->items as $item)
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-3">
                        <span class="block font-semibold text-slate-800">
                            {{ $item->product->name }}{{ $item->productVariant ? ' — '.$item->productVariant->label : '' }}
                        </span>
                        <span class="block text-xs text-slate-400">{{ $item->productVariant->sku ?? $item->product->sku }}</span>
                    </td>
                    <td class="px-5 py-3 text-right tabular-nums text-slate-500">{{ rtrim(rtrim(number_format($item->quantity, 4), '0'), '.') ?: '0' }} {{ $item->product->stockUnit?->short_name }}</td>
                    <td class="px-5 py-3 text-right tabular-nums text-slate-500">{{ number_format($item->estimated_unit_cost, 2) }}</td>
                    <td class="px-5 py-3 text-right tabular-nums font-semibold text-slate-900">{{ number_format($item->quantity * $item->estimated_unit_cost, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>
</x-app-layout>
