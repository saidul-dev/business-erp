<x-app-layout>
    <x-slot name="title">{{ __('Profit & Loss') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('Profit & Loss') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ __('Income minus Expense for a period') }}</p>
        </div>
    </x-slot>

    <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 mb-6">
        <form method="GET" action="{{ route('profit-loss.index') }}" class="flex flex-wrap items-end gap-4">
            <div class="w-40 shrink-0">
                <x-input-label for="from" :value="__('From')" />
                <x-text-input id="from" name="from" type="date" class="mt-1 block w-full" :value="$from" />
            </div>
            <div class="w-40 shrink-0">
                <x-input-label for="to" :value="__('To')" />
                <x-text-input id="to" name="to" type="date" class="mt-1 block w-full" :value="$to" />
            </div>
            <div class="w-56 shrink-0">
                <x-input-label for="branch_id" :value="__('Branch')" />
                <div class="mt-1">
                    <x-searchable-select name="branch_id" :options="$branches" :selected="$branchId" placeholder="{{ __('All branches') }}" />
                </div>
            </div>
            <button type="submit" class="rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                {{ __('Apply') }}
            </button>
            @if ($branchId)
            <a href="{{ route('profit-loss.index', ['from' => $from, 'to' => $to]) }}" class="text-sm font-semibold text-slate-500 hover:text-slate-700">{{ __('Clear branch') }}</a>
            @endif
        </form>
    </div>

    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 overflow-hidden">
        <table class="w-full text-sm">
            <colgroup>
                <col>
                <col class="w-56">
            </colgroup>
            <tbody class="divide-y divide-slate-100">
                {{-- Revenue --}}
                <tr class="bg-slate-50">
                    <td colspan="2" class="px-5 py-2 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Revenue') }}</td>
                </tr>
                @forelse ($revenue as $row)
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-2.5 text-slate-700">{{ $row->account->name }}</td>
                    <td class="px-5 py-2.5 text-right tabular-nums text-slate-700">{{ number_format($row->amount, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="2" class="px-5 py-4 text-center text-slate-400">{{ __('No sales in this period.') }}</td></tr>
                @endforelse
                @foreach ($contraRevenue as $row)
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-2.5 text-slate-500">({{ $row->account->name }})</td>
                    <td class="px-5 py-2.5 text-right tabular-nums text-slate-500">({{ number_format($row->amount, 2) }})</td>
                </tr>
                @endforeach
                <tr class="border-t border-slate-100">
                    <td class="px-5 py-2.5 font-semibold text-slate-600">{{ __('Net Sales Revenue') }}</td>
                    <td class="px-5 py-2.5 text-right font-semibold tabular-nums text-slate-600">{{ number_format($netRevenue, 2) }}</td>
                </tr>

                {{-- COGS --}}
                <tr class="bg-slate-50">
                    <td colspan="2" class="px-5 py-2 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Cost of Goods Sold') }}</td>
                </tr>
                @forelse ($cogs as $row)
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-2.5 text-slate-700">{{ $row->account->name }}</td>
                    <td class="px-5 py-2.5 text-right tabular-nums text-slate-700">({{ number_format($row->amount, 2) }})</td>
                </tr>
                @empty
                <tr><td colspan="2" class="px-5 py-4 text-center text-slate-400">{{ __('Nothing yet.') }}</td></tr>
                @endforelse
                <tr class="border-t-2 border-brand-800 bg-slate-50">
                    <td class="px-5 py-3 font-bold text-slate-800">{{ __('Gross Profit') }}</td>
                    <td class="px-5 py-3 text-right font-bold tabular-nums text-slate-800">{{ number_format($grossProfit, 2) }}</td>
                </tr>

                {{-- Operating Expenses --}}
                <tr class="bg-slate-50">
                    <td colspan="2" class="px-5 py-2 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Operating Expenses') }}</td>
                </tr>
                @forelse ($operatingExpenses as $row)
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-2.5 text-slate-700">{{ $row->account->name }}</td>
                    <td class="px-5 py-2.5 text-right tabular-nums text-slate-700">({{ number_format($row->amount, 2) }})</td>
                </tr>
                @empty
                <tr><td colspan="2" class="px-5 py-4 text-center text-slate-400">{{ __('No expenses in this period.') }}</td></tr>
                @endforelse
                <tr class="border-t border-slate-100">
                    <td class="px-5 py-2.5 font-semibold text-slate-600">{{ __('Total Operating Expenses') }}</td>
                    <td class="px-5 py-2.5 text-right font-semibold tabular-nums text-slate-600">({{ number_format($totalOperatingExpenses, 2) }})</td>
                </tr>

                {{-- Other Income --}}
                <tr class="bg-slate-50">
                    <td colspan="2" class="px-5 py-2 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Other Income') }}</td>
                </tr>
                @forelse ($otherIncome as $row)
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-2.5 text-slate-700">{{ $row->account->name }}</td>
                    <td class="px-5 py-2.5 text-right tabular-nums text-slate-700">{{ number_format($row->amount, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="2" class="px-5 py-4 text-center text-slate-400">{{ __('None in this period.') }}</td></tr>
                @endforelse
                <tr class="border-t border-slate-100">
                    <td class="px-5 py-2.5 font-semibold text-slate-600">{{ __('Total Other Income') }}</td>
                    <td class="px-5 py-2.5 text-right font-semibold tabular-nums text-slate-600">{{ number_format($totalOtherIncome, 2) }}</td>
                </tr>
            </tbody>
            <tfoot>
                <tr class="border-t-2 border-brand-800 {{ $netProfit >= 0 ? 'bg-emerald-50' : 'bg-rose-50' }}">
                    <td class="px-5 py-3 font-bold {{ $netProfit >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">{{ __('Net Profit / (Loss)') }}</td>
                    <td class="px-5 py-3 text-right font-bold tabular-nums {{ $netProfit >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">{{ number_format($netProfit, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</x-app-layout>
