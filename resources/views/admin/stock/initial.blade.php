<x-app-layout>
    <x-slot name="title">{{ __('Initial Stock') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('Initial Stock') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ __('One-time opening balance per Site — enter quantity for as many products as you like and save them together.') }}</p>
        </div>
    </x-slot>

    @php $hasItems = $site && ($products->isNotEmpty() || $variantGroups->isNotEmpty()); @endphp

    <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 mb-6">
        <div class="flex flex-nowrap items-end gap-4 overflow-x-auto">
            <form method="GET" action="{{ route('stock.initial.index') }}" class="w-64 shrink-0">
                <x-input-label for="site_id" :value="__('Site')" />
                <div class="mt-1">
                    <x-searchable-select name="site_id" :options="$sites" :selected="$site?->id"
                                          placeholder="{{ __('Select a site…') }}" :auto-submit="true" />
                </div>
            </form>

            @if ($hasItems)
            <div class="w-44 shrink-0">
                <x-input-label for="moved_at" :value="__('As of Date')" />
                <x-text-input id="moved_at" name="moved_at" type="date" form="initial-stock-form" class="mt-1 block w-full"
                              :value="old('moved_at', now()->toDateString())" required />
                <x-input-error class="mt-2" :messages="$errors->get('moved_at')" />
            </div>
            @endif
        </div>
    </div>

    @if (! $site)
        <div class="rounded-2xl bg-white p-10 text-center text-slate-400 shadow-sm ring-1 ring-slate-200">
            {{ __('Pick a Site above to enter its opening stock.') }}
        </div>
    @elseif (! $hasItems)
        <div class="rounded-2xl bg-white p-10 text-center text-slate-400 shadow-sm ring-1 ring-slate-200">
            {{ __('Every active product already has stock movement history at :site (opening stock or otherwise). Use Adjustment to correct a quantity.', ['site' => $site->name]) }}
        </div>
    @else
        <form method="POST" action="{{ route('stock.initial.store') }}" id="initial-stock-form">
            @csrf
            <input type="hidden" name="site_id" value="{{ $site->id }}">

            @if ($products->isNotEmpty())
            <h3 class="mb-2 text-sm font-bold text-brand-900">{{ __('Products') }}</h3>
            <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 overflow-hidden mb-6">
                <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                            <th class="px-5 py-3 font-semibold">{{ __('Product') }}</th>
                            <th class="px-5 py-3 font-semibold">{{ __('Unit') }}</th>
                            <th class="px-5 py-3 font-semibold w-32">{{ __('Quantity') }}</th>
                            <th class="px-5 py-3 font-semibold w-36">
                                {{ __('Unit Cost') }}
                                <span class="block normal-case text-[10px] font-medium tracking-normal text-slate-400">{{ __('required if qty > 0') }}</span>
                            </th>
                            <th class="px-5 py-3 font-semibold">{{ __('Batch / Expiry / Serial') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($products as $product)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3">
                                <span class="block font-semibold text-slate-800">{{ $product->name }}</span>
                                <span class="block text-xs text-slate-400">{{ $product->sku }}</span>
                            </td>
                            <td class="px-5 py-3 text-slate-600">{{ $product->stockUnit->short_name ?? '—' }}</td>
                            <td class="px-5 py-3">
                                <input type="number" step="0.0001" min="0" name="products[{{ $product->id }}][quantity]"
                                       value="{{ old("products.{$product->id}.quantity") }}"
                                       class="w-28 rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500" placeholder="0">
                            </td>
                            <td class="px-5 py-3">
                                <input type="number" step="0.0001" min="0" name="products[{{ $product->id }}][unit_cost]"
                                       value="{{ old("products.{$product->id}.unit_cost", rtrim(rtrim(number_format($product->estimated_cost, 4), '0'), '.')) }}"
                                       class="w-28 rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500" placeholder="0.00">
                                <x-input-error class="mt-1" :messages="$errors->get('products.'.$product->id.'.unit_cost')" />
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex flex-wrap gap-2">
                                    @if ($product->track_batch)
                                    <input type="text" name="products[{{ $product->id }}][batch_no]"
                                           value="{{ old("products.{$product->id}.batch_no") }}"
                                           placeholder="{{ __('Batch no.') }}"
                                           class="w-28 rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">
                                    @endif
                                    @if ($product->track_expiry)
                                    <input type="date" name="products[{{ $product->id }}][expiry_date]"
                                           value="{{ old("products.{$product->id}.expiry_date") }}"
                                           class="w-36 rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">
                                    @endif
                                    @if ($product->track_serial)
                                    <input type="text" name="products[{{ $product->id }}][serial_no]"
                                           value="{{ old("products.{$product->id}.serial_no") }}"
                                           placeholder="{{ __('Serial no.') }}"
                                           class="w-28 rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">
                                    @endif
                                    @unless ($product->track_batch || $product->track_expiry || $product->track_serial)
                                    <span class="text-slate-300">—</span>
                                    @endunless
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
            </div>
            @endif

            @if ($variantGroups->isNotEmpty())
            <h3 class="mb-2 text-sm font-bold text-brand-900">{{ __('Product Variants') }}</h3>
            <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 overflow-hidden mb-6">
                <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                            <th class="px-5 py-3 font-semibold">{{ __('Product / Variant') }}</th>
                            <th class="px-5 py-3 font-semibold">{{ __('Unit') }}</th>
                            <th class="px-5 py-3 font-semibold w-32">{{ __('Quantity') }}</th>
                            <th class="px-5 py-3 font-semibold w-36">
                                {{ __('Unit Cost') }}
                                <span class="block normal-case text-[10px] font-medium tracking-normal text-slate-400">{{ __('required if qty > 0') }}</span>
                            </th>
                            <th class="px-5 py-3 font-semibold">{{ __('Batch / Expiry / Serial') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($variantGroups as $product)
                        @foreach ($product->variants as $variant)
                        @php $variantCost = $variant->estimated_cost ?? $product->estimated_cost; @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3">
                                <span class="block font-semibold text-slate-800">{{ $product->name }} — {{ $variant->label }}</span>
                                <span class="block text-xs text-slate-400">{{ $variant->sku }}</span>
                            </td>
                            <td class="px-5 py-3 text-slate-600">{{ $product->stockUnit->short_name ?? '—' }}</td>
                            <td class="px-5 py-3">
                                <input type="number" step="0.0001" min="0" name="variants[{{ $variant->id }}][quantity]"
                                       value="{{ old("variants.{$variant->id}.quantity") }}"
                                       class="w-28 rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500" placeholder="0">
                            </td>
                            <td class="px-5 py-3">
                                <input type="number" step="0.0001" min="0" name="variants[{{ $variant->id }}][unit_cost]"
                                       value="{{ old("variants.{$variant->id}.unit_cost", rtrim(rtrim(number_format($variantCost, 4), '0'), '.')) }}"
                                       class="w-28 rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500" placeholder="0.00">
                                <x-input-error class="mt-1" :messages="$errors->get('variants.'.$variant->id.'.unit_cost')" />
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex flex-wrap gap-2">
                                    @if ($product->track_batch)
                                    <input type="text" name="variants[{{ $variant->id }}][batch_no]"
                                           value="{{ old("variants.{$variant->id}.batch_no") }}"
                                           placeholder="{{ __('Batch no.') }}"
                                           class="w-28 rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">
                                    @endif
                                    @if ($product->track_expiry)
                                    <input type="date" name="variants[{{ $variant->id }}][expiry_date]"
                                           value="{{ old("variants.{$variant->id}.expiry_date") }}"
                                           class="w-36 rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">
                                    @endif
                                    @if ($product->track_serial)
                                    <input type="text" name="variants[{{ $variant->id }}][serial_no]"
                                           value="{{ old("variants.{$variant->id}.serial_no") }}"
                                           placeholder="{{ __('Serial no.') }}"
                                           class="w-28 rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">
                                    @endif
                                    @unless ($product->track_batch || $product->track_expiry || $product->track_serial)
                                    <span class="text-slate-300">—</span>
                                    @endunless
                                </div>
                            </td>
                        </tr>
                        @endforeach
                        @endforeach
                    </tbody>
                </table>
                </div>
            </div>
            @endif

            <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 px-5 py-4">
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-accent-500 focus:ring-offset-2">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                    {{ __('Save Opening Stock') }}
                </button>
                <p class="mt-2 text-xs text-slate-400">{{ __('Leave quantity blank (or 0) to skip an item — you can come back for it later.') }}</p>
            </div>
        </form>
    @endif
</x-app-layout>
