<x-app-layout>
    <x-slot name="title">{{ __('Print') }} — {{ $saleQuotation->quotation_no }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('Sale Quotation') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ $saleQuotation->quotation_no }}</p>
        </div>
    </x-slot>

    <div class="no-print mb-4 flex justify-end gap-3">
        <a href="{{ route('sale-quotations.show', $saleQuotation) }}"
           class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">
            {{ __('Back') }}
        </a>
        <button type="button" onclick="window.print()"
                class="inline-flex items-center gap-2 rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
            {{ __('Print') }}
        </button>
    </div>

    <div class="print-area mx-auto max-w-3xl rounded-2xl bg-white p-8 ring-1 ring-slate-200">
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
                </div>
            </div>
            <div class="text-right">
                <h2 class="text-lg font-bold uppercase tracking-wide text-brand-800">{{ __('Sale Quotation') }}</h2>
                <p class="text-sm font-semibold text-slate-800">{{ $saleQuotation->quotation_no }}</p>
                <p class="text-xs text-slate-500">{{ $saleQuotation->quote_date->format('d M, Y') }}</p>
                @if ($saleQuotation->valid_until)
                <p class="text-xs text-slate-500">{{ __('Valid until') }} {{ $saleQuotation->valid_until->format('d M, Y') }}</p>
                @endif
            </div>
        </div>

        <div class="mt-6 grid grid-cols-2 gap-6 text-sm">
            <div>
                <p class="mb-1 text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ __('Customer') }}</p>
                <p class="font-semibold text-slate-800">{{ $saleQuotation->party->name }}</p>
                @if ($saleQuotation->party->phone)<p class="text-slate-600">{{ $saleQuotation->party->phone }}</p>@endif
                @if ($saleQuotation->party->address)<p class="text-slate-500">{{ $saleQuotation->party->address }}</p>@endif
            </div>
            <div class="text-right">
                <p class="mb-1 text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ __('Ship To') }}</p>
                <p class="font-semibold text-slate-800">{{ $saleQuotation->site->name }}</p>
                <p class="text-slate-500">{{ __('Status') }}: {{ ucfirst($saleQuotation->status) }}</p>
            </div>
        </div>

        <table class="mt-6 w-full text-sm">
            <thead>
                <tr class="border-b-2 border-brand-800 text-left">
                    <th class="py-2 pr-2">#</th>
                    <th class="py-2 pr-2">{{ __('Item') }}</th>
                    <th class="py-2 pr-2 text-right">{{ __('Qty') }}</th>
                    <th class="py-2 pr-2 text-right">{{ __('Unit Price') }}</th>
                    <th class="py-2 text-right">{{ __('Subtotal') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($saleQuotation->items as $i => $item)
                <tr class="border-b border-slate-200">
                    <td class="py-2 pr-2 align-top">{{ $i + 1 }}</td>
                    <td class="py-2 pr-2 align-top">
                        {{ $item->product->name }}{{ $item->productVariant ? ' — '.$item->productVariant->label : '' }}
                    </td>
                    <td class="py-2 pr-2 text-right align-top">
                        {{ rtrim(rtrim(number_format($item->quantity, 4), '0'), '.') ?: '0' }} {{ $item->product->stockUnit?->short_name }}
                    </td>
                    <td class="py-2 pr-2 text-right align-top">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="py-2 text-right align-top">{{ number_format($item->subtotal, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-3 text-right text-sm">
            <div class="text-slate-500">{{ __('Subtotal') }}: {{ number_format($saleQuotation->subtotal_amount, 2) }}</div>
            @if ($saleQuotation->discount_amount > 0)
            <div class="text-slate-500">{{ __('Discount') }}: {{ number_format($saleQuotation->discount_amount, 2) }}</div>
            @endif
            <div class="font-bold text-slate-800">{{ __('Total') }}: {{ number_format($saleQuotation->total_amount, 2) }}</div>
        </div>

        @if ($saleQuotation->note)
        <div class="mt-6 text-sm">
            <p class="mb-1 text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ __('Note') }}</p>
            <p class="text-slate-700">{{ $saleQuotation->note }}</p>
        </div>
        @endif

        <div class="mt-16 print-footer">
            <div class="grid grid-cols-2 gap-6 text-sm">
                <div class="border-t border-slate-400 pt-2 text-slate-600">
                    {{ __('Prepared By') }} ({{ $saleQuotation->creator->name ?? '—' }})
                </div>
                <div class="border-t border-slate-400 pt-2 text-right text-slate-600">
                    {{ __('Customer Signature') }}
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
