<x-app-layout>
    <x-slot name="title">{{ __('Stock Report') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('Stock Report') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ __('Current stock per product at a Site.') }}</p>
        </div>
    </x-slot>

    <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 mb-6">
        <form method="GET" action="{{ route('stock.report') }}" class="flex flex-wrap items-end gap-4">
            <div class="w-full max-w-xs">
                <x-input-label for="site_id" :value="__('Site')" />
                <select id="site_id" name="site_id" onchange="this.form.submit()"
                        class="mt-1 block w-full rounded-lg border-slate-300 focus:border-accent-500 focus:ring-accent-500">
                    <option value="">{{ __('Select a site…') }}</option>
                    @foreach ($sites as $option)
                        <option value="{{ $option->id }}" @selected($site && $site->id === $option->id)>{{ $option->name }}</option>
                    @endforeach
                </select>
            </div>

            @if ($site)
            <div class="flex-1 min-w-[220px]">
                <x-input-label for="q" :value="__('Search')" />
                <input type="search" id="q" name="q" value="{{ request('q') }}" placeholder="{{ __('Name or SKU…') }}"
                       class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">
            </div>
            <button type="submit" class="rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">{{ __('Filter') }}</button>
            @endif
        </form>
    </div>

    @if (! $site)
        <div class="rounded-2xl bg-white p-10 text-center text-slate-400 shadow-sm ring-1 ring-slate-200">
            {{ __('Pick a Site above to see its stock.') }}
        </div>
    @else
        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
            <table class="w-full min-w-[720px] text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                        <th class="px-5 py-3 font-semibold">{{ __('Product') }}</th>
                        <th class="px-5 py-3 font-semibold">{{ __('Unit') }}</th>
                        <th class="px-5 py-3 font-semibold text-right">{{ __('Quantity') }}</th>
                        <th class="px-5 py-3 font-semibold">{{ __('Status') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($products as $product)
                    @php $qty = (float) ($stock[$product->id] ?? 0); @endphp
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3">
                            <span class="block font-semibold text-slate-800">{{ $product->name }}</span>
                            <span class="block text-xs text-slate-400">{{ $product->sku }}</span>
                        </td>
                        <td class="px-5 py-3 text-slate-600">{{ $product->stockUnit->short_name ?? '—' }}</td>
                        <td class="px-5 py-3 text-right font-semibold text-slate-800">{{ rtrim(rtrim(number_format($qty, 4), '0'), '.') ?: '0' }}</td>
                        <td class="px-5 py-3">
                            @if ($qty <= 0)
                                <span class="inline-flex rounded-full bg-rose-50 px-2.5 py-0.5 text-xs font-semibold text-rose-600 ring-1 ring-rose-200">{{ __('Out of Stock') }}</span>
                            @elseif ($qty <= $product->reorder_level)
                                <span class="inline-flex rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-600 ring-1 ring-amber-200">{{ __('Low Stock') }}</span>
                            @else
                                <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-600 ring-1 ring-emerald-200">{{ __('In Stock') }}</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-5 py-10 text-center text-slate-400">{{ __('No products found.') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            </div>

            @if ($products->hasPages())
            <div class="border-t border-slate-100 px-5 py-4">
                {{ $products->links() }}
            </div>
            @endif
        </div>
    @endif
</x-app-layout>
