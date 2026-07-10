<x-app-layout>
    <x-slot name="title">{{ __('Party Ledger') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('Ledger') }} — {{ $party->name }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ __('Every accounting transaction posted against this party') }}</p>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Receivable (they owe us)') }}</p>
            <p class="mt-1 text-2xl font-bold {{ $receivable > 0 ? 'text-rose-600' : 'text-slate-800' }}">{{ number_format($receivable, 2) }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Payable (we owe them)') }}</p>
            <p class="mt-1 text-2xl font-bold {{ $payable > 0 ? 'text-amber-600' : 'text-slate-800' }}">{{ number_format($payable, 2) }}</p>
        </div>
    </div>

    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full min-w-[760px] text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <th class="px-5 py-3 font-semibold">{{ __('Date') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Voucher') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Account') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Narration') }}</th>
                    <th class="px-5 py-3 font-semibold text-right">{{ __('Debit') }}</th>
                    <th class="px-5 py-3 font-semibold text-right">{{ __('Credit') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($lines as $line)
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-3 text-slate-600">{{ $line->transaction->date->format('d M Y') }}</td>
                    <td class="px-5 py-3 font-mono text-xs text-slate-500">{{ $line->transaction->voucher_no }}</td>
                    <td class="px-5 py-3 text-slate-600">{{ $line->account->name }}</td>
                    <td class="px-5 py-3 text-slate-600">{{ $line->transaction->narration }}</td>
                    <td class="px-5 py-3 text-right text-slate-800">{{ $line->debit > 0 ? number_format($line->debit, 2) : '—' }}</td>
                    <td class="px-5 py-3 text-right text-slate-800">{{ $line->credit > 0 ? number_format($line->credit, 2) : '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-5 py-10 text-center text-slate-400">{{ __('No ledger transactions yet.') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    <div class="mt-5">
        <a href="{{ route('parties.index') }}" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50">{{ __('Back to Parties') }}</a>
    </div>
</x-app-layout>
