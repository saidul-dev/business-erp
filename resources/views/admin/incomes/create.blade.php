<x-app-layout>
    <x-slot name="title">{{ __('New Income') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('New Income') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ __('Record miscellaneous income — this credits the category and debits the receiving account.') }}</p>
        </div>
    </x-slot>

    @php
        $categoryIcons = [
            'interest_income' => 'M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
            'rental_income' => 'M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6 21v-3.375c0-.621.504-1.125 1.125-1.125h1.75c.621 0 1.125.504 1.125 1.125V21',
            'commission_income' => 'M2.25 18L9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.28m5.94 2.28-2.28 5.94',
            'other_income' => 'M12 6.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 12.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 18.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5Z',
        ];
    @endphp

    <form method="POST" action="{{ route('incomes.store') }}"
          x-data="{ category: '{{ old('category_account_id', '') }}', amount: '{{ old('amount', '') }}' }">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-[1fr_320px] gap-6 items-start">
            <div class="order-2 lg:order-1 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 space-y-5">
                <div>
                    <x-input-label :value="__('Category')" />
                    <input type="hidden" name="category_account_id" x-model="category">
                    <div class="mt-1 grid grid-cols-2 sm:grid-cols-3 gap-2">
                        @foreach ($categories as $cat)
                        <button type="button" @click="category = '{{ $cat->id }}'"
                                class="flex items-center gap-2 rounded-lg border-2 px-3 py-2.5 text-left text-sm font-semibold transition-colors"
                                :class="category === '{{ $cat->id }}' ? 'border-teal-500 bg-teal-50 text-teal-800' : 'border-slate-200 text-slate-600 hover:border-slate-300'">
                            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $categoryIcons[$cat->code] ?? $categoryIcons['other_income'] }}"/></svg>
                            {{ $cat->name }}
                        </button>
                        @endforeach
                    </div>
                    <x-input-error class="mt-2" :messages="$errors->get('category_account_id')" />
                </div>

                <div>
                    <x-input-label for="received_into_account_id" :value="__('Received Into')" />
                    <div class="mt-1">
                        <x-searchable-select name="received_into_account_id" :options="$accounts" :selected="old('received_into_account_id')"
                                              placeholder="{{ __('Select a cash/bank account…') }}" />
                    </div>
                    <x-input-error class="mt-2" :messages="$errors->get('received_into_account_id')" />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="amount" :value="__('Amount')" />
                        <x-text-input id="amount" name="amount" type="number" step="0.01" min="0.01" class="mt-1 block w-full"
                                      x-model="amount" required />
                        <x-input-error class="mt-2" :messages="$errors->get('amount')" />
                    </div>
                    <div>
                        <x-input-label for="income_date" :value="__('Date')" />
                        <x-text-input id="income_date" name="income_date" type="date" class="mt-1 block w-full"
                                      :value="old('income_date', now()->toDateString())" required />
                        <x-input-error class="mt-2" :messages="$errors->get('income_date')" />
                    </div>
                </div>

                <div>
                    <x-input-label for="reference_no" :value="__('Reference No. (optional)')" />
                    <x-text-input id="reference_no" name="reference_no" type="text" class="mt-1 block w-full"
                                  :value="old('reference_no')" placeholder="{{ __('Receipt no. / transaction ID') }}" />
                    <x-input-error class="mt-2" :messages="$errors->get('reference_no')" />
                </div>

                <div>
                    <x-input-label for="note" :value="__('Note (optional)')" />
                    <textarea id="note" name="note" rows="2"
                              class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">{{ old('note') }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('note')" />
                </div>

                <div class="pt-1 lg:hidden">
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-teal-700">
                        {{ __('Record Income') }}
                    </button>
                </div>
            </div>

            <div class="order-1 lg:order-2 lg:sticky lg:top-6 rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 overflow-hidden">
                <div class="p-5">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-teal-700">{{ __('Amount') }}</p>
                    <p class="mt-1 font-mono text-3xl font-black tabular-nums text-teal-700"
                       x-text="amount ? Number(amount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '0.00'"></p>
                    <template x-if="category">
                        <p class="mt-3 border-t border-slate-100 pt-3 text-sm text-slate-600">
                            {{ __('For') }}: <span class="font-semibold text-slate-800" x-text="(@js($categories->pluck('name', 'id')))[category] || ''"></span>
                        </p>
                    </template>
                </div>
                <div class="border-t border-slate-100 bg-teal-50/60 px-5 py-4">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-teal-700">{{ __("This Month's Income") }}</p>
                    <p class="mt-1 text-lg font-bold text-teal-800">{{ number_format($monthTotal, 2) }}</p>
                </div>
                <div class="hidden lg:block border-t border-slate-100 p-5">
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-teal-700">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                        {{ __('Record Income') }}
                    </button>
                </div>
            </div>
        </div>
    </form>
</x-app-layout>
