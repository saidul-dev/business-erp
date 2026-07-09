<x-app-layout>
    <x-slot name="title">{{ __('Initial Stock') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('Initial Stock') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ __('One-time opening balance per Site — enter quantity for as many products as you like and save them together.') }}</p>
        </div>
    </x-slot>

    <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 mb-6 max-w-md">
        <form method="GET" action="{{ route('stock.initial.index') }}">
            <x-input-label for="site_id" :value="__('Site')" />
            <select id="site_id" name="site_id" onchange="this.form.submit()"
                    class="mt-1 block w-full rounded-lg border-slate-300 focus:border-accent-500 focus:ring-accent-500">
                <option value="">{{ __('Select a site…') }}</option>
                @foreach ($sites as $option)
                    <option value="{{ $option->id }}" @selected($site && $site->id === $option->id)>{{ $option->name }}</option>
                @endforeach
            </select>
        </form>
    </div>

    @if (! $site)
        <div class="rounded-2xl bg-white p-10 text-center text-slate-400 shadow-sm ring-1 ring-slate-200">
            {{ __('Pick a Site above to enter its opening stock.') }}
        </div>
    @elseif ($products->isEmpty())
        <div class="rounded-2xl bg-white p-10 text-center text-slate-400 shadow-sm ring-1 ring-slate-200">
            {{ __('Every active product already has opening stock recorded for :site. Use Adjustment to correct a quantity.', ['site' => $site->name]) }}
        </div>
    @else
        <form method="POST" action="{{ route('stock.initial.store') }}">
            @csrf
            <input type="hidden" name="site_id" value="{{ $site->id }}">

            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 mb-4 max-w-xs">
                <x-input-label for="moved_at" :value="__('As of Date')" />
                <x-text-input id="moved_at" name="moved_at" type="date" class="mt-1 block w-full"
                              :value="old('moved_at', now()->toDateString())" required />
                <x-input-error class="mt-2" :messages="$errors->get('moved_at')" />
            </div>

            <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                            <th class="px-5 py-3 font-semibold">{{ __('Product') }}</th>
                            <th class="px-5 py-3 font-semibold">{{ __('Unit') }}</th>
                            <th class="px-5 py-3 font-semibold w-32">{{ __('Quantity') }}</th>
                            <th class="px-5 py-3 font-semibold w-32">{{ __('Unit Cost') }}</th>
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
                                <input type="number" step="0.0001" min="0" name="rows[{{ $product->id }}][quantity]"
                                       value="{{ old("rows.{$product->id}.quantity") }}"
                                       class="w-28 rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500" placeholder="0">
                            </td>
                            <td class="px-5 py-3">
                                <input type="number" step="0.0001" min="0" name="rows[{{ $product->id }}][unit_cost]"
                                       value="{{ old("rows.{$product->id}.unit_cost", $product->estimated_cost) }}"
                                       class="w-28 rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500" placeholder="0.00">
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex flex-wrap gap-2">
                                    @if ($product->track_batch)
                                    <input type="text" name="rows[{{ $product->id }}][batch_no]"
                                           value="{{ old("rows.{$product->id}.batch_no") }}"
                                           placeholder="{{ __('Batch no.') }}"
                                           class="w-28 rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">
                                    @endif
                                    @if ($product->track_expiry)
                                    <input type="date" name="rows[{{ $product->id }}][expiry_date]"
                                           value="{{ old("rows.{$product->id}.expiry_date") }}"
                                           class="w-36 rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">
                                    @endif
                                    @if ($product->track_serial)
                                    <input type="text" name="rows[{{ $product->id }}][serial_no]"
                                           value="{{ old("rows.{$product->id}.serial_no") }}"
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

                <div class="border-t border-slate-100 px-5 py-4">
                    <x-primary-button>{{ __('Save Opening Stock') }}</x-primary-button>
                    <p class="mt-2 text-xs text-slate-400">{{ __('Leave quantity blank (or 0) to skip a product — you can come back for it later.') }}</p>
                </div>
            </div>
        </form>
    @endif
</x-app-layout>
