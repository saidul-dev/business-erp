@php
    $palette = match ($accent) {
        'rose' => ['solid' => '#e11d48', 'soft' => '#fff1f2', 'border' => '#fecdd3', 'ink' => '#9f1239'],
        'emerald' => ['solid' => '#059669', 'soft' => '#ecfdf5', 'border' => '#a7f3d0', 'ink' => '#065f46'],
        default => ['solid' => '#0b2a6b', 'soft' => '#eef4fd', 'border' => '#b3ccf4', 'ink' => '#081c4e'],
    };
    $amountInWords = \App\Support\AmountInWords::convert((float) $amount, $company->currency_label);
    $stampFontSize = mb_strlen($stampLabel) > 5 ? '0.8rem' : '1.05rem';
    $stampLetterSpacing = mb_strlen($stampLabel) > 5 ? '0.06em' : '0.12em';
@endphp

<div class="print-area voucher-print mx-auto max-w-3xl rounded-2xl bg-white p-8 ring-1 ring-slate-200">
    <div class="h-1.5 -mx-8 -mt-8 mb-6 rounded-t-2xl" style="background:{{ $palette['solid'] }}"></div>

    <div class="flex items-start justify-between gap-6 border-b-2 pb-6" style="border-color:{{ $palette['solid'] }}">
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
            <h2 class="text-lg font-bold uppercase tracking-wide" style="color:{{ $palette['solid'] }}">{{ $voucherTitle }}</h2>
            <p class="text-sm font-semibold text-slate-800">{{ $voucherNo }}</p>
            <p class="text-xs text-slate-500">{{ $voucherDate->format('d M, Y') }}</p>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-2 gap-6 text-sm">
        <div>
            <p class="mb-1 text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ $partyLabel }}</p>
            <p class="font-semibold text-slate-800">{{ $party->name }}</p>
            @if ($party->phone)<p class="text-slate-600">{{ $party->phone }}</p>@endif
            @if ($party->address)<p class="text-slate-500">{{ $party->address }}</p>@endif
        </div>
        <div class="text-right">
            <p class="mb-1 text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ $accountLabel }}</p>
            <p class="font-semibold text-slate-800">{{ $account->name }}</p>
            @if ($referenceNo)
            <p class="text-slate-500">{{ __('Ref.') }}: {{ $referenceNo }}</p>
            @endif
        </div>
    </div>

    <div class="mt-6 rounded-xl border-2 px-6 py-5" style="background:{{ $palette['soft'] }}; border-color:{{ $palette['border'] }}">
        <div class="flex items-baseline justify-between gap-4">
            <span class="text-[11px] font-semibold uppercase tracking-wide" style="color:{{ $palette['ink'] }}">{{ __('Amount') }}</span>
            <span class="font-mono text-3xl font-black tabular-nums" style="color:{{ $palette['ink'] }}">{{ number_format($amount, 2) }}</span>
        </div>
        <p class="mt-2 border-t pt-2 text-xs italic text-slate-600" style="border-color:{{ $palette['border'] }}">
            {{ __('In Words') }}: {{ $amountInWords }}
        </p>
    </div>

    @if ($note)
    <div class="mt-6 text-sm">
        <p class="mb-1 text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ __('Particulars') }}</p>
        <p class="text-slate-700">{{ $note }}</p>
    </div>
    @endif

    <div class="relative mt-16 print-footer">
        <!-- Voucher seal — an authenticating mark, not decoration; ink-stamp
             feel via mix-blend-mode so it reads as pressed onto the paper,
             placed where a real stamp lands: in the blank signing space
             above the counter-signature line. -->
        <div class="voucher-stamp" style="color:{{ $palette['solid'] }}">
            <span class="voucher-stamp__ring"></span>
            <span class="voucher-stamp__label" style="font-size:{{ $stampFontSize }}; letter-spacing:{{ $stampLetterSpacing }}">{{ $stampLabel }}</span>
            <span class="voucher-stamp__no">{{ $voucherNo }}</span>
        </div>

        <div class="grid grid-cols-2 gap-6 pt-14 text-sm">
            <div class="border-t border-slate-400 pt-2 text-slate-600">
                {{ $preparedByLabel }} ({{ $preparedByName }})
            </div>
            <div class="border-t border-slate-400 pt-2 text-right text-slate-600">
                {{ $counterSignLabel }}
            </div>
        </div>

        <div class="h-1.5 -mx-8 -mb-8 mt-8 rounded-b-2xl" style="background:{{ $palette['solid'] }}"></div>
    </div>
</div>

<style>
    .voucher-stamp {
        position: absolute;
        top: 0.25rem;
        right: 1.5rem;
        display: grid;
        place-items: center;
        width: 104px;
        height: 104px;
        border-radius: 9999px;
        border: 3px double currentColor;
        transform: rotate(-14deg);
        mix-blend-mode: multiply;
        opacity: 0.85;
        text-align: center;
    }
    .voucher-stamp__ring {
        position: absolute;
        inset: 6px;
        border: 1px solid currentColor;
        border-radius: 9999px;
    }
    .voucher-stamp__label {
        font-size: 1.05rem;
        font-weight: 800;
        letter-spacing: 0.12em;
    }
    .voucher-stamp__no {
        margin-top: 2px;
        font-size: 0.55rem;
        font-weight: 600;
        letter-spacing: 0.06em;
    }

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
            box-shadow: none;
            border: none;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .print-footer {
            page-break-inside: avoid;
            break-inside: avoid;
        }
        @page { margin: 12mm; }
    }
</style>
