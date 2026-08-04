<x-app-layout>
    <x-slot name="title">{{ __('New Stock Transfer') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('New Stock Transfer') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ __('Send stock from one Branch to another — it stays in transit until the destination confirms receipt.') }}</p>
        </div>
    </x-slot>

    <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 mb-6">
        <form method="GET" action="{{ route('stock.transfers.create') }}" class="flex flex-nowrap items-end gap-4 overflow-x-auto">
            <div class="w-56 shrink-0">
                <x-input-label for="from_branch_id" :value="__('From Branch')" />
                <div class="mt-1">
                    <x-searchable-select name="from_branch_id" :options="$branches" :selected="$fromBranch?->id"
                                          placeholder="{{ __('Select a branch…') }}" :auto-submit="true" />
                </div>
            </div>
            <div class="w-56 shrink-0">
                <x-input-label for="to_branch_id" :value="__('To Branch')" />
                <div class="mt-1">
                    <x-searchable-select name="to_branch_id" :options="$branches" :selected="$toBranch?->id"
                                          placeholder="{{ __('Select a branch…') }}" :auto-submit="true" />
                </div>
            </div>
        </form>
        @if ($fromBranch && $toBranch && $fromBranch->id === $toBranch->id)
        <p class="mt-3 text-sm font-medium text-rose-600">{{ __('From Branch and To Branch must be different.') }}</p>
        @endif
    </div>

    @if (! $fromBranch)
        <div class="rounded-2xl bg-white p-10 text-center text-slate-400 shadow-sm ring-1 ring-slate-200">
            {{ __('Pick a From Branch above to see what\'s available to transfer.') }}
        </div>
    @else
        <div x-data="transferCart(@js(['itemOptions' => $itemOptions]))">
            <form method="POST" action="{{ route('stock.transfers.store') }}" x-ref="form">
                @csrf
                <input type="hidden" name="from_branch_id" value="{{ $fromBranch->id }}">
                <input type="hidden" name="to_branch_id" value="{{ $toBranch?->id }}">

                <!-- Item picker -->
                <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 mb-4">
                    <x-input-label :value="__('Add Item')" />
                    <div class="relative mt-1 max-w-md" @click.outside="open = false">
                        <input type="text" x-model="query" @focus="open = true"
                               placeholder="{{ __('Search a product or variant with stock…') }}"
                               class="block w-full rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">
                        <div x-show="open" x-cloak x-transition
                             class="absolute z-20 mt-1 w-full max-h-64 overflow-y-auto rounded-xl bg-white py-1.5 shadow-lg ring-1 ring-slate-200">
                            <template x-for="opt in filtered" :key="opt.id">
                                <div @click="addItem(opt)"
                                     class="flex cursor-pointer items-center justify-between gap-3 px-4 py-2 text-sm hover:bg-slate-50">
                                    <span x-text="opt.name" class="text-slate-700"></span>
                                    <span class="shrink-0 text-xs text-slate-400" x-text="opt.balance + ' ' + (opt.unit || '')"></span>
                                </div>
                            </template>
                            <div x-show="filtered.length === 0" class="px-4 py-2 text-sm text-slate-400">{{ __('No matching items with stock.') }}</div>
                        </div>
                    </div>
                    @if ($itemOptions->isEmpty())
                    <p class="mt-2 text-xs text-amber-600">{{ __('Nothing at :branch currently has stock to transfer.', ['branch' => $fromBranch->name]) }}</p>
                    @endif
                </div>

                <!-- Cart -->
                <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 overflow-hidden mb-4" x-show="items.length" x-cloak>
                    <div class="overflow-x-auto">
                    <table class="w-full min-w-[900px] text-sm">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                                <th class="px-5 py-3 font-semibold">{{ __('Item') }}</th>
                                <th class="px-5 py-3 font-semibold w-28">{{ __('Available') }}</th>
                                <th class="px-5 py-3 font-semibold w-32">{{ __('Quantity') }}</th>
                                <th class="px-5 py-3 font-semibold">{{ __('Batch / Expiry / Serial') }}</th>
                                <th class="px-5 py-3 font-semibold w-16"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="(item, i) in items" :key="item.id">
                                <tr class="hover:bg-slate-50">
                                    <td class="px-5 py-3">
                                        <input type="hidden" :name="'items[' + i + '][item]'" :value="item.id">
                                        <span class="font-semibold text-slate-800" x-text="item.name"></span>
                                    </td>
                                    <td class="px-5 py-3 text-slate-600" x-text="item.balance + ' ' + (item.unit || '')"></td>
                                    <td class="px-5 py-3">
                                        <input type="number" step="0.0001" min="0.0001" :max="item.balance"
                                               :name="'items[' + i + '][quantity]'" x-model.number="item.quantity"
                                               class="w-28 rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500" placeholder="0" required>
                                    </td>
                                    <td class="px-5 py-3">
                                        <div class="flex flex-wrap gap-2">
                                            <template x-if="item.trackBatch">
                                                <input type="text" :name="'items[' + i + '][batch_no]'" x-model="item.batch_no"
                                                       placeholder="{{ __('Batch no.') }}"
                                                       class="w-28 rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">
                                            </template>
                                            <template x-if="item.trackExpiry">
                                                <input type="date" :name="'items[' + i + '][expiry_date]'" x-model="item.expiry_date"
                                                       class="w-36 rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">
                                            </template>
                                            <template x-if="item.trackSerial">
                                                <input type="text" :name="'items[' + i + '][serial_no]'" x-model="item.serial_no"
                                                       placeholder="{{ __('Serial no.') }}"
                                                       class="w-28 rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">
                                            </template>
                                            <template x-if="!item.trackBatch && !item.trackExpiry && !item.trackSerial">
                                                <span class="text-slate-300">—</span>
                                            </template>
                                        </div>
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
                    <div class="mb-4 grid grid-cols-1 sm:grid-cols-2 gap-4 max-w-xl">
                        <div>
                            <x-input-label for="dispatched_at" :value="__('Dispatch Date')" />
                            <x-text-input id="dispatched_at" name="dispatched_at" type="date" class="mt-1 block w-full"
                                          :value="old('dispatched_at', now()->toDateString())" required />
                            <x-input-error class="mt-2" :messages="$errors->get('dispatched_at')" />
                        </div>
                    </div>
                    <div class="mb-5 max-w-xl">
                        <x-input-label for="note" :value="__('Note (optional)')" />
                        <textarea id="note" name="note" rows="2"
                                  class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">{{ old('note') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('note')" />
                    </div>

                    <button type="submit" :disabled="{{ $toBranch ? 'false' : 'true' }} || items.length === 0"
                            class="inline-flex items-center gap-2 rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-accent-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                        {{ __('Dispatch Transfer') }}
                    </button>
                    @unless ($toBranch)
                    <p class="mt-2 text-xs text-amber-600">{{ __('Pick a To Branch above before dispatching.') }}</p>
                    @endunless
                </div>
            </form>
        </div>
    @endif
</x-app-layout>
