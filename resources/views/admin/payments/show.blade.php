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

    <div class="mt-5">
        <a href="{{ route('payments.index') }}" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50">{{ __('Back to Payments') }}</a>
    </div>
</x-app-layout>
