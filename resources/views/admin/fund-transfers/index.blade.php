<x-app-layout>
    <x-slot name="title">{{ __('Fund Transfers') }}</x-slot>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-brand-900">{{ __('Fund Transfers') }}</h2>
                <p class="text-sm text-slate-500 mt-0.5">{{ __('Money moved between your own cash and bank accounts') }}</p>
            </div>
            @can('accounts.create')
            <a href="{{ route('fund-transfers.create') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                {{ __('New Transfer') }}
            </a>
            @endcan
        </div>
    </x-slot>

    <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 mb-6">
        <form method="GET" action="{{ route('fund-transfers.index') }}" class="flex flex-nowrap items-end gap-4 overflow-x-auto">
            <div class="w-64 shrink-0">
                <x-input-label for="account_id" :value="__('Account')" />
                <div class="mt-1">
                    <x-searchable-select name="account_id" :options="$accounts" :selected="$accountId"
                                          placeholder="{{ __('All accounts') }}" :auto-submit="true" />
                </div>
            </div>
        </form>
    </div>

    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full min-w-[760px] text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <th class="px-5 py-3 font-semibold">{{ __('Transfer') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('From') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('To') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Date') }}</th>
                    <th class="px-5 py-3 font-semibold text-right">{{ __('Amount') }}</th>
                    <th class="px-5 py-3 font-semibold"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($transfers as $transfer)
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-3 font-semibold text-slate-800">{{ $transfer->transfer_no }}</td>
                    <td class="px-5 py-3 text-slate-600">{{ $transfer->fromAccount->name }}</td>
                    <td class="px-5 py-3 text-slate-600">{{ $transfer->toAccount->name }}</td>
                    <td class="px-5 py-3 text-slate-600">{{ $transfer->transfer_date->format('d M, Y') }}</td>
                    <td class="px-5 py-3 text-right font-semibold text-slate-800">{{ number_format($transfer->amount, 2) }}</td>
                    <td class="px-5 py-3 text-right">
                        <a href="{{ route('fund-transfers.show', $transfer) }}" class="font-semibold text-accent-600 hover:text-accent-800">{{ __('View') }}</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-5 py-10 text-center text-slate-400">{{ __('No transfers yet.') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>

        @if ($transfers->hasPages())
        <div class="border-t border-slate-100 px-5 py-4">
            {{ $transfers->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
