<x-app-layout>
    <x-slot name="title">{{ __('POS Terminal') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('POS Terminal') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ __('Scan or search an item, take payment, print the receipt — stock leaves immediately.') }}</p>
        </div>
    </x-slot>

    @if (! $siteId)
    <div class="rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-700 ring-1 ring-amber-200">
        {{ __('No site selected — pick a site before selling.') }}
        <a href="{{ route('sites.select') }}" class="font-semibold underline">{{ __('Select a site') }}</a>
    </div>
    @else
    <div x-data="posTerminal(@js([
            'accounts' => $accounts,
            'productsUrl' => route('pos.products'),
            'checkoutUrl' => route('pos.checkout'),
            'customersUrl' => route('pos.customers.store'),
        ]))" class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-start">

        <!-- Left: scan/search + cart -->
        <div class="lg:col-span-2 space-y-4">
            <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
                <div class="relative">
                    <input x-ref="scanInput" x-model="query" @input="search()" @keydown.enter.prevent="handleEnter()"
                           autofocus placeholder="{{ __('Scan a barcode or type a product name / SKU…') }}"
                           class="block w-full rounded-lg border-slate-300 text-base py-3 focus:border-accent-500 focus:ring-accent-500">
                    <div x-show="results.length" x-cloak
                         class="absolute z-20 mt-1 w-full max-h-72 overflow-y-auto rounded-xl bg-white py-1.5 shadow-lg ring-1 ring-slate-200">
                        <template x-for="opt in results" :key="opt.id">
                            <div @click="addResult(opt)"
                                 class="flex cursor-pointer items-center justify-between gap-3 px-4 py-2 text-sm hover:bg-slate-50"
                                 :class="opt.available_qty <= 0 && 'opacity-40 pointer-events-none'">
                                <span>
                                    <span x-text="opt.name" class="text-slate-700 font-medium"></span>
                                    <span class="block text-xs text-slate-400" x-text="(opt.available_qty ?? 0) + ' ' + (opt.unit || '') + ' available'"></span>
                                </span>
                                <span class="shrink-0 font-semibold text-slate-800" x-text="Number(opt.price).toFixed(2)"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                <table class="w-full min-w-[520px] text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                            <th class="px-5 py-3 font-semibold">{{ __('Item') }}</th>
                            <th class="px-5 py-3 font-semibold w-36">{{ __('Quantity') }}</th>
                            <th class="px-5 py-3 font-semibold text-right w-28">{{ __('Price') }}</th>
                            <th class="px-5 py-3 font-semibold text-right w-28">{{ __('Subtotal') }}</th>
                            <th class="px-5 py-3 font-semibold w-12"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="row in cart" :key="row.id">
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-3">
                                    <span class="font-semibold text-slate-800" x-text="row.name"></span>
                                    <span class="block text-xs text-slate-400" x-text="row.unit || ''"></span>
                                </td>
                                <td class="px-5 py-3">
                                    <div class="inline-flex items-center gap-2">
                                        <button type="button" @click="decQty(row)" class="h-7 w-7 rounded-md border border-slate-200 text-slate-500 hover:bg-slate-50">&minus;</button>
                                        <span class="w-8 text-center font-semibold" x-text="row.quantity"></span>
                                        <button type="button" @click="incQty(row)" class="h-7 w-7 rounded-md border border-slate-200 text-slate-500 hover:bg-slate-50">+</button>
                                    </div>
                                </td>
                                <td class="px-5 py-3 text-right tabular-nums text-slate-500" x-text="Number(row.price).toFixed(2)"></td>
                                <td class="px-5 py-3 text-right tabular-nums font-semibold text-slate-800" x-text="(row.quantity * row.price).toFixed(2)"></td>
                                <td class="px-5 py-3 text-right">
                                    <button type="button" @click="removeItem(row.id)" class="text-slate-400 hover:text-rose-600" title="{{ __('Remove') }}">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="cart.length === 0" x-cloak>
                            <td colspan="5" class="px-5 py-12 text-center text-slate-400">{{ __('Cart is empty — scan or search an item to begin.') }}</td>
                        </tr>
                    </tbody>
                </table>
                </div>
            </div>
        </div>

        <!-- Right: customer + payment + checkout -->
        <div class="space-y-4">
            <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
                <x-input-label :value="__('Customer')" />
                <div class="mt-2 flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2">
                    <span class="text-sm font-semibold text-slate-700" x-text="customer ? customer.name + ' (' + customer.phone + ')' : '{{ __('Walk-in Customer') }}'"></span>
                    <button type="button" x-show="customer" x-cloak @click="selectWalkIn()" class="text-xs font-semibold text-slate-400 hover:text-rose-600">{{ __('Clear') }}</button>
                </div>
                <button type="button" @click="openNewCustomer()"
                        class="mt-2 w-full rounded-lg border border-dashed border-slate-300 px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                    {{ __('+ New Customer') }}
                </button>
            </div>

            <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
                <x-input-label :value="__('Payment Method')" />
                <div class="mt-2 grid grid-cols-2 gap-2">
                    <template x-for="acct in accounts" :key="acct.id">
                        <button type="button" @click="selectedAccountId = acct.id"
                                class="rounded-lg px-3 py-2 text-sm font-semibold ring-1 transition-colors"
                                :class="selectedAccountId === acct.id ? 'bg-brand-800 text-white ring-brand-800' : 'bg-white text-slate-600 ring-slate-200 hover:bg-slate-50'"
                                x-text="acct.name"></button>
                    </template>
                </div>

                <div x-show="isCash" x-cloak class="mt-3">
                    <x-input-label for="cash_tendered" :value="__('Cash Tendered')" />
                    <input id="cash_tendered" type="number" step="0.01" min="0" x-model.number="cashTendered"
                           class="mt-1 block w-full rounded-lg border-slate-300 text-sm text-right focus:border-accent-500 focus:ring-accent-500" placeholder="0.00">
                    <p class="mt-1 text-right text-sm text-slate-500">{{ __('Change Due') }}: <span class="font-semibold text-slate-800" x-text="change.toFixed(2)"></span></p>
                </div>
            </div>

            <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
                <div class="flex items-center justify-between text-sm text-slate-500">
                    <span>{{ __('Subtotal') }}</span>
                    <span x-text="subtotal.toFixed(2)"></span>
                </div>
                <div class="mt-2 flex items-center justify-between text-sm">
                    <span class="text-slate-500">{{ __('Discount') }}</span>
                    <input type="number" step="0.01" min="0" x-model.number="discount"
                           class="w-24 rounded-lg border-slate-300 text-sm text-right focus:border-accent-500 focus:ring-accent-500" placeholder="0.00">
                </div>
                <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-3">
                    <span class="font-semibold text-slate-700">{{ __('Total') }}</span>
                    <span class="text-2xl font-bold text-brand-900" x-text="total.toFixed(2)"></span>
                </div>

                <p x-show="checkoutError" x-cloak x-text="checkoutError" class="mt-3 text-sm text-rose-600"></p>

                <button type="button" @click="checkout()" :disabled="cart.length === 0 || submitting"
                        class="mt-4 w-full inline-flex items-center justify-center gap-2 rounded-lg bg-brand-800 px-4 py-3 text-base font-semibold text-white hover:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-50">
                    <span x-show="!submitting">{{ __('Checkout & Print') }}</span>
                    <span x-show="submitting" x-cloak>{{ __('Processing…') }}</span>
                </button>
            </div>
        </div>

        <x-modal name="pos-new-customer" max-width="md" focusable>
            <div class="p-6">
                <h2 class="text-lg font-bold text-brand-900">{{ __('New Customer') }}</h2>
                <div class="mt-4 space-y-4">
                    <div>
                        <x-input-label for="new_customer_name" :value="__('Name')" />
                        <input id="new_customer_name" x-model="newCustomerName"
                               class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">
                    </div>
                    <div>
                        <x-input-label for="new_customer_phone" :value="__('Mobile No.')" />
                        <input id="new_customer_phone" x-model="newCustomerPhone"
                               class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">
                    </div>
                    <p x-show="customerError" x-cloak x-text="customerError" class="text-sm text-rose-600"></p>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <x-secondary-button x-on:click="$dispatch('close')">{{ __('Cancel') }}</x-secondary-button>
                    <x-primary-button type="button" @click="saveCustomer()">
                        <span x-show="!savingCustomer">{{ __('Save & Select') }}</span>
                        <span x-show="savingCustomer" x-cloak>{{ __('Saving…') }}</span>
                    </x-primary-button>
                </div>
            </div>
        </x-modal>
    </div>
    @endif
</x-app-layout>
