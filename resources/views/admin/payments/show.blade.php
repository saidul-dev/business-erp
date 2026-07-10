<x-app-layout>
    <x-slot name="title">{{ $payment->payment_no }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ $payment->payment_no }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ __('Payment detail') }}</p>
        </div>
    </x-slot>

    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 p-6 max-w-2xl">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 text-sm">
            <div>
                <span class="block text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ __('Supplier') }}</span>
                <a href="{{ route('parties.ledger', $payment->party) }}" class="block font-semibold text-accent-600 hover:text-accent-800">{{ $payment->party->name }}</a>
            </div>
            <div>
                <span class="block text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ __('Paid From') }}</span>
                <span class="block text-slate-700">{{ $payment->account->name }}</span>
            </div>
            <div>
                <span class="block text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ __('Amount') }}</span>
                <span class="block text-xl font-bold text-brand-900">{{ number_format($payment->amount, 2) }}</span>
            </div>
            <div>
                <span class="block text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ __('Date') }}</span>
                <span class="block text-slate-700">{{ $payment->payment_date->format('d M, Y') }}</span>
            </div>
            @if ($payment->reference_no)
            <div>
                <span class="block text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ __('Reference No.') }}</span>
                <span class="block text-slate-700">{{ $payment->reference_no }}</span>
            </div>
            @endif
            <div>
                <span class="block text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ __('Recorded By') }}</span>
                <span class="block text-slate-700">{{ $payment->creator->name ?? '—' }}</span>
            </div>
            @if ($payment->note)
            <div class="sm:col-span-2">
                <span class="block text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ __('Note') }}</span>
                <span class="block text-slate-700">{{ $payment->note }}</span>
            </div>
            @endif
        </div>
    </div>

    <div class="mt-5 flex items-center gap-3">
        <a href="{{ route('payments.index') }}" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50">{{ __('Back to Payments') }}</a>
        <a href="{{ route('payments.print', $payment) }}" target="_blank"
           class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z"/></svg>
            {{ __('Print') }}
        </a>
    </div>
</x-app-layout>
