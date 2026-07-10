<x-app-layout>
    <x-slot name="title">{{ __('Deliver') }} — {{ $sale->sale_no }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('Deliver Items') }} — {{ $sale->sale_no }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ __('Check off what actually goes out — leave a line at 0 to deliver it later. Quantity, revenue and COGS are posted immediately for whatever you enter here.') }}</p>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('sales.deliver.store', $sale) }}" x-data="{ fillRemaining() { this.$refs.form.querySelectorAll('[data-remaining]').forEach(el => { el.value = el.dataset.remaining; }); } }" x-ref="form">
        @csrf

        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 mb-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-w-xl">
                <div>
                    <x-input-label for="delivered_date" :value="__('Delivered Date')" />
                    <x-text-input id="delivered_date" name="delivered_date" type="date" class="mt-1 block w-full"
                                  :value="old('delivered_date', now()->toDateString())" required />
                    <x-input-error class="mt-2" :messages="$errors->get('delivered_date')" />
                </div>
            </div>
        </div>

        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 overflow-hidden mb-4">
            <div class="flex items-center justify-between px-5 py-3 border-b border-slate-100">
                <h3 class="font-bold text-brand-900">{{ __('Items') }}</h3>
                <button type="button" @click="fillRemaining()" class="text-xs font-semibold text-accent-600 hover:text-accent-800">{{ __('Fill all remaining') }}</button>
            </div>
            <div class="overflow-x-auto">
            <table class="w-full min-w-[860px] text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                        <th class="px-5 py-3 font-semibold">{{ __('Item') }}</th>
                        <th class="px-5 py-3 font-semibold text-right">{{ __('Ordered') }}</th>
                        <th class="px-5 py-3 font-semibold text-right">{{ __('Remaining') }}</th>
                        <th class="px-5 py-3 font-semibold w-32">{{ __('Deliver Now') }}</th>
                        <th class="px-5 py-3 font-semibold">{{ __('Batch / Expiry / Serial') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($sale->items as $item)
                    @php $remaining = $item->remaining(); @endphp
                    <tr class="{{ $remaining <= 0 ? 'opacity-50' : '' }} hover:bg-slate-50">
                        <td class="px-5 py-3">
                            <span class="block font-semibold text-slate-800">
                                {{ $item->product->name }}{{ $item->productVariant ? ' — '.$item->productVariant->label : '' }}
                            </span>
                            <span class="block text-xs text-slate-400">{{ $item->productVariant->sku ?? $item->product->sku }}</span>
                        </td>
                        <td class="px-5 py-3 text-right text-slate-600">{{ rtrim(rtrim(number_format($item->quantity, 4), '0'), '.') ?: '0' }}</td>
                        <td class="px-5 py-3 text-right font-semibold {{ $remaining > 0 ? 'text-amber-600' : 'text-emerald-600' }}">{{ rtrim(rtrim(number_format($remaining, 4), '0'), '.') ?: '0' }}</td>
                        <td class="px-5 py-3">
                            <input type="number" step="0.0001" min="0" max="{{ $remaining }}" data-remaining="{{ $remaining }}"
                                   name="items[{{ $item->id }}][quantity]" {{ $remaining <= 0 ? 'disabled' : '' }}
                                   class="w-28 rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500" placeholder="0">
                            <x-input-error class="mt-1" :messages="$errors->get('items.'.$item->id.'.quantity')" />
                        </td>
                        <td class="px-5 py-3">
                            @if ($remaining > 0 && ($item->product->track_batch || $item->product->track_expiry || $item->product->track_serial))
                            <div class="flex flex-wrap gap-2">
                                @if ($item->product->track_batch)
                                <input type="text" name="items[{{ $item->id }}][batch_no]" placeholder="{{ __('Batch no.') }}"
                                       class="w-28 rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">
                                @endif
                                @if ($item->product->track_expiry)
                                <input type="date" name="items[{{ $item->id }}][expiry_date]"
                                       class="w-36 rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">
                                @endif
                                @if ($item->product->track_serial)
                                <input type="text" name="items[{{ $item->id }}][serial_no]" placeholder="{{ __('Serial no.') }}"
                                       class="w-28 rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">
                                @endif
                            </div>
                            @else
                            <span class="text-slate-300">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>
        <x-input-error class="mb-4" :messages="$errors->get('items')" />

        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <div class="mb-5 max-w-xl">
                <x-input-label for="note" :value="__('Note (optional)')" />
                <textarea id="note" name="note" rows="2"
                          class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">{{ old('note') }}</textarea>
                <x-input-error class="mt-2" :messages="$errors->get('note')" />
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-accent-500 focus:ring-offset-2">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                    {{ __('Post Delivery') }}
                </button>
                <a href="{{ route('sales.show', $sale) }}" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50">{{ __('Cancel') }}</a>
            </div>
        </div>
    </form>
</x-app-layout>
