<x-app-layout>
    <x-slot name="title">{{ __('Trial Balance') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('Trial Balance') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ __('Every account\'s cumulative balance as of a date — total Debit must equal total Credit') }}</p>
        </div>
    </x-slot>

    <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 mb-6">
        <form method="GET" action="{{ route('trial-balance.index') }}" class="flex flex-wrap items-end gap-4">
            <div class="w-48 shrink-0">
                <x-input-label for="as_of" :value="__('As Of')" />
                <x-text-input id="as_of" name="as_of" type="date" class="mt-1 block w-full" :value="$asOf" />
            </div>
            <button type="submit" class="rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                {{ __('Apply') }}
            </button>
        </form>
    </div>

    @php
        $groupLabels = [
            'cash_bank' => __('Cash & Bank'),
            'party' => __('Party Control Accounts'),
            'inventory' => __('Inventory'),
            'income_expense' => __('Income & Expense'),
            'equity_adjustment' => __('Equity & Adjustment'),
        ];
    @endphp

    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full min-w-[640px] text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <th class="px-5 py-3 font-semibold">{{ __('Account') }}</th>
                    <th class="px-5 py-3 font-semibold text-right">{{ __('Debit') }}</th>
                    <th class="px-5 py-3 font-semibold text-right">{{ __('Credit') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($groups as $group)
                    @php $groupRows = $rows->get($group, collect()); @endphp
                    @if ($groupRows->isNotEmpty())
                    <tr class="bg-slate-50/60">
                        <td colspan="3" class="px-5 py-2 text-xs font-semibold uppercase tracking-wide text-slate-400">{{ $groupLabels[$group] ?? $group }}</td>
                    </tr>
                    @foreach ($groupRows as $row)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-2.5 text-slate-700">{{ $row->account->name }}</td>
                        <td class="px-5 py-2.5 text-right tabular-nums text-slate-700">{{ $row->debit > 0 ? number_format($row->debit, 2) : '—' }}</td>
                        <td class="px-5 py-2.5 text-right tabular-nums text-slate-700">{{ $row->credit > 0 ? number_format($row->credit, 2) : '—' }}</td>
                    </tr>
                    @endforeach
                    @endif
                @empty
                @endforelse
            </tbody>
            <tfoot>
                <tr class="border-t-2 border-brand-800 bg-slate-50">
                    <td class="px-5 py-3 font-bold text-slate-800">{{ __('Total') }}</td>
                    <td class="px-5 py-3 text-right font-bold tabular-nums text-slate-800">{{ number_format($totalDebit, 2) }}</td>
                    <td class="px-5 py-3 text-right font-bold tabular-nums text-slate-800">{{ number_format($totalCredit, 2) }}</td>
                </tr>
            </tfoot>
        </table>
        </div>

        <div class="border-t border-slate-100 px-5 py-3">
            @if (round($totalDebit, 2) === round($totalCredit, 2))
            <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-600">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                {{ __('Balanced') }}
            </span>
            @else
            <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-rose-600">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
                {{ __('Not balanced — check recent postings') }}
            </span>
            @endif
        </div>
    </div>
</x-app-layout>
