<x-app-layout>
    <x-slot name="title">{{ __('Account Ledger') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('Ledger') }} — {{ $bankAccount->name }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ __('Every transaction posted against this account, oldest first') }}</p>
        </div>
    </x-slot>

    <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 mb-6 max-w-xs">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Current Balance') }}</p>
        <p class="mt-1 text-2xl font-bold text-brand-900">{{ number_format($balance, 2) }}</p>
    </div>

    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full min-w-[860px] text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <th class="px-5 py-3 font-semibold">{{ __('Date') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Voucher') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Narration') }}</th>
                    <th class="px-5 py-3 font-semibold text-right">{{ __('Debit') }}</th>
                    <th class="px-5 py-3 font-semibold text-right">{{ __('Credit') }}</th>
                    <th class="px-5 py-3 font-semibold text-right">{{ __('Balance') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($lines as $line)
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-3 text-slate-600">{{ $line->transaction->date->format('d M, Y') }}</td>
                    <td class="px-5 py-3 font-mono text-xs text-slate-500">{{ $line->transaction->voucher_no }}</td>
                    <td class="px-5 py-3 text-slate-600">{{ $line->transaction->narration }}</td>
                    <td class="px-5 py-3 text-right text-slate-800">{{ $line->debit > 0 ? number_format($line->debit, 2) : '—' }}</td>
                    <td class="px-5 py-3 text-right text-slate-800">{{ $line->credit > 0 ? number_format($line->credit, 2) : '—' }}</td>
                    <td class="px-5 py-3 text-right font-semibold text-slate-800">{{ number_format($line->running_balance, 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-5 py-10 text-center text-slate-400">{{ __('No transactions yet.') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    <div class="mt-5">
        <a href="{{ route('bank-accounts.index') }}" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50">{{ __('Back to Bank Accounts') }}</a>
    </div>
</x-app-layout>
