<x-app-layout>
    <x-slot name="title">{{ __('Print') }} — {{ $payment->payment_no }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('Payment Voucher') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ $payment->payment_no }}</p>
        </div>
    </x-slot>

    <div class="no-print mb-4 flex justify-end gap-3">
        <a href="{{ route('payments.show', $payment) }}"
           class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">
            {{ __('Back') }}
        </a>
        <button type="button" onclick="window.print()"
                class="inline-flex items-center gap-2 rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
            {{ __('Print') }}
        </button>
    </div>

    @include('admin.vouchers._voucher', [
        'kind' => 'payment',
        'accent' => 'rose',
        'stampLabel' => __('PAID'),
        'voucherTitle' => __('Payment Voucher'),
        'voucherNo' => $payment->payment_no,
        'voucherDate' => $payment->payment_date,
        'partyLabel' => __('Paid To'),
        'party' => $payment->party,
        'accountLabel' => __('Paid From'),
        'account' => $payment->account,
        'amount' => $payment->amount,
        'referenceNo' => $payment->reference_no,
        'note' => $payment->note,
        'preparedByLabel' => __('Prepared By'),
        'preparedByName' => $payment->creator->name ?? '—',
        'counterSignLabel' => __('Payee Signature'),
        'company' => $company,
    ])
</x-app-layout>
