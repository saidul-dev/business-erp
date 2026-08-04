<x-app-layout>
    <x-slot name="title">{{ __('Bank Accounts') }}</x-slot>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-brand-900">{{ __('Bank Accounts') }}</h2>
                <p class="text-sm text-slate-500 mt-0.5">{{ __('Cash and bank accounts the accounting ledger posts against') }}</p>
            </div>
            @can('accounts.create')
            <a href="{{ route('bank-accounts.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 focus:ring-2 focus:ring-accent-500 focus:ring-offset-2">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                {{ __('Add Bank Account') }}
            </a>
            @endcan
        </div>
    </x-slot>

    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full min-w-[680px] text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <th class="px-5 py-3 font-semibold">{{ __('Account') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Branch') }}</th>
                    <th class="px-5 py-3 font-semibold text-right">{{ __('Balance') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Status') }}</th>
                    <th class="px-5 py-3 font-semibold text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($bankAccounts as $account)
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-3">
                        <a href="{{ route('bank-accounts.ledger', $account) }}" class="font-semibold text-slate-800 hover:text-accent-600">{{ $account->name }}</a>
                        <span class="inline-flex ml-2 rounded-full px-2 py-0.5 text-[11px] font-semibold ring-1 {{ $account->is_cash ? 'bg-emerald-50 text-emerald-600 ring-emerald-200' : 'bg-blue-50 text-blue-600 ring-blue-200' }}">
                            {{ $account->is_cash ? __('Cash') : __('Bank/Mobile') }}
                        </span>
                        @if ($account->is_system)
                            <span class="block text-xs text-slate-400">{{ __('System account') }}</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-slate-600">{{ $account->branch->name ?? __('Company-wide') }}</td>
                    <td class="px-5 py-3 text-right font-semibold text-slate-800">{{ number_format($account->balance(), 2) }}</td>
                    <td class="px-5 py-3">
                        @can('accounts.edit')
                        <form method="POST" action="{{ route('bank-accounts.toggle-status', $account) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit"
                                    class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1
                                    {{ $account->status ? 'bg-emerald-50 text-emerald-600 ring-emerald-200' : 'bg-slate-100 text-slate-500 ring-slate-200' }}">
                                {{ $account->status ? __('Active') : __('Inactive') }}
                            </button>
                        </form>
                        @else
                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1
                            {{ $account->status ? 'bg-emerald-50 text-emerald-600 ring-emerald-200' : 'bg-slate-100 text-slate-500 ring-slate-200' }}">
                            {{ $account->status ? __('Active') : __('Inactive') }}
                        </span>
                        @endcan
                    </td>
                    <td class="px-5 py-3">
                        <div class="flex items-center justify-end gap-1">
                            <a href="{{ route('bank-accounts.ledger', $account) }}" title="{{ __('Click to view this account\'s ledger') }}"
                               class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-semibold text-slate-500 ring-1 ring-slate-200 hover:bg-brand-50 hover:text-brand-700 hover:ring-brand-200">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                {{ __('Ledger') }}
                            </a>
                            @can('accounts.edit')
                            <a href="{{ route('bank-accounts.edit', $account) }}" title="{{ __('Edit') }}"
                               class="rounded-lg p-2 text-slate-400 hover:bg-brand-50 hover:text-brand-700">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg>
                            </a>
                            @endcan
                            @can('accounts.delete')
                            @if (! $account->is_system)
                            <form method="POST" action="{{ route('bank-accounts.destroy', $account) }}"
                                  onsubmit="return confirm('{{ __('Delete account :name? This cannot be undone.', ['name' => $account->name]) }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" title="{{ __('Delete') }}"
                                        class="rounded-lg p-2 text-slate-400 hover:bg-rose-50 hover:text-rose-600">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                </button>
                            </form>
                            @endif
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-5 py-10 text-center text-slate-400">{{ __('No bank accounts yet.') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>

        @if ($bankAccounts->hasPages())
        <div class="border-t border-slate-100 px-5 py-4">
            {{ $bankAccounts->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
