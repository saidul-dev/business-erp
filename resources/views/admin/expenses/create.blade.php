<x-app-layout>
    <x-slot name="title">{{ __('New Expense') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('New Expense') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ __('Record a direct expense — this debits the category and credits the paying account.') }}</p>
        </div>
    </x-slot>

    @php
        $categoryIcons = [
            'rent_expense' => 'M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25',
            'utility_expense' => 'M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z',
            'salary_expense' => 'M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z',
            'office_expense' => 'M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0',
            'other_expense' => 'M12 6.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 12.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 18.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5Z',
        ];
    @endphp

    <form method="POST" action="{{ route('expenses.store') }}"
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
                                :class="category === '{{ $cat->id }}' ? 'border-amber-500 bg-amber-50 text-amber-800' : 'border-slate-200 text-slate-600 hover:border-slate-300'">
                            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $categoryIcons[$cat->code] ?? $categoryIcons['other_expense'] }}"/></svg>
                            {{ $cat->name }}
                        </button>
                        @endforeach
                    </div>
                    <x-input-error class="mt-2" :messages="$errors->get('category_account_id')" />
                </div>

                <div>
                    <x-input-label for="paid_from_account_id" :value="__('Paid From')" />
                    <div class="mt-1">
                        <x-searchable-select name="paid_from_account_id" :options="$accounts" :selected="old('paid_from_account_id')"
                                              placeholder="{{ __('Select a cash/bank account…') }}" />
                    </div>
                    <x-input-error class="mt-2" :messages="$errors->get('paid_from_account_id')" />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="amount" :value="__('Amount')" />
                        <x-text-input id="amount" name="amount" type="number" step="0.01" min="0.01" class="mt-1 block w-full"
                                      x-model="amount" required />
                        <x-input-error class="mt-2" :messages="$errors->get('amount')" />
                    </div>
                    <div>
                        <x-input-label for="expense_date" :value="__('Date')" />
                        <x-text-input id="expense_date" name="expense_date" type="date" class="mt-1 block w-full"
                                      :value="old('expense_date', now()->toDateString())" required />
                        <x-input-error class="mt-2" :messages="$errors->get('expense_date')" />
                    </div>
                </div>

                <div>
                    <x-input-label for="reference_no" :value="__('Reference No. (optional)')" />
                    <x-text-input id="reference_no" name="reference_no" type="text" class="mt-1 block w-full"
                                  :value="old('reference_no')" placeholder="{{ __('Bill no. / receipt no.') }}" />
                    <x-input-error class="mt-2" :messages="$errors->get('reference_no')" />
                </div>

                <div>
                    <x-input-label for="note" :value="__('Note (optional)')" />
                    <textarea id="note" name="note" rows="2"
                              class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">{{ old('note') }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('note')" />
                </div>

                <div class="pt-1 lg:hidden">
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-amber-700">
                        {{ __('Record Expense') }}
                    </button>
                </div>
            </div>

            <div class="order-1 lg:order-2 lg:sticky lg:top-6 rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 overflow-hidden">
                <div class="p-5">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-amber-700">{{ __('Amount') }}</p>
                    <p class="mt-1 font-mono text-3xl font-black tabular-nums text-amber-700"
                       x-text="amount ? Number(amount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '0.00'"></p>
                    <template x-if="category">
                        <p class="mt-3 border-t border-slate-100 pt-3 text-sm text-slate-600">
                            {{ __('For') }}: <span class="font-semibold text-slate-800" x-text="(@js($categories->pluck('name', 'id')))[category] || ''"></span>
                        </p>
                    </template>
                </div>
                <div class="border-t border-slate-100 bg-amber-50/60 px-5 py-4">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-amber-700">{{ __("This Month's Expenses") }}</p>
                    <p class="mt-1 text-lg font-bold text-amber-800">{{ number_format($monthTotal, 2) }}</p>
                </div>
                <div class="hidden lg:block border-t border-slate-100 p-5">
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-amber-700">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                        {{ __('Record Expense') }}
                    </button>
                </div>
            </div>
        </div>
    </form>
</x-app-layout>
