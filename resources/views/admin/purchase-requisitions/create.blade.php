<x-app-layout>
    <x-slot name="title">{{ __('New Requisition') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('New Requisition') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ __('Request stock to be purchased — no price is committed here, only quantity and (optionally) a suggested supplier.') }}</p>
        </div>
    </x-slot>

    <div x-data="requisitionCart(@js(['itemOptions' => $itemOptions]))">
        <form method="POST" action="{{ route('purchase-requisitions.store') }}" x-ref="form">
            @csrf

            <!-- Site / Supplier / Dates -->
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 mb-4">
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div>
                        <x-input-label for="site_id" :value="__('Requesting Site')" />
                        <div class="mt-1">
                            <x-searchable-select name="site_id" :options="$sites" :selected="old('site_id')"
                                                  placeholder="{{ __('Select a site…') }}" />
                        </div>
                        <x-input-error class="mt-2" :messages="$errors->get('site_id')" />
                    </div>
                    <div>
                        <x-input-label for="party_id" :value="__('Suggested Supplier (optional)')" />
                        <div class="mt-1">
                            <x-searchable-select name="party_id" :options="$suppliers" :selected="old('party_id')"
                                                  placeholder="{{ __('Not decided yet…') }}" />
                        </div>
                        <x-input-error class="mt-2" :messages="$errors->get('party_id')" />
                    </div>
                    <div>
                        <x-input-label for="request_date" :value="__('Request Date')" />
                        <x-text-input id="request_date" name="request_date" type="date" class="mt-1 block w-full"
                                      :value="old('request_date', now()->toDateString())" required />
                        <x-input-error class="mt-2" :messages="$errors->get('request_date')" />
                    </div>
                    <div>
                        <x-input-label for="required_by_date" :value="__('Required By (optional)')" />
                        <x-text-input id="required_by_date" name="required_by_date" type="date" class="mt-1 block w-full"
                                      :value="old('required_by_date')" />
                        <x-input-error class="mt-2" :messages="$errors->get('required_by_date')" />
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
                <table class="w-full min-w-[600px] text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                            <th class="px-5 py-3 font-semibold">{{ __('Item') }}</th>
                            <th class="px-5 py-3 font-semibold w-32">{{ __('Quantity') }}</th>
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
                                <td class="px-5 py-3 text-right">
                                    <button type="button" @click="removeItem(item.id)" class="text-slate-400 hover:text-rose-600" title="{{ __('Remove') }}">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
                </div>
            </div>
            <x-input-error class="mb-4" :messages="$errors->get('items')" />

            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200" x-show="items.length" x-cloak>
                <div class="mb-5 max-w-xl">
                    <x-input-label for="note" :value="__('Note (optional)')" />
                    <textarea id="note" name="note" rows="2"
                              class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">{{ old('note') }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('note')" />
                </div>

                <button type="submit" :disabled="items.length === 0"
                        class="inline-flex items-center gap-2 rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-accent-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    {{ __('Submit Requisition') }}
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
