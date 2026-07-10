<x-app-layout>
    <x-slot name="title">{{ __('Day Book') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('Day Book') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ __('Every voucher posted to the ledger — Purchases, Sales, Payments, Collections and opening balances — in one place') }}</p>
        </div>
    </x-slot>

    <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 mb-6">
        <form method="GET" action="{{ route('day-book.index') }}" class="flex flex-wrap items-end gap-4">
            <div class="w-40 shrink-0">
                <x-input-label for="from" :value="__('From')" />
                <x-text-input id="from" name="from" type="date" class="mt-1 block w-full" :value="$from" />
            </div>
            <div class="w-40 shrink-0">
                <x-input-label for="to" :value="__('To')" />
                <x-text-input id="to" name="to" type="date" class="mt-1 block w-full" :value="$to" />
            </div>
            <div class="w-48 shrink-0">
                <x-input-label for="type" :value="__('Type')" />
                <select name="type" class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">
                    <option value="">{{ __('All types') }}</option>
                    @foreach ($types as $key)
                        <option value="{{ $key }}" @selected($type === $key)>{{ \App\Models\LedgerTransaction::TYPE_LABELS[$key] ?? $key }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-56 shrink-0">
                <x-input-label for="site_id" :value="__('Site')" />
                <div class="mt-1">
                    <x-searchable-select name="site_id" :options="$sites" :selected="$siteId" placeholder="{{ __('All sites') }}" />
                </div>
            </div>
            <button type="submit" class="rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                {{ __('Filter') }}
            </button>
            @if ($type || $siteId)
            <a href="{{ route('day-book.index') }}" class="text-sm font-semibold text-slate-500 hover:text-slate-700">{{ __('Clear') }}</a>
            @endif
        </form>
    </div>

    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full min-w-[920px] text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <th class="px-5 py-3 font-semibold">{{ __('Date') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Voucher') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Type') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Narration') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Site') }}</th>
                    <th class="px-5 py-3 font-semibold text-right">{{ __('Amount') }}</th>
                    <th class="px-5 py-3 font-semibold"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($transactions as $txn)
                @php
                    $typeStyles = [
                        'purchase' => 'bg-amber-50 text-amber-700 ring-amber-200',
                        'sale' => 'bg-sky-50 text-sky-700 ring-sky-200',
                        'payment_out' => 'bg-rose-50 text-rose-700 ring-rose-200',
                        'payment_in' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                        'expense' => 'bg-orange-50 text-orange-700 ring-orange-200',
                        'income' => 'bg-teal-50 text-teal-700 ring-teal-200',
                        'transfer' => 'bg-indigo-50 text-indigo-700 ring-indigo-200',
                        'investment' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                        'drawing' => 'bg-rose-50 text-rose-700 ring-rose-200',
                        'opening_balance' => 'bg-slate-100 text-slate-600 ring-slate-200',
                        'journal' => 'bg-violet-50 text-violet-700 ring-violet-200',
                    ];
                    $referenceUrl = $txn->referenceUrl();
                @endphp
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-3 text-slate-600">{{ $txn->date->format('d M, Y') }}</td>
                    <td class="px-5 py-3 font-semibold text-slate-800">{{ $txn->voucher_no }}</td>
                    <td class="px-5 py-3">
                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 {{ $typeStyles[$txn->type] ?? $typeStyles['journal'] }}">
                            {{ \App\Models\LedgerTransaction::TYPE_LABELS[$txn->type] ?? $txn->type }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-slate-600">{{ $txn->narration }}</td>
                    <td class="px-5 py-3 text-slate-500">{{ $txn->site->name ?? '—' }}</td>
                    <td class="px-5 py-3 text-right font-semibold text-slate-800">{{ number_format($txn->total_debit, 2) }}</td>
                    <td class="px-5 py-3 text-right">
                        @if ($referenceUrl)
                        <a href="{{ $referenceUrl }}" class="font-semibold text-accent-600 hover:text-accent-800">{{ __('View') }}</a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-5 py-10 text-center text-slate-400">{{ __('No vouchers in this range.') }}</td>
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
