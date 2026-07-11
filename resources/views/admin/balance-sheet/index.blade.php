<x-app-layout>
    <x-slot name="title">{{ __('Balance Sheet') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('Balance Sheet') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ __('What the business owns, owes, and is worth as of a date') }}</p>
        </div>
    </x-slot>

    <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 mb-6">
        <form method="GET" action="{{ route('balance-sheet.index') }}" class="flex flex-wrap items-end gap-4">
            <div class="w-48 shrink-0">
                <x-input-label for="as_of" :value="__('As Of')" />
                <x-text-input id="as_of" name="as_of" type="date" class="mt-1 block w-full" :value="$asOf" />
            </div>
            <button type="submit" class="rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                {{ __('Apply') }}
            </button>
        </form>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        {{-- Assets --}}
        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 overflow-hidden">
            <div class="border-b border-slate-100 bg-slate-50 px-5 py-3">
                <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Assets') }}</h3>
            </div>
            <table class="w-full text-sm">
                <tbody class="divide-y divide-slate-100">
                    @forelse ($assets as $row)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-2.5 text-slate-700">{{ $row->account->name }}</td>
                        <td class="px-5 py-2.5 text-right tabular-nums text-slate-700">{{ number_format($row->amount, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="2" class="px-5 py-6 text-center text-slate-400">{{ __('Nothing yet.') }}</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-brand-800 bg-slate-50">
                        <td class="px-5 py-3 font-bold text-slate-800">{{ __('Total Assets') }}</td>
                        <td class="px-5 py-3 text-right font-bold tabular-nums text-slate-800">{{ number_format($totalAssets, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Liabilities + Equity --}}
        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 overflow-hidden">
            <div class="border-b border-slate-100 bg-slate-50 px-5 py-3">
                <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Liabilities') }}</h3>
            </div>
            <table class="w-full text-sm">
                <tbody class="divide-y divide-slate-100">
                    @forelse ($liabilities as $row)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-2.5 text-slate-700">{{ $row->account->name }}</td>
                        <td class="px-5 py-2.5 text-right tabular-nums text-slate-700">{{ number_format($row->amount, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="2" class="px-5 py-6 text-center text-slate-400">{{ __('Nothing yet.') }}</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="border-t border-slate-100 bg-slate-50">
                        <td class="px-5 py-2.5 font-semibold text-slate-600">{{ __('Total Liabilities') }}</td>
                        <td class="px-5 py-2.5 text-right font-semibold tabular-nums text-slate-600">{{ number_format($totalLiabilities, 2) }}</td>
                    </tr>
                </tfoot>
            </table>

            <div class="border-t border-slate-100 bg-slate-50 px-5 py-3">
                <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Equity') }}</h3>
            </div>
            <table class="w-full text-sm">
                <tbody class="divide-y divide-slate-100">
                    @forelse ($equity as $row)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-2.5 text-slate-700">{{ $row->account->name }}</td>
                        <td class="px-5 py-2.5 text-right tabular-nums text-slate-700">{{ number_format($row->amount, 2) }}</td>
                    </tr>
                    @empty
                    @endforelse
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-2.5 text-slate-700">
                            {{ __('Net Profit / (Loss)') }}
                            <span class="block text-xs text-slate-400">{{ __('Income minus Expense — not yet closed into Capital') }}</span>
                        </td>
                        <td class="px-5 py-2.5 text-right tabular-nums {{ $netProfit >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">{{ number_format($netProfit, 2) }}</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="border-t border-slate-100 bg-slate-50">
                        <td class="px-5 py-2.5 font-semibold text-slate-600">{{ __('Total Equity') }}</td>
                        <td class="px-5 py-2.5 text-right font-semibold tabular-nums text-slate-600">{{ number_format($totalEquity, 2) }}</td>
                    </tr>
                    <tr class="border-t-2 border-brand-800 bg-slate-50">
                        <td class="px-5 py-3 font-bold text-slate-800">{{ __('Total Liabilities + Equity') }}</td>
                        <td class="px-5 py-3 text-right font-bold tabular-nums text-slate-800">{{ number_format($totalLiabilities + $totalEquity, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="mt-4 rounded-2xl bg-white px-5 py-3 shadow-sm ring-1 ring-slate-200">
        @if (round($totalAssets, 2) === round($totalLiabilities + $totalEquity, 2))
        <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-600">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
            {{ __('Balanced — Assets = Liabilities + Equity') }}
        </span>
        @else
        <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-rose-600">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
            {{ __('Not balanced — check recent postings') }}
        </span>
        @endif
    </div>
</x-app-layout>
