<x-website-layout :title="__('Order Confirmed')">

    <section class="bg-white py-16">
        <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8 text-center">
            @if (session('error'))
            <div class="mb-6 flex items-center gap-3 rounded-xl bg-rose-50 px-4 py-3 text-left text-sm font-medium text-rose-700 ring-1 ring-rose-200">
                <span>{{ session('error') }}</span>
            </div>
            @endif

            <div class="mx-auto grid h-16 w-16 place-items-center rounded-full bg-emerald-100 text-emerald-600">
                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
            </div>
            <h1 class="mt-5 text-3xl font-extrabold text-brand-950">{{ __('Thank you for your order!') }}</h1>
            <p class="mt-2 text-slate-500">{{ __('Your order number is') }}</p>
            <p class="mt-1 text-2xl font-bold text-accent-600">{{ $sale->sale_no }}</p>
            <p class="mt-4 text-sm text-slate-500">
                {{ __("Save this order number and the phone number you checked out with — you'll need both to") }}
                <a href="{{ route('track-order') }}" class="font-semibold text-accent-600 hover:text-accent-800">{{ __('track your order') }}</a> {{ __('later.') }}
            </p>
        </div>

        <div class="mx-auto mt-10 max-w-2xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Deliver To') }}</p>
                        <p class="mt-1 font-semibold text-slate-800">{{ $sale->shipping_name }}</p>
                        <p class="text-sm text-slate-500">{{ $sale->shipping_phone }}</p>
                        <p class="text-sm text-slate-500">{{ $sale->shipping_address }}</p>
                    </div>
                    <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
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
                    <span class="font-semibold text-slate-700">
                        @if ($sale->payment_method === 'sslcommerz')
                            {{ __('Total') }} —
                            @if ($sale->payment_status === 'paid')
                                <span class="text-emerald-600">{{ __('Paid Online') }}</span>
                            @elseif ($sale->payment_status === 'failed')
                                <span class="text-rose-600">{{ __('Payment Failed') }}</span>
                            @else
                                <span class="text-amber-600">{{ __('Awaiting Payment') }}</span>
                            @endif
                        @else
                            {{ __('Total (Cash on Delivery)') }}
                        @endif
                    </span>
                    <span class="text-xl font-bold text-brand-950">{{ number_format($sale->total_amount, 2) }}</span>
                </div>

                @if ($sale->delivery_zone_name)
                <div class="mt-3 flex items-center justify-between rounded-lg bg-amber-50 px-3 py-2 text-sm ring-1 ring-amber-200">
                    <span class="text-amber-700">{{ __('Delivery (:zone) — pay courier separately', ['zone' => $sale->delivery_zone_name]) }}</span>
                    <span class="font-semibold text-amber-800">{{ number_format($sale->delivery_charge, 2) }}</span>
                </div>
                @endif
            </div>

            <div class="mt-8 text-center">
                <a href="{{ route('shop') }}" class="inline-flex items-center gap-2 rounded-lg bg-brand-900 px-6 py-3 text-sm font-semibold text-white hover:bg-brand-800">
                    {{ __('Continue Shopping') }}
                </a>
            </div>
        </div>
    </section>

</x-website-layout>
