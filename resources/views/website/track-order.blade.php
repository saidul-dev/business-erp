<x-website-layout :title="__('Track Order')">

    <section class="bg-white py-16">
        <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-3xl font-extrabold text-brand-950">{{ __('Track Your Order') }}</h1>
                <p class="mt-2 text-slate-500">{{ __('Enter your order number and the phone number you checked out with.') }}</p>
            </div>

            <form method="POST" action="{{ route('track-order.result') }}" class="mt-8 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 space-y-4">
                @csrf
                <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Order Number') }}</label>
                    <input type="text" name="sale_no" value="{{ old('sale_no') }}" placeholder="SO-000123" required
                           class="w-full rounded-lg border-slate-300 focus:border-accent-500 focus:ring-accent-500">
                    @error('sale_no') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Phone Number') }}</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" required
                           class="w-full rounded-lg border-slate-300 focus:border-accent-500 focus:ring-accent-500">
                </div>
                <button type="submit" class="w-full rounded-lg bg-brand-900 px-6 py-3 text-sm font-semibold text-white hover:bg-brand-800">
                    {{ __('Track Order') }}
                </button>
            </form>

            @isset($sale)
            <div class="mt-8 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <p class="font-semibold text-brand-950">{{ $sale->sale_no }}</p>
                        <p class="text-sm text-slate-500">{{ $sale->order_date->format('d M, Y') }}</p>
                    </div>
                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold
                        {{ match($sale->status) {
                            'pending' => 'bg-slate-100 text-slate-600',
                            'partial' => 'bg-amber-100 text-amber-700',
                            'delivered' => 'bg-emerald-100 text-emerald-700',
                            'cancelled' => 'bg-rose-100 text-rose-700',
                            default => 'bg-slate-100 text-slate-600',
                        } }}">
                        {{ ucfirst($sale->status) }}
                    </span>
                </div>

                <div class="divide-y divide-slate-100">
                    @foreach ($sale->items as $item)
                    <div class="flex items-center justify-between gap-3 py-3 text-sm">
                        <span class="text-slate-600">
                            {{ $item->product->name }}{{ $item->productVariant ? ' — '.$item->productVariant->label : '' }}
                            × {{ (int) $item->quantity }}
                        </span>
                        <span class="font-semibold text-slate-800">{{ number_format($item->subtotal, 2) }}</span>
                    </div>
                    @endforeach
                </div>

                <div class="mt-2 flex items-center justify-between border-t border-slate-100 pt-4">
                    <span class="font-semibold text-slate-700">{{ __('Total') }}</span>
                    <span class="text-xl font-bold text-brand-950">{{ number_format($sale->total_amount, 2) }}</span>
                </div>

                @if ($sale->delivery_zone_name)
                <div class="mt-3 flex items-center justify-between rounded-lg bg-amber-50 px-3 py-2 text-sm ring-1 ring-amber-200">
                    <span class="text-amber-700">{{ __('Delivery (:zone) — pay courier separately', ['zone' => $sale->delivery_zone_name]) }}</span>
                    <span class="font-semibold text-amber-800">{{ number_format($sale->delivery_charge, 2) }}</span>
                </div>
                @endif

                @if ($sale->deliveries->isNotEmpty())
                <div class="mt-5 border-t border-slate-100 pt-4">
                    <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Delivery') }}</p>
                    @foreach ($sale->deliveries as $delivery)
                    <div class="text-sm text-slate-600">
                        {{ $delivery->delivery_no }} — {{ $delivery->delivered_date->format('d M, Y') }}
                        @if ($delivery->consignment)
                            {{ __('via') }} {{ $delivery->consignment->deliveryPartner->name }}
                            @if ($delivery->consignment->tracking_no)
                                ({{ __('Tracking') }}: {{ $delivery->consignment->tracking_no }})
                            @endif
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
            @endisset
        </div>
    </section>

</x-website-layout>
