<x-app-layout>
    <x-slot name="title">{{ __('Receipt') }} — {{ $sale->sale_no }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('Receipt') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ $sale->sale_no }}</p>
        </div>
    </x-slot>

    <div class="no-print mb-4 flex justify-end gap-3">
        <a href="{{ route('pos.index') }}"
           class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">
            {{ __('Back to POS') }}
        </a>
        <button type="button" onclick="window.print()"
                class="inline-flex items-center gap-2 rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
            {{ __('Print') }}
        </button>
    </div>

    @php
        $collection = $sale->collections->first();
        $paperWidth = $company->pos_receipt_paper_width ?: '80mm';
    @endphp

    <div class="print-area mx-auto bg-white p-4 ring-1 ring-slate-200 font-mono text-xs" style="max-width: {{ $paperWidth }};">
        <div class="text-center">
            @if ($company->logo_url)
            <img src="{{ $company->logo_url }}" alt="{{ $company->name }}" class="mx-auto h-10 w-10 object-cover mb-1">
            @endif
            <p class="font-bold text-sm">{{ $company->name }}</p>
            @if ($company->address)<p>{{ $company->address }}</p>@endif
            @if ($company->phone)<p>{{ __('Tel') }}: {{ $company->phone }}</p>@endif
        </div>

        <div class="my-2 border-t border-dashed border-slate-400"></div>

        <div class="flex justify-between"><span>{{ $sale->sale_no }}</span><span>{{ $sale->order_date->format('d M, Y') }}</span></div>
        <div class="flex justify-between"><span>{{ __('Cashier') }}: {{ $sale->creator->name ?? '—' }}</span><span>{{ now()->format('h:i A') }}</span></div>
        <div>{{ __('Customer') }}: {{ $sale->party->name }}</div>

        <div class="my-2 border-t border-dashed border-slate-400"></div>

        @foreach ($sale->items as $item)
        <div>{{ $item->product->name }}{{ $item->productVariant ? ' — '.$item->productVariant->label : '' }}</div>
        <div class="flex justify-between">
            <span>{{ rtrim(rtrim(number_format($item->quantity, 4), '0'), '.') ?: '0' }} x {{ number_format($item->unit_price, 2) }}</span>
            <span>{{ number_format($item->subtotal, 2) }}</span>
        </div>
        @endforeach

        <div class="my-2 border-t border-dashed border-slate-400"></div>

        <div class="flex justify-between"><span>{{ __('Subtotal') }}</span><span>{{ number_format($sale->subtotal_amount, 2) }}</span></div>
        @if ($sale->discount_amount > 0)
        <div class="flex justify-between"><span>{{ __('Discount') }}</span><span>-{{ number_format($sale->discount_amount, 2) }}</span></div>
        @endif
        <div class="flex justify-between font-bold text-sm"><span>{{ __('Total') }}</span><span>{{ number_format($sale->total_amount, 2) }}</span></div>

        <div class="my-2 border-t border-dashed border-slate-400"></div>

        @if ($collection)
        <div class="flex justify-between"><span>{{ __('Paid via') }} {{ $collection->account->name }}</span><span>{{ number_format($collection->amount, 2) }}</span></div>
        @endif

        <p class="mt-2 text-[11px]">{{ \App\Support\AmountInWords::convert((float) $sale->total_amount, $company->currency_label) }}</p>

        <div class="my-3 border-t border-dashed border-slate-400"></div>

        <p class="text-center">{{ __('Thank you for your purchase!') }}</p>
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
                box-shadow: none;
                border: none;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            @page { size: {{ $paperWidth }} auto; margin: 0; }
        }
    </style>

    <script>
        window.addEventListener('load', function () { window.print(); });
    </script>
</x-app-layout>
