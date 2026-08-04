<x-app-layout>
    <x-slot name="title">{{ __('Stock Report') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('Stock Report') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ __('Current stock per product at a Branch.') }}</p>
        </div>
    </x-slot>

    <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 mb-6">
        <form method="GET" action="{{ route('stock.report') }}" class="flex flex-nowrap items-end gap-4 overflow-x-auto">
            <div class="w-56 shrink-0">
                <x-input-label for="branch_id" :value="__('Branch')" />
                <div class="mt-1">
                    <x-searchable-select name="branch_id" :options="$branches" :selected="$branch?->id"
                                          placeholder="{{ __('Select a branch…') }}" :auto-submit="true" />
                </div>
            </div>

            @if ($branch)
            <div class="w-56 shrink-0">
                <x-input-label for="category_id" :value="__('Category')" />
                <div class="mt-1">
                    <x-searchable-select name="category_id" :options="$categories" :selected="request('category_id')"
                                          placeholder="{{ __('All categories') }}" />
                </div>
            </div>
            @php $currentView = request('view') === 'summary' ? 'summary' : 'detail'; @endphp
            <div class="w-44 shrink-0">
                <x-input-label :value="__('View')" />
                <div class="mt-1 flex h-[38px] w-full items-stretch rounded-lg border border-slate-300 bg-slate-50 p-0.5 text-sm">
                    <a href="{{ request()->fullUrlWithQuery(['view' => 'detail', 'page' => null]) }}"
                       class="flex flex-1 items-center justify-center whitespace-nowrap rounded-md px-2.5 text-center font-medium transition-colors {{ $currentView === 'detail' ? 'bg-brand-800 text-white shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">
                        {{ __('Variants') }}
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['view' => 'summary', 'page' => null]) }}"
                       class="flex flex-1 items-center justify-center whitespace-nowrap rounded-md px-2.5 text-center font-medium transition-colors {{ $currentView === 'summary' ? 'bg-brand-800 text-white shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">
                        {{ __('Parent') }}
                    </a>
                </div>
            </div>
            <div class="w-48 shrink-0">
                <x-input-label :value="__('Availability')" />
                <div class="mt-1 flex h-[38px] w-full items-stretch rounded-lg border border-slate-300 bg-slate-50 p-0.5 text-sm">
                    <a href="{{ request()->fullUrlWithQuery(['availability' => 'in', 'page' => null]) }}"
                       class="flex flex-1 items-center justify-center whitespace-nowrap rounded-md px-2.5 text-center font-medium transition-colors {{ $availability === 'in' ? 'bg-brand-800 text-white shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">
                        {{ __('In Stock') }}
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['availability' => 'out', 'page' => null]) }}"
                       class="flex flex-1 items-center justify-center whitespace-nowrap rounded-md px-2.5 text-center font-medium transition-colors {{ $availability === 'out' ? 'bg-brand-800 text-white shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">
                        {{ __('Out of Stock') }}
                    </a>
                </div>
            </div>
            <div class="flex-1 min-w-[200px]">
                <x-input-label for="q" :value="__('Search')" />
                <input type="search" id="q" name="q" value="{{ request('q') }}" placeholder="{{ __('Name or SKU…') }}"
                       class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">
            </div>
            <button type="submit" class="shrink-0 rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">{{ __('Filter') }}</button>
            @endif
        </form>
    </div>

    @if (! $branch)
        <div class="rounded-2xl bg-white p-10 text-center text-slate-400 shadow-sm ring-1 ring-slate-200">
            {{ __('Pick a Branch above to see its stock.') }}
        </div>
    @else
        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
            <table class="w-full min-w-[1080px] text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                        <th class="px-5 py-3 font-semibold">{{ __('Product / Variant') }}</th>
                        <th class="px-5 py-3 font-semibold">{{ __('Category') }}</th>
                        <th class="px-5 py-3 font-semibold">{{ __('Unit') }}</th>
                        <th class="px-5 py-3 font-semibold text-right">{{ __('Quantity') }}</th>
                        <th class="px-5 py-3 font-semibold text-right">{{ __('Cost Valuation') }}</th>
                        <th class="px-5 py-3 font-semibold text-right">{{ __('Sale Valuation') }}</th>
                        <th class="px-5 py-3 font-semibold">{{ __('Status') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @php $pageCostTotal = 0; $pageSaleTotal = 0; @endphp
                    @forelse ($products as $row)
                    @php
                        $qty = $row->balance;
                        $costValuation = $row->cost_valuation;
                        $saleValuation = $row->sale_valuation;
                        $pageCostTotal += $costValuation;
                        $pageSaleTotal += $saleValuation;
                    @endphp
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3">
                            <span class="block font-semibold text-slate-800">{{ $row->name }}</span>
                            <span class="block text-xs text-slate-400">
                                {{ $row->sku }}
                                @if ($row->is_group)
                                    · {{ trans_choice(':count variant|:count variants', $row->variant_count, ['count' => $row->variant_count]) }}
                                @endif
                            </span>
                        </td>
                        <td class="px-5 py-3 text-slate-600">{{ $row->category ?? '—' }}</td>
                        <td class="px-5 py-3 text-slate-600">{{ $row->unit ?? '—' }}</td>
                        <td class="px-5 py-3 text-right font-semibold text-slate-800">{{ rtrim(rtrim(number_format($qty, 4), '0'), '.') ?: '0' }}</td>
                        <td class="px-5 py-3 text-right text-slate-600">{{ number_format($costValuation, 2) }}</td>
                        <td class="px-5 py-3 text-right text-slate-600">{{ number_format($saleValuation, 2) }}</td>
                        <td class="px-5 py-3">
                            @if ($qty <= 0)
                                <span class="inline-flex rounded-full bg-rose-50 px-2.5 py-0.5 text-xs font-semibold text-rose-600 ring-1 ring-rose-200">{{ __('Out of Stock') }}</span>
                            @elseif ($qty <= $row->reorder_level)
                                <span class="inline-flex rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-600 ring-1 ring-amber-200">{{ __('Low Stock') }}</span>
                            @else
                                <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-600 ring-1 ring-emerald-200">{{ __('In Stock') }}</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-10 text-center text-slate-400">{{ __('No products found.') }}</td>
                    </tr>
                    @endforelse
                </tbody>
                @if ($products->isNotEmpty())
                <tfoot>
                    <tr class="border-t border-slate-200 bg-slate-50">
                        <td colspan="3" class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Page Total') }}</td>
                        <td class="px-5 py-3"></td>
                        <td class="px-5 py-3 text-right font-semibold text-slate-800">{{ number_format($pageCostTotal, 2) }}</td>
                        <td class="px-5 py-3 text-right font-semibold text-slate-800">{{ number_format($pageSaleTotal, 2) }}</td>
                        <td class="px-5 py-3"></td>
                    </tr>
                </tfoot>
                @endif
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
