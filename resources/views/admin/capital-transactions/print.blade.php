@php
    $amountInWords = \App\Support\AmountInWords::convert((float) $transaction->amount, $company->currency_label);
    $isInvestment = $transaction->type === 'investment';
    $voucherTitle = $isInvestment ? __('Owner Investment Voucher') : __('Owner Drawing Voucher');
@endphp
<x-app-layout>
    <x-slot name="title">{{ __('Print') }} — {{ $transaction->transaction_no }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ $voucherTitle }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ $transaction->transaction_no }}</p>
        </div>
    </x-slot>

    <div class="no-print mb-4 flex justify-end gap-3">
        <a href="{{ route('capital-transactions.show', $transaction) }}"
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
                <h2 class="text-lg font-bold uppercase tracking-wide text-brand-800">{{ $voucherTitle }}</h2>
                <p class="text-sm font-semibold text-slate-800">{{ $transaction->transaction_no }}</p>
                <p class="text-xs text-slate-500">{{ $transaction->transaction_date->format('d M, Y') }}</p>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-2 gap-6 text-sm">
            <div>
                <p class="mb-1 text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ __('Owner\'s Equity') }}</p>
                <p class="font-semibold text-slate-800">{{ $isInvestment ? __("Owner's Capital") : __("Owner's Drawings") }}</p>
            </div>
            <div class="text-right">
                <p class="mb-1 text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ $isInvestment ? __('Received Into') : __('Paid From') }}</p>
                <p class="font-semibold text-slate-800">{{ $transaction->account->name }}</p>
                @if ($transaction->reference_no)
                <p class="text-slate-500">{{ __('Ref.') }}: {{ $transaction->reference_no }}</p>
                @endif
            </div>
        </div>

        <div class="mt-6 rounded-xl border-2 {{ $isInvestment ? 'border-emerald-200 bg-emerald-50' : 'border-rose-200 bg-rose-50' }} px-6 py-5">
            <div class="flex items-baseline justify-between gap-4">
                <span class="text-[11px] font-semibold uppercase tracking-wide {{ $isInvestment ? 'text-emerald-800' : 'text-rose-800' }}">{{ __('Amount') }}</span>
                <span class="font-mono text-3xl font-black tabular-nums {{ $isInvestment ? 'text-emerald-800' : 'text-rose-800' }}">{{ number_format($transaction->amount, 2) }}</span>
            </div>
            <p class="mt-2 border-t {{ $isInvestment ? 'border-emerald-200' : 'border-rose-200' }} pt-2 text-xs italic text-slate-600">
                {{ __('In Words') }}: {{ $amountInWords }}
            </p>
        </div>

        @if ($transaction->note)
        <div class="mt-6 text-sm">
            <p class="mb-1 text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ __('Particulars') }}</p>
            <p class="text-slate-700">{{ $transaction->note }}</p>
        </div>
        @endif

        <div class="mt-16 print-footer">
            <div class="grid grid-cols-2 gap-6 text-sm">
                <div class="border-t border-slate-400 pt-2 text-slate-600">
                    {{ __('Prepared By') }} ({{ $transaction->creator->name ?? '—' }})
                </div>
                <div class="border-t border-slate-400 pt-2 text-right text-slate-600">
                    {{ __('Authorized Signatory') }}
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
