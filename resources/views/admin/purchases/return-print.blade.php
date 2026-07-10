<x-app-layout>
    <x-slot name="title">{{ __('Print') }} — {{ $purchaseReturn->return_no }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('Purchase Return Note') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ $purchaseReturn->return_no }}</p>
        </div>
    </x-slot>

    <div class="no-print mb-4 flex justify-end gap-3">
        <a href="{{ route('purchases.show', $purchaseReturn->purchase) }}"
           class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">
            {{ __('Back') }}
        </a>
        <button type="button" onclick="window.print()"
                class="inline-flex items-center gap-2 rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
            {{ __('Print') }}
        </button>
    </div>

    <div class="print-area voucher-print mx-auto max-w-3xl rounded-2xl bg-white p-8 ring-1 ring-slate-200">
        <div class="h-1.5 -mx-8 -mt-8 mb-6 rounded-t-2xl bg-brand-800"></div>
        <div class="flex items-start justify-between gap-6 border-b-2 border-brand-800 pb-6">
            <div class="flex items-center gap-3">
                @if ($company->logo_url)
                <img src="{{ $company->logo_url }}" alt="{{ $company->name }}" class="h-14 w-14 rounded-lg object-cover">
                @endif
                <div>
                    <h1 class="text-xl font-bold text-brand-800">{{ $company->name }}</h1>
                    @if ($company->address)<p class="text-xs text-slate-500">{{ $company->address }}</p>@endif
                    @if ($company->phone || $company->email)
                    <p class="text-xs text-slate-500">{{ collect([$company->phone, $company->email])->filter()->implode(' · ') }}</p>
                    @endif
                    @if ($company->vat_registration_no || $company->bin_no)
                    <p class="text-xs text-slate-500">
                        @if ($company->vat_registration_no) {{ __('VAT') }}: {{ $company->vat_registration_no }} @endif
                        @if ($company->bin_no) &nbsp;&nbsp; {{ __('BIN') }}: {{ $company->bin_no }} @endif
                    </p>
                    @endif
                </div>
            </div>
            <div class="text-right">
                <h2 class="text-lg font-bold uppercase tracking-wide text-brand-800">{{ __('Purchase Return Note') }}</h2>
                <p class="text-sm font-semibold text-slate-800">{{ $purchaseReturn->return_no }}</p>
                <p class="text-xs text-slate-500">{{ $purchaseReturn->return_date->format('d M, Y') }}</p>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-2 gap-6 text-sm">
            <div>
                <p class="mb-1 text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ __('Supplier') }}</p>
                <p class="font-semibold text-slate-800">{{ $purchaseReturn->purchase->party->name }}</p>
                @if ($purchaseReturn->purchase->party->phone)<p class="text-slate-600">{{ $purchaseReturn->purchase->party->phone }}</p>@endif
                @if ($purchaseReturn->purchase->party->address)<p class="text-slate-500">{{ $purchaseReturn->purchase->party->address }}</p>@endif
            </div>
            <div class="text-right">
                <p class="mb-1 text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ __('Returned From') }}</p>
                <p class="font-semibold text-slate-800">{{ $purchaseReturn->purchase->site->name }}</p>
                <p class="text-slate-500">{{ __('Against PO') }}: {{ $purchaseReturn->purchase->purchase_no }}</p>
            </div>
        </div>

        <table class="mt-6 w-full text-sm">
            <thead>
                <tr class="border-b-2 border-brand-800 text-left">
                    <th class="py-2 pr-2">#</th>
                    <th class="py-2 pr-2">{{ __('Item') }}</th>
                    <th class="py-2 pr-2 text-right">{{ __('Qty Returned') }}</th>
                    <th class="py-2 pr-2 text-right">{{ __('Unit Cost') }}</th>
                    <th class="py-2 text-right">{{ __('Value') }}</th>
                </tr>
            </thead>
            <tbody>
                @php $totalValue = 0; $discountRate = $purchaseReturn->purchase->discountRate(); @endphp
                @foreach ($purchaseReturn->items as $i => $ri)
                @php
                    $netUnitCost = round($ri->purchaseItem->unit_cost * (1 - $discountRate), 4);
                    $lineValue = $ri->quantity * $netUnitCost;
                    $totalValue += $lineValue;
                @endphp
                <tr class="border-b border-slate-200">
                    <td class="py-2 pr-2 align-top">{{ $i + 1 }}</td>
                    <td class="py-2 pr-2 align-top">
                        {{ $ri->purchaseItem->product->name }}{{ $ri->purchaseItem->productVariant ? ' — '.$ri->purchaseItem->productVariant->label : '' }}
                        @if ($ri->batch_no || $ri->expiry_date || $ri->serial_no)
                        <span class="block text-xs text-slate-400">{{ collect([$ri->batch_no, $ri->expiry_date?->format('d M, Y'), $ri->serial_no])->filter()->implode(' · ') }}</span>
                        @endif
                    </td>
                    <td class="py-2 pr-2 text-right align-top">
                        {{ rtrim(rtrim(number_format($ri->quantity, 4), '0'), '.') ?: '0' }} {{ $ri->purchaseItem->product->stockUnit?->short_name }}
                    </td>
                    <td class="py-2 pr-2 text-right align-top">{{ number_format($netUnitCost, 2) }}</td>
                    <td class="py-2 text-right align-top">{{ number_format($lineValue, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-3 text-right text-sm font-bold text-slate-800">
            {{ __('Total') }}: {{ number_format($totalValue, 2) }}
        </div>

        @if ($purchaseReturn->note)
        <div class="mt-6 text-sm">
            <p class="mb-1 text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ __('Note') }}</p>
            <p class="text-slate-700">{{ $purchaseReturn->note }}</p>
        </div>
        @endif

        <div class="mt-16 print-footer">
            <div class="grid grid-cols-2 gap-6 text-sm">
                <div class="border-t border-slate-400 pt-2 text-slate-600">
                    {{ __('Returned By') }} ({{ $purchaseReturn->returnedBy->name ?? '—' }})
                </div>
                <div class="border-t border-slate-400 pt-2 text-right text-slate-600">
                    {{ __('Supplier Signature') }}
                </div>
            </div>

            <div class="h-1.5 -mx-8 -mb-8 mt-8 rounded-b-2xl bg-brand-800"></div>
        </div>
    </div>

    <style>
        @media print {
            body { background: #fff !important; }
            body * { visibility: hidden; }
            .print-area, .print-area * { visibility: visible; }
            .print-area {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                max-width: 100%;
                min-height: calc(297mm - 24mm);
                display: flex;
                flex-direction: column;
                box-shadow: none;
                border: none;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .print-footer {
                margin-top: auto !important;
                page-break-inside: avoid;
                break-inside: avoid;
            }
            @page { size: A4; margin: 12mm; }
        }
    </style>

    <script>
        window.addEventListener('load', function () { window.print(); });
    </script>
</x-app-layout>
