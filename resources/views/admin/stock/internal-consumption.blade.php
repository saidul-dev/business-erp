<x-app-layout>
    <x-slot name="title">{{ __('Internal Consumption') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('Internal Consumption') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ __('Record stock used internally (office/self-use) — posts as an expense against Accounts.') }}</p>
        </div>
    </x-slot>

    <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 mb-6">
        <form method="GET" action="{{ route('stock.internal-consumption.index') }}" class="flex flex-nowrap items-end gap-4 overflow-x-auto">
            <div class="w-64 shrink-0">
                <x-input-label for="site_id" :value="__('Site')" />
                <div class="mt-1">
                    <x-searchable-select name="site_id" :options="$sites" :selected="$site?->id"
                                          placeholder="{{ __('Select a site…') }}" :auto-submit="true" />
                </div>
            </div>

            @if ($site)
            <div class="flex-1 min-w-[260px]">
                <x-input-label for="item" :value="__('Product / Variant')" />
                <div class="mt-1">
                    <x-searchable-select name="item" :options="$items" :selected="$selectedItem ?: null"
                                          placeholder="{{ __('Search a product or variant…') }}" :auto-submit="true" />
                </div>
            </div>
            @endif
        </form>
    </div>

    @if (! $site)
        <div class="rounded-2xl bg-white p-10 text-center text-slate-400 shadow-sm ring-1 ring-slate-200">
            {{ __('Pick a Site above to record internal consumption.') }}
        </div>
    @elseif (! $product)
        <div class="rounded-2xl bg-white p-10 text-center text-slate-400 shadow-sm ring-1 ring-slate-200">
            {{ __('Pick a product or variant above to record internal consumption.') }}
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4"
             x-data="{
                qty: {{ (float) old('quantity', 0) }},
                balance: {{ $balance }},
                get after() { return this.balance - (this.qty || 0); },
             }">

            <!-- Consumption Details -->
            <div class="lg:col-span-2 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <div class="mb-5">
                    <h3 class="font-bold text-brand-900">{{ __('Consumption Details') }}</h3>
                    <p class="text-xs text-slate-400">{{ __('How much was used, and why') }}</p>
                </div>

                <form method="POST" action="{{ route('stock.internal-consumption.store') }}" id="internal-consumption-form">
                    @csrf
                    <input type="hidden" name="site_id" value="{{ $site->id }}">
                    <input type="hidden" name="item" value="{{ $selectedItem }}">

                    <div class="mb-5 grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="quantity" :value="__('Quantity')" />
                            <input id="quantity" type="number" step="0.0001" min="0.0001" max="{{ $balance }}" name="quantity" x-model.number="qty"
                                   class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500" required>
                            <x-input-error class="mt-2" :messages="$errors->get('quantity')" />
                        </div>
                        <div>
                            <x-input-label for="moved_at" :value="__('Date')" />
                            <x-text-input id="moved_at" name="moved_at" type="date" class="mt-1 block w-full"
                                          :value="old('moved_at', now()->toDateString())" required />
                            <x-input-error class="mt-2" :messages="$errors->get('moved_at')" />
                        </div>
                    </div>

                    @if ($product->track_batch || $product->track_expiry || $product->track_serial)
                    <div class="mb-5 grid grid-cols-2 gap-4 sm:grid-cols-3">
                        @if ($product->track_batch)
                        <div>
                            <x-input-label for="batch_no" :value="__('Batch No.')" />
                            <input id="batch_no" type="text" name="batch_no" value="{{ old('batch_no') }}"
                                   class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">
                        </div>
                        @endif
                        @if ($product->track_expiry)
                        <div>
                            <x-input-label for="expiry_date" :value="__('Expiry Date')" />
                            <input id="expiry_date" type="date" name="expiry_date" value="{{ old('expiry_date') }}"
                                   class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">
                        </div>
                        @endif
                        @if ($product->track_serial)
                        <div>
                            <x-input-label for="serial_no" :value="__('Serial No.')" />
                            <input id="serial_no" type="text" name="serial_no" value="{{ old('serial_no') }}"
                                   class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">
                        </div>
                        @endif
                    </div>
                    @endif

                    <div>
                        <x-input-label for="note" :value="__('Reason / Purpose')" />
                        <textarea id="note" name="note" rows="2" required
                                  placeholder="{{ __('e.g. office use, staff distribution, sample for internal testing…') }}"
                                  class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">{{ old('note') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('note')" />
                    </div>
                </form>
            </div>

            <!-- Stock Summary -->
            <div class="lg:sticky lg:top-6 h-fit rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <div class="mb-4">
                    <h3 class="font-bold text-brand-900">{{ __('Stock Summary') }}</h3>
                    <p class="text-xs text-slate-400">{{ $site->name }}</p>
                </div>

                <div class="mb-4 border-b border-slate-100 pb-4">
                    <span class="block font-semibold text-slate-800">
                        {{ $product->name }}{{ $variant ? ' — '.$variant->label : '' }}
                    </span>
                    <span class="block text-xs text-slate-400">{{ $variant->sku ?? $product->sku }} · {{ $product->stockUnit?->short_name ?? __('unit') }}</span>
                </div>

                <div class="space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-xl bg-slate-50 px-4 py-3 ring-1 ring-slate-100">
                            <span class="block text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ __('Current Stock') }}</span>
                            <span class="block text-xl font-bold text-slate-800">{{ rtrim(rtrim(number_format($balance, 4), '0'), '.') ?: '0' }}</span>
                        </div>
                        <div class="rounded-xl bg-slate-50 px-4 py-3 ring-1 ring-slate-100">
                            <span class="block text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ __('Avg. Cost') }}</span>
                            <span class="block text-xl font-bold text-slate-800">{{ number_format($avgCost, 2) }}</span>
                        </div>
                    </div>

                    <div class="rounded-xl px-4 py-3 ring-1" :class="after < 0 ? 'bg-rose-50 ring-rose-100' : 'bg-brand-800/5 ring-brand-800/10'">
                        <span class="block text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ __('After Consumption') }}</span>
                        <span class="block text-2xl font-bold" :class="after < 0 ? 'text-rose-600' : 'text-brand-900'" x-text="after"></span>
                    </div>

                    @if ($avgCost > 0)
                    <p class="text-xs text-slate-400">
                        {{ __('Will post an :expense voucher of about', ['expense' => 'Internal Consumption']) }}
                        <span class="font-semibold text-slate-600" x-text="((qty || 0) * {{ $avgCost }}).toFixed(2)"></span>
                        {{ __('to Accounts.') }}
                    </p>
                    @endif
                </div>

                <button type="submit" form="internal-consumption-form"
                        class="mt-6 flex w-full items-center justify-center gap-2 rounded-lg bg-brand-800 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-accent-500 focus:ring-offset-2">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                    {{ __('Save Consumption') }}
                </button>
            </div>
        </div>
    @endif
</x-app-layout>
