<x-website-layout :title="'Cart'">

    <section class="bg-white py-12">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <h1 class="text-2xl font-extrabold text-brand-950">Your Cart</h1>

            @if (session('success'))
            <div class="mt-5 flex items-center gap-3 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 ring-1 ring-emerald-200">
                <span>{{ session('success') }}</span>
            </div>
            @endif
            @if (session('error'))
            <div class="mt-5 flex items-center gap-3 rounded-xl bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700 ring-1 ring-rose-200">
                <span>{{ session('error') }}</span>
            </div>
            @endif

            @if ($lines->isEmpty())
            <div class="mt-8 rounded-2xl border border-dashed border-slate-200 py-20 text-center">
                <p class="font-semibold text-slate-500">Your cart is empty.</p>
                <a href="{{ route('shop') }}" class="mt-2 inline-block text-sm font-semibold text-accent-600 hover:text-accent-800">Continue shopping</a>
            </div>
            @else
            <div class="mt-6 rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 overflow-hidden">
                <div class="divide-y divide-slate-100">
                    @foreach ($lines as $line)
                    <div class="flex items-center gap-4 p-5">
                        <div class="h-16 w-16 shrink-0 rounded-lg bg-slate-100 grid place-items-center text-slate-300 overflow-hidden">
                            @if ($line['image_url'])
                                <img src="{{ $line['image_url'] }}" alt="{{ $line['name'] }}" class="h-full w-full object-cover">
                            @else
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3 3h18M3 3l1.5 18h15L21 3H3Z"/></svg>
                            @endif
                        </div>

                        <div class="min-w-0 flex-1">
                            <p class="truncate font-semibold text-brand-950">{{ $line['name'] }}</p>
                            <p class="text-sm text-slate-500">{{ number_format($line['unit_price'], 2) }} each</p>
                        </div>

                        <form method="POST" action="{{ route('cart.update', $line['key']) }}" class="flex items-center gap-2">
                            @csrf
                            @method('PATCH')
                            <input type="number" name="quantity" value="{{ $line['quantity'] }}" min="1" step="1"
                                   class="w-20 rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">
                            <button type="submit" class="text-xs font-semibold text-slate-500 hover:text-slate-700">Update</button>
                        </form>

                        <p class="w-24 shrink-0 text-right font-semibold text-brand-950">{{ number_format($line['subtotal'], 2) }}</p>

                        <form method="POST" action="{{ route('cart.remove', $line['key']) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-slate-400 hover:text-rose-600" title="Remove">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                            </button>
                        </form>
                    </div>
                    @endforeach
                </div>

                <div class="flex items-center justify-between bg-slate-50 px-5 py-4">
                    <span class="font-semibold text-slate-600">Subtotal</span>
                    <span class="text-xl font-bold text-brand-950">{{ number_format($subtotal, 2) }}</span>
                </div>
            </div>

            <div class="mt-6 flex items-center justify-between">
                <a href="{{ route('shop') }}" class="text-sm font-semibold text-slate-500 hover:text-slate-700">← Continue shopping</a>
                <a href="{{ route('checkout') }}" class="inline-flex items-center gap-2 rounded-lg bg-accent-500 px-6 py-3 text-sm font-semibold text-brand-950 hover:bg-accent-400 transition-colors">
                    Proceed to Checkout
                </a>
            </div>
            @endif
        </div>
    </section>

</x-website-layout>
