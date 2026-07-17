<x-app-layout>
    <x-slot name="title">{{ __('Stock History') }} — {{ $product->name }}</x-slot>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-brand-900">{{ __('Stock History') }}</h2>
                <p class="text-sm text-slate-500 mt-0.5">{{ $product->name }} ({{ $product->sku }}) — {{ __('every stock movement, newest first.') }}</p>
            </div>
            <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50">
                {{ __('Back to Products') }}
            </a>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6 items-start">
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <form method="GET" action="{{ route('products.history', $product) }}" class="flex flex-nowrap items-end gap-4">
                <div class="w-56 shrink-0">
                    <x-input-label for="site_id" :value="__('Site')" />
                    <div class="mt-1">
                        <x-searchable-select name="site_id" :options="$sites" :selected="$siteId"
                                              placeholder="{{ __('All sites') }}" :auto-submit="true" />
                    </div>
                </div>
            </form>
        </div>

        <div class="lg:col-span-2 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <x-input-label :value="__('Current Stock')" />
                    <p class="mt-1 text-2xl font-bold text-brand-900">
                        {{ rtrim(rtrim(number_format($totalStock, 4), '0'), '.') }}
                        <span class="text-sm font-medium text-slate-400">{{ $product->stockUnit->short_name ?? '' }}</span>
                    </p>
                </div>
                <div>
                    <x-input-label :value="__('Cost Valuation')" />
                    <p class="mt-1 text-2xl font-bold text-slate-700">{{ number_format($totalCostValuation, 2) }}</p>
                </div>
                <div>
                    <x-input-label :value="__('Sale Valuation')" />
                    <p class="mt-1 text-2xl font-bold text-emerald-700">{{ number_format($totalSaleValuation, 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    @if ($currentStock->isEmpty())
    <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 mb-6">
        <p class="text-sm text-slate-400">{{ __('No stock on hand.') }}</p>
    </div>
    @else
    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 overflow-hidden mb-6">
        <div class="overflow-x-auto">
        <table class="w-full min-w-[760px] text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <th class="px-5 py-3 font-semibold">{{ __('Site') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Variant') }}</th>
                    <th class="px-5 py-3 font-semibold text-right">{{ __('Qty') }}</th>
                    <th class="px-5 py-3 font-semibold text-right">{{ __('Unit Cost') }}</th>
                    <th class="px-5 py-3 font-semibold text-right">{{ __('Unit Sale Price') }}</th>
                    <th class="px-5 py-3 font-semibold text-right">{{ __('Cost Value') }}</th>
                    <th class="px-5 py-3 font-semibold text-right">{{ __('Sale Value') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($currentStock as $row)
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-3 text-slate-600">{{ $row->site }}</td>
                    <td class="px-5 py-3 text-slate-600">{{ $row->variant ?? '—' }}</td>
                    <td class="px-5 py-3 text-right tabular-nums font-semibold text-slate-800">{{ rtrim(rtrim(number_format($row->balance, 4), '0'), '.') }}</td>
                    <td class="px-5 py-3 text-right tabular-nums text-slate-600">{{ number_format($row->cost_price, 2) }}</td>
                    <td class="px-5 py-3 text-right tabular-nums text-slate-600">{{ number_format($row->sale_price, 2) }}</td>
                    <td class="px-5 py-3 text-right tabular-nums text-slate-700">{{ number_format($row->cost_valuation, 2) }}</td>
                    <td class="px-5 py-3 text-right tabular-nums font-semibold text-emerald-700">{{ number_format($row->sale_valuation, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>
    @endif

    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full min-w-[960px] text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <th class="px-5 py-3 font-semibold">{{ __('Date') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Type') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Variant') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Site') }}</th>
                    <th class="px-5 py-3 font-semibold text-right">{{ __('Quantity') }}</th>
                    <th class="px-5 py-3 font-semibold text-right">{{ __('Cost / Price') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Reference') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('By') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($movements as $movement)
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-3 text-slate-600 whitespace-nowrap">{{ $movement->moved_at->format('d M Y') }}</td>
                    <td class="px-5 py-3">
                        <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1
                            {{ $movement->direction === 'in' ? 'bg-emerald-50 text-emerald-600 ring-emerald-200' : 'bg-rose-50 text-rose-600 ring-rose-200' }}">
                            {{ $movement->direction === 'in' ? '↓' : '↑' }} {{ Str::headline($movement->type) }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-slate-600">{{ $movement->productVariant?->label ?? '—' }}</td>
                    <td class="px-5 py-3 text-slate-600">{{ $movement->site->name }}</td>
                    <td class="px-5 py-3 text-right tabular-nums font-semibold {{ $movement->direction === 'in' ? 'text-emerald-600' : 'text-rose-600' }}">
                        {{ $movement->direction === 'in' ? '+' : '-' }}{{ rtrim(rtrim(number_format($movement->quantity, 4), '0'), '.') }}
                    </td>
                    <td class="px-5 py-3 text-right tabular-nums">
                        @if ($movement->type === 'sale' && $movement->sale_price !== null)
                            <span class="font-semibold text-slate-800">{{ number_format($movement->sale_price, 2) }}</span>
                            <span class="block text-xs text-slate-400">{{ __('sale price') }}</span>
                        @elseif ($movement->unit_cost !== null)
                            <span class="font-semibold text-slate-800">{{ number_format($movement->unit_cost, 2) }}</span>
                            <span class="block text-xs text-slate-400">{{ __('cost') }}</span>
                        @else
                            <span class="text-slate-400">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-3">
                        @if ($movement->reference_url)
                            <a href="{{ $movement->reference_url }}" class="font-semibold text-brand-700 hover:underline">{{ $movement->reference_label }}</a>
                        @elseif ($movement->reason)
                            <span class="text-slate-600">{{ \App\Models\StockMovement::REASONS[$movement->reason] ?? $movement->reason }}</span>
                        @elseif ($movement->note)
                            <span class="text-slate-600">{{ $movement->note }}</span>
                        @else
                            <span class="text-slate-400">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-slate-500">{{ $movement->createdBy?->name ?? '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-5 py-10 text-center text-slate-400">
                        {{ __('No stock movements recorded for this product yet.') }}
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>

        @if ($movements->hasPages())
        <div class="border-t border-slate-100 px-5 py-4">
            {{ $movements->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
