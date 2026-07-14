<x-app-layout>
    <x-slot name="title">{{ __('Due Report') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('Due Report') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ __('Every party with an outstanding balance — customers who owe us, suppliers we owe') }}</p>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Total Receivable (from Customers)') }}</p>
            <p class="mt-1 text-2xl font-bold text-rose-600">{{ number_format($totalReceivable, 2) }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Total Payable (to Suppliers)') }}</p>
            <p class="mt-1 text-2xl font-bold text-amber-600">{{ number_format($totalPayable, 2) }}</p>
        </div>
    </div>

    <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 mb-6">
        <form method="GET" action="{{ route('due-report.index') }}" class="flex flex-nowrap items-end gap-4 overflow-x-auto">
            <div class="w-64 shrink-0">
                <x-input-label :value="__('Role')" />
                <div class="mt-1 flex h-[38px] w-full items-stretch rounded-lg border border-slate-300 bg-slate-50 p-0.5 text-sm">
                    <a href="{{ request()->fullUrlWithQuery(['role' => null]) }}"
                       class="flex flex-1 items-center justify-center whitespace-nowrap rounded-md px-2.5 text-center font-medium transition-colors {{ ! $role ? 'bg-brand-800 text-white shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">
                        {{ __('All') }}
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['role' => 'customer']) }}"
                       class="flex flex-1 items-center justify-center whitespace-nowrap rounded-md px-2.5 text-center font-medium transition-colors {{ $role === 'customer' ? 'bg-brand-800 text-white shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">
                        {{ __('Customers') }}
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['role' => 'supplier']) }}"
                       class="flex flex-1 items-center justify-center whitespace-nowrap rounded-md px-2.5 text-center font-medium transition-colors {{ $role === 'supplier' ? 'bg-brand-800 text-white shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">
                        {{ __('Suppliers') }}
                    </a>
                </div>
            </div>
            <div class="flex-1 min-w-[200px]">
                <x-input-label for="q" :value="__('Search')" />
                <input type="search" id="q" name="q" value="{{ request('q') }}" placeholder="{{ __('Name or phone…') }}"
                       class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">
            </div>
            <button type="submit" class="shrink-0 rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">{{ __('Filter') }}</button>
        </form>
    </div>

    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full min-w-[720px] text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <th class="px-5 py-3 font-semibold">{{ __('Party') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Contact') }}</th>
                    <th class="px-5 py-3 font-semibold text-right">{{ __('Receivable (Due from them)') }}</th>
                    <th class="px-5 py-3 font-semibold text-right">{{ __('Payable (Due to them)') }}</th>
                    <th class="px-5 py-3 font-semibold text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($rows as $row)
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-3">
                        <span class="block font-semibold text-slate-800">{{ $row->party->name }}</span>
                        <span class="block text-xs text-slate-400">{{ __($row->party->role_label) }}</span>
                    </td>
                    <td class="px-5 py-3 text-slate-600">{{ $row->party->phone }}</td>
                    <td class="px-5 py-3 text-right">
                        @if ($row->receivable > 0)
                            <span class="font-semibold text-rose-600">{{ number_format($row->receivable, 2) }}</span>
                        @else
                            <span class="text-slate-300">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-right">
                        @if ($row->payable > 0)
                            <span class="font-semibold text-amber-600">{{ number_format($row->payable, 2) }}</span>
                        @else
                            <span class="text-slate-300">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-right">
                        @can('parties.view')
                        <a href="{{ route('parties.ledger', $row->party) }}"
                           class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-semibold text-slate-500 ring-1 ring-slate-200 hover:bg-brand-50 hover:text-brand-700 hover:ring-brand-200">
                            {{ __('Ledger') }}
                        </a>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-5 py-10 text-center text-slate-400">{{ __('No outstanding dues.') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</x-app-layout>
