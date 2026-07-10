<x-app-layout>
    <x-slot name="title">{{ __('New Transfer') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('New Transfer') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ __('Move money between your own cash and bank accounts — this debits the destination and credits the source.') }}</p>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('fund-transfers.store') }}" x-data="{ amount: '{{ old('amount', '') }}' }">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-[1fr_320px] gap-6 items-start">
            <div class="order-2 lg:order-1 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 space-y-5">
                <div class="grid grid-cols-1 sm:grid-cols-[1fr_auto_1fr] gap-3 sm:items-end">
                    <div>
                        <x-input-label for="from_account_id" :value="__('From')" />
                        <div class="mt-1">
                            <x-searchable-select name="from_account_id" :options="$accounts" :selected="old('from_account_id')"
                                                  placeholder="{{ __('Source account…') }}" />
                        </div>
                        <x-input-error class="mt-2" :messages="$errors->get('from_account_id')" />
                    </div>
                    <div class="hidden sm:flex justify-center pb-2.5 text-slate-300">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                    </div>
                    <div>
                        <x-input-label for="to_account_id" :value="__('To')" />
                        <div class="mt-1">
                            <x-searchable-select name="to_account_id" :options="$accounts" :selected="old('to_account_id')"
                                                  placeholder="{{ __('Destination account…') }}" />
                        </div>
                        <x-input-error class="mt-2" :messages="$errors->get('to_account_id')" />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="amount" :value="__('Amount')" />
                        <x-text-input id="amount" name="amount" type="number" step="0.01" min="0.01" class="mt-1 block w-full"
                                      x-model="amount" required />
                        <x-input-error class="mt-2" :messages="$errors->get('amount')" />
                    </div>
                    <div>
                        <x-input-label for="transfer_date" :value="__('Date')" />
                        <x-text-input id="transfer_date" name="transfer_date" type="date" class="mt-1 block w-full"
                                      :value="old('transfer_date', now()->toDateString())" required />
                        <x-input-error class="mt-2" :messages="$errors->get('transfer_date')" />
                    </div>
                </div>

                <div>
                    <x-input-label for="reference_no" :value="__('Reference No. (optional)')" />
                    <x-text-input id="reference_no" name="reference_no" type="text" class="mt-1 block w-full"
                                  :value="old('reference_no')" placeholder="{{ __('Slip no. / transaction ID') }}" />
                    <x-input-error class="mt-2" :messages="$errors->get('reference_no')" />
                </div>

                <div>
                    <x-input-label for="note" :value="__('Note (optional)')" />
                    <textarea id="note" name="note" rows="2"
                              class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">{{ old('note') }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('note')" />
                </div>

                <div class="pt-1 lg:hidden">
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-violet-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-violet-700">
                        {{ __('Record Transfer') }}
                    </button>
                </div>
            </div>

            <div class="order-1 lg:order-2 lg:sticky lg:top-6 rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 overflow-hidden">
                <div class="p-5">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-violet-700">{{ __('Amount') }}</p>
                    <p class="mt-1 font-mono text-3xl font-black tabular-nums text-violet-700"
                       x-text="amount ? Number(amount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '0.00'"></p>
                </div>
                <div class="border-t border-slate-100 bg-violet-50/60 px-5 py-4">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-violet-700">{{ __("This Month's Transfers") }}</p>
                    <p class="mt-1 text-lg font-bold text-violet-800">{{ number_format($monthTotal, 2) }}</p>
                </div>
                <div class="hidden lg:block border-t border-slate-100 p-5">
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-violet-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-violet-700">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                        {{ __('Record Transfer') }}
                    </button>
                </div>
            </div>
        </div>
    </form>
</x-app-layout>
