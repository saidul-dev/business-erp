<x-app-layout>
    <x-slot name="title">{{ __('Investment') }}</x-slot>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-brand-900">{{ __('Investment') }}</h2>
                <p class="text-sm text-slate-500 mt-0.5">{{ __("Owner capital movements — money invested into or drawn out of the business") }}</p>
            </div>
            @can('accounts.create')
            <a href="{{ route('capital-transactions.create') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                {{ __('New Transaction') }}
            </a>
            @endcan
        </div>
    </x-slot>

    <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 mb-6">
        <form method="GET" action="{{ route('capital-transactions.index') }}" class="flex flex-nowrap items-end gap-4 overflow-x-auto">
            <div class="w-48 shrink-0">
                <x-input-label for="type" :value="__('Type')" />
                <select name="type" onchange="this.form.submit()"
                        class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">
                    <option value="">{{ __('All types') }}</option>
                    @foreach (\App\Models\CapitalTransaction::TYPES as $key => $label)
                        <option value="{{ $key }}" @selected($type === $key)>{{ __($label) }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full min-w-[760px] text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <th class="px-5 py-3 font-semibold">{{ __('Transaction') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Type') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Account') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Date') }}</th>
                    <th class="px-5 py-3 font-semibold text-right">{{ __('Amount') }}</th>
                    <th class="px-5 py-3 font-semibold"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($transactions as $transaction)
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-3 font-semibold text-slate-800">{{ $transaction->transaction_no }}</td>
                    <td class="px-5 py-3">
                        @if ($transaction->type === 'investment')
                        <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200">{{ __('Investment') }}</span>
                        @else
                        <span class="inline-flex rounded-full bg-rose-50 px-2.5 py-0.5 text-xs font-semibold text-rose-700 ring-1 ring-rose-200">{{ __('Drawing') }}</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-slate-600">{{ $transaction->account->name }}</td>
                    <td class="px-5 py-3 text-slate-600">{{ $transaction->transaction_date->format('d M, Y') }}</td>
                    <td class="px-5 py-3 text-right font-semibold text-slate-800">{{ number_format($transaction->amount, 2) }}</td>
                    <td class="px-5 py-3 text-right">
                        <a href="{{ route('capital-transactions.show', $transaction) }}" class="font-semibold text-accent-600 hover:text-accent-800">{{ __('View') }}</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-5 py-10 text-center text-slate-400">{{ __('No capital transactions yet.') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>

        @if ($transactions->hasPages())
        <div class="border-t border-slate-100 px-5 py-4">
            {{ $transactions->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
