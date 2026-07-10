<x-app-layout>
    <x-slot name="title">{{ $payment->payment_no }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ $payment->payment_no }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ __('Payment detail') }}</p>
        </div>
    </x-slot>

    @include('admin.vouchers._show', [
        'accent' => 'rose',
        'stampLabel' => __('Paid'),
        'party' => $payment->party,
        'partyRoleLabel' => __('Supplier'),
        'accountLabel' => __('Paid From'),
        'account' => $payment->account,
        'amount' => $payment->amount,
        'currencyLabel' => \App\Models\CompanySetting::current()->currency_label,
        'date' => $payment->payment_date,
        'referenceNo' => $payment->reference_no,
        'creatorName' => $payment->creator->name ?? '—',
        'note' => $payment->note,
        'ledgerTransaction' => $payment->ledgerTransaction,
        'balanceLabel' => __('Current Payable'),
        'currentBalance' => $payment->party->payableBalance(),
        'indexUrl' => route('payments.index'),
        'backLabel' => __('Back to Payments'),
        'printUrl' => route('payments.print', $payment),
    ])
</x-app-layout>
