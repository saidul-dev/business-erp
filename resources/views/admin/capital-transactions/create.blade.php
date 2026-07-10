<x-app-layout>
    <x-slot name="title">{{ __('New Capital Transaction') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('New Capital Transaction') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ __("Record money the owner puts into or takes out of the business.") }}</p>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('capital-transactions.store') }}" x-data="{ type: 'investment', amount: '{{ old('amount', '') }}' }">
        @csrf
        <input type="hidden" name="type" x-model="type">

        <div class="grid grid-cols-1 lg:grid-cols-[1fr_320px] gap-6 items-start">
            <div class="order-2 lg:order-1 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 space-y-5">
                <div>
                    <x-input-label :value="__('Type')" />
                    <div class="mt-1 flex h-11 w-full max-w-xs items-stretch rounded-lg border border-slate-300 bg-slate-50 p-1 text-sm">
                        <button type="button" @click="type = 'investment'"
                                class="flex flex-1 items-center justify-center whitespace-nowrap rounded-md px-2.5 text-center font-semibold transition-colors"
                                :class="type === 'investment' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-500 hover:text-slate-800'">
                            {{ __('Investment') }}
                        </button>
                        <button type="button" @click="type = 'drawing'"
                                class="flex flex-1 items-center justify-center whitespace-nowrap rounded-md px-2.5 text-center font-semibold transition-colors"
                                :class="type === 'drawing' ? 'bg-rose-600 text-white shadow-sm' : 'text-slate-500 hover:text-slate-800'">
                            {{ __('Drawing') }}
                        </button>
                    </div>
                    <x-input-error class="mt-2" :messages="$errors->get('type')" />
                </div>

                <div>
                    <x-input-label for="account_id">
                        <span x-text="type === 'investment' ? '{{ __('Received Into') }}' : '{{ __('Paid From') }}'"></span>
                    </x-input-label>
                    <div class="mt-1">
                        <x-searchable-select name="account_id" :options="$accounts" :selected="old('account_id')"
                                              placeholder="{{ __('Select a cash/bank account…') }}" />
                    </div>
                    <x-input-error class="mt-2" :messages="$errors->get('account_id')" />
                    <p class="mt-1.5 text-xs text-slate-400" x-show="type === 'investment'">{{ __("Which account the owner's money landed in.") }}</p>
                    <p class="mt-1.5 text-xs text-slate-400" x-show="type === 'drawing'" x-cloak>{{ __('Which account the money was paid out from.') }}</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="amount" :value="__('Amount')" />
                        <x-text-input id="amount" name="amount" type="number" step="0.01" min="0.01" class="mt-1 block w-full"
                                      x-model="amount" required />
                        <x-input-error class="mt-2" :messages="$errors->get('amount')" />
                    </div>
                    <div>
                        <x-input-label for="transaction_date" :value="__('Date')" />
                        <x-text-input id="transaction_date" name="transaction_date" type="date" class="mt-1 block w-full"
                                      :value="old('transaction_date', now()->toDateString())" required />
                        <x-input-error class="mt-2" :messages="$errors->get('transaction_date')" />
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
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-brand-800 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">
                        {{ __('Record Transaction') }}
                    </button>
                </div>
            </div>

            <div class="order-1 lg:order-2 lg:sticky lg:top-6 rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 overflow-hidden">
                <div class="p-5">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-brand-700">{{ __('Amount') }}</p>
                    <p class="mt-1 font-mono text-3xl font-black tabular-nums text-brand-800"
                       x-text="amount ? Number(amount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '0.00'"></p>
                </div>
                <div class="border-t border-slate-100 bg-brand-50/60 px-5 py-4">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-brand-700">{{ __('Net Capital Contributed') }}</p>
                    <p class="mt-1 text-lg font-bold text-brand-800">{{ number_format($netCapital, 2) }}</p>
                </div>
                <div class="hidden lg:block border-t border-slate-100 p-5">
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-brand-800 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                        {{ __('Record Transaction') }}
                    </button>
                </div>
            </div>
        </div>
    </form>
</x-app-layout>
