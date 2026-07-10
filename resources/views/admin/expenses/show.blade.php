@php
    $amountInWords = \App\Support\AmountInWords::convert((float) $expense->amount, \App\Models\CompanySetting::current()->currency_label);
@endphp
<x-app-layout>
    <x-slot name="title">{{ $expense->expense_no }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ $expense->expense_no }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ __('Expense detail') }}</p>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-[1fr_360px] gap-6 items-start">
        <!-- Main column -->
        <div class="order-2 lg:order-1 space-y-6">
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                <div class="h-1.5 bg-amber-600"></div>
                <div class="p-6">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <span class="block text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ __('Category') }}</span>
                            <span class="block text-lg font-semibold text-slate-800">{{ $expense->category->name }}</span>
                        </div>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 ring-1 ring-amber-200">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                            {{ __('Paid') }}
                        </span>
                    </div>

                    <div class="mt-6 grid grid-cols-1 sm:grid-cols-3 gap-5 text-sm">
                        <div>
                            <span class="block text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ __('Paid From') }}</span>
                            <span class="block font-semibold text-slate-700">{{ $expense->paidFrom->name }}</span>
                        </div>
                        <div>
                            <span class="block text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ __('Date') }}</span>
                            <span class="block text-slate-700">{{ $expense->expense_date->format('d M, Y') }}</span>
                        </div>
                        <div>
                            <span class="block text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ __('Recorded By') }}</span>
                            <span class="block text-slate-700">{{ $expense->creator->name ?? '—' }}</span>
                        </div>
                        @if ($expense->reference_no)
                        <div>
                            <span class="block text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ __('Reference No.') }}</span>
                            <span class="block text-slate-700">{{ $expense->reference_no }}</span>
                        </div>
                        @endif
                    </div>

                    @if ($expense->note)
                    <div class="mt-5 rounded-lg bg-slate-50 px-4 py-3 text-sm text-slate-600 ring-1 ring-slate-100">
                        <span class="block text-[11px] font-semibold uppercase tracking-wide text-slate-400 mb-1">{{ __('Note') }}</span>
                        {{ $expense->note }}
                    </div>
                    @endif
                </div>
            </div>

            @if ($expense->ledgerTransaction)
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                    <h3 class="font-bold text-brand-900">{{ __('Ledger Entry') }}</h3>
                    <span class="text-xs font-semibold text-slate-400">{{ $expense->ledgerTransaction->voucher_no }}</span>
                </div>
                <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                            <th class="px-6 py-2.5 font-semibold">{{ __('Account') }}</th>
                            <th class="px-6 py-2.5 font-semibold text-right">{{ __('Debit') }}</th>
                            <th class="px-6 py-2.5 font-semibold text-right">{{ __('Credit') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($expense->ledgerTransaction->lines as $line)
                        <tr>
                            <td class="px-6 py-2.5 text-slate-700">{{ $line->account->name }}</td>
                            <td class="px-6 py-2.5 text-right tabular-nums text-slate-700">{{ $line->debit > 0 ? number_format($line->debit, 2) : '—' }}</td>
                            <td class="px-6 py-2.5 text-right tabular-nums text-slate-700">{{ $line->credit > 0 ? number_format($line->credit, 2) : '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="order-1 lg:order-2 lg:sticky lg:top-6 space-y-4">
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                <div class="p-5">
                    <span class="text-[11px] font-semibold uppercase tracking-wide text-amber-700">{{ __('Amount') }}</span>
                    <div class="mt-1 font-mono text-3xl font-black tabular-nums text-amber-700">{{ number_format($expense->amount, 2) }}</div>
                    <p class="mt-3 border-t border-slate-100 pt-3 text-xs italic text-slate-500">{{ $amountInWords }}</p>
                </div>
            </div>

            <div class="flex flex-col gap-2">
                <a href="{{ route('expenses.print', $expense) }}" target="_blank"
                   class="inline-flex items-center justify-center gap-2 rounded-lg bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-amber-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z"/></svg>
                    {{ __('Print') }}
                </a>
                <a href="{{ route('expenses.index') }}"
                   class="inline-flex items-center justify-center rounded-lg px-4 py-2.5 text-sm font-semibold text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50">
                    {{ __('Back to Expenses') }}
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
