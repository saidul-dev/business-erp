<x-app-layout>
    <x-slot name="title">{{ __('Edit Quotation') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('Edit Quotation') }} — {{ $saleQuotation->quotation_no }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ __('Only possible while still pending.') }}</p>
        </div>
    </x-slot>

    <div x-data="quotationCart(@js(['itemOptions' => $itemOptions, 'items' => $items, 'discount' => (float) $saleQuotation->discount_amount]))">
        <form method="POST" action="{{ route('sale-quotations.update', $saleQuotation) }}" x-ref="form">
            @csrf
            @method('PUT')

            <!-- Customer / Site / Dates -->
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 mb-4">
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div>
                        <x-input-label for="party_id" :value="__('Customer')" />
                        <div class="mt-1">
                            <x-searchable-select name="party_id" :options="$customers" :selected="old('party_id', $saleQuotation->party_id)"
                                                  placeholder="{{ __('Select a customer…') }}" />
                        </div>
                        <x-input-error class="mt-2" :messages="$errors->get('party_id')" />
                    </div>
                    <div>
                        <x-input-label for="site_id" :value="__('Site')" />
                        <div class="mt-1">
                            <x-searchable-select name="site_id" :options="$sites" :selected="old('site_id', $saleQuotation->site_id)"
                                                  placeholder="{{ __('Shipping site…') }}" />
                        </div>
                        <x-input-error class="mt-2" :messages="$errors->get('site_id')" />
                    </div>
                    <div>
                        <x-input-label for="quote_date" :value="__('Quote Date')" />
                        <x-text-input id="quote_date" name="quote_date" type="date" class="mt-1 block w-full"
                                      :value="old('quote_date', $saleQuotation->quote_date->toDateString())" required />
                        <x-input-error class="mt-2" :messages="$errors->get('quote_date')" />
                    </div>
                    <div>
                        <x-input-label for="valid_until" :value="__('Valid Until (optional)')" />
                        <x-text-input id="valid_until" name="valid_until" type="date" class="mt-1 block w-full"
                                      :value="old('valid_until', $saleQuotation->valid_until?->toDateString())" />
                        <x-input-error class="mt-2" :messages="$errors->get('valid_until')" />
                    </div>
                </div>
            </div>

            <!-- Item picker -->
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 mb-4">
                <x-input-label :value="__('Add Item')" />
                <div class="relative mt-1 max-w-md" @click.outside="open = false">
                    <input type="text" x-model="query" @focus="open = true"
                           placeholder="{{ __('Search a product or variant…') }}"
                           class="block w-full rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">
                    <div x-show="open" x-cloak x-transition
                         class="absolute z-20 mt-1 w-full max-h-64 overflow-y-auto rounded-xl bg-white py-1.5 shadow-lg ring-1 ring-slate-200">
                        <template x-for="opt in filtered" :key="opt.id">
                            <div @click="addItem(opt)"
                                 class="flex cursor-pointer items-center justify-between gap-3 px-4 py-2 text-sm hover:bg-slate-50">
                                <span x-text="opt.name" class="text-slate-700"></span>
                                <span class="shrink-0 text-xs text-slate-400" x-text="opt.unit || ''"></span>
                            </div>
                        </template>
                        <div x-show="filtered.length === 0" class="px-4 py-2 text-sm text-slate-400">{{ __('No matching products.') }}</div>
                    </div>
                </div>
            </div>

            <!-- Cart -->
            <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 overflow-hidden mb-4" x-show="items.length" x-cloak>
                <div class="overflow-x-auto">
                <table class="w-full min-w-[720px] text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                            <th class="px-5 py-3 font-semibold">{{ __('Item') }}</th>
                            <th class="px-5 py-3 font-semibold w-32">{{ __('Quantity') }}</th>
                            <th class="px-5 py-3 font-semibold w-32">{{ __('Unit Price') }}</th>
                            <th class="px-5 py-3 font-semibold text-right w-32">{{ __('Subtotal') }}</th>
                            <th class="px-5 py-3 font-semibold w-16"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="(item, i) in items" :key="item.id">
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-3">
                                    <input type="hidden" :name="'items[' + i + '][item]'" :value="item.id">
                                    <span class="font-semibold text-slate-800" x-text="item.name"></span>
                                    <span class="block text-xs text-slate-400" x-text="item.unit || ''"></span>
                                </td>
                                <td class="px-5 py-3">
                                    <input type="number" step="0.0001" min="0.0001"
                                           :name="'items[' + i + '][quantity]'" x-model.number="item.quantity"
                                           class="w-24 rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500" placeholder="0" required>
                                </td>
                                <td class="px-5 py-3">
                                    <input type="number" step="0.01" min="0"
                                           :name="'items[' + i + '][unit_price]'" x-model.number="item.unit_price"
                                           class="w-28 rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500" placeholder="0.00" required>
                                </td>
                                <td class="px-5 py-3 text-right font-semibold text-slate-800" x-text="((item.quantity || 0) * (item.unit_price || 0)).toFixed(2)"></td>
                                <td class="px-5 py-3 text-right">
                                    <button type="button" @click="removeItem(item.id)" class="text-slate-400 hover:text-rose-600" title="{{ __('Remove') }}">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                    <tfoot>
                        <tr class="border-t border-slate-100">
                            <td colspan="3" class="px-5 py-2 text-right text-slate-500">{{ __('Subtotal') }}</td>
                            <td class="px-5 py-2 text-right font-semibold text-slate-700" x-text="subtotal.toFixed(2)"></td>
                            <td></td>
                        </tr>
                        <tr class="border-t border-slate-100">
                            <td colspan="3" class="px-5 py-2 text-right text-slate-500">{{ __('Discount') }}</td>
                            <td class="px-5 py-2 text-right">
                                <input type="number" step="0.01" min="0" name="discount_amount" x-model.number="discount"
                                       class="w-28 rounded-lg border-slate-300 text-sm text-right focus:border-accent-500 focus:ring-accent-500" placeholder="0.00">
                            </td>
                            <td></td>
                        </tr>
                        <tr class="border-t border-slate-100 bg-slate-50">
                            <td colspan="3" class="px-5 py-3 text-right font-semibold text-slate-600">{{ __('Total') }}</td>
                            <td class="px-5 py-3 text-right font-bold text-brand-900" x-text="total.toFixed(2)"></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
                </div>
            </div>
            <x-input-error class="mb-4" :messages="$errors->get('items')" />
            <x-input-error class="mb-4" :messages="$errors->get('discount_amount')" />

            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200" x-show="items.length" x-cloak>
                <div class="mb-5 max-w-xl">
                    <x-input-label for="note" :value="__('Note (optional)')" />
                    <textarea id="note" name="note" rows="2"
                              class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">{{ old('note', $saleQuotation->note) }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('note')" />
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit" :disabled="items.length === 0"
                            class="inline-flex items-center gap-2 rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-accent-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                        {{ __('Update Quotation') }}
                    </button>
                    <a href="{{ route('sale-quotations.show', $saleQuotation) }}" class="text-sm font-semibold text-slate-500 hover:text-slate-700">{{ __('Cancel') }}</a>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
