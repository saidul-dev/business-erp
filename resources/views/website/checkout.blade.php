<x-website-layout :title="'Checkout'">

    <section class="bg-white py-12">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <h1 class="text-2xl font-extrabold text-brand-950">Checkout</h1>

            @if (! $company->online_site_id)
            <div class="mt-5 flex items-center gap-3 rounded-xl bg-amber-50 px-4 py-3 text-sm font-medium text-amber-700 ring-1 ring-amber-200">
                <span>The online store isn't fully set up yet — please contact us directly to place your order.</span>
            </div>
            @endif

            @if (session('error'))
            <div class="mt-5 flex items-center gap-3 rounded-xl bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700 ring-1 ring-rose-200">
                <span>{{ session('error') }}</span>
            </div>
            @endif

            <div class="mt-6 grid gap-8 lg:grid-cols-[1fr_360px]">
                <form method="POST" action="{{ route('checkout.store') }}" class="space-y-5 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                    @csrf

                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-400">Full Name</label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                               class="w-full rounded-lg border-slate-300 focus:border-accent-500 focus:ring-accent-500">
                        @error('name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-400">Phone Number</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" required
                               class="w-full rounded-lg border-slate-300 focus:border-accent-500 focus:ring-accent-500">
                        @error('phone') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        <p class="mt-1 text-xs text-slate-400">Used to track your order later — no account needed.</p>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-400">Delivery Address</label>
                        <textarea name="address" rows="3" required
                                  class="w-full rounded-lg border-slate-300 focus:border-accent-500 focus:ring-accent-500">{{ old('address') }}</textarea>
                        @error('address') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-400">Order Note (optional)</label>
                        <textarea name="note" rows="2"
                                  class="w-full rounded-lg border-slate-300 focus:border-accent-500 focus:ring-accent-500">{{ old('note') }}</textarea>
                    </div>

                    <div>
                        <p class="mb-1.5 text-xs font-semibold uppercase tracking-wide text-slate-400">Payment Method</p>
                        <label class="flex items-center gap-2 rounded-lg bg-slate-50 px-4 py-3 text-sm font-medium text-slate-700">
                            <input type="radio" checked disabled class="text-accent-600">
                            Cash on Delivery
                        </label>
                    </div>

                    <button type="submit" @if (! $company->online_site_id) disabled @endif
                            class="w-full rounded-lg bg-accent-500 px-6 py-3 text-sm font-semibold text-brand-950 hover:bg-accent-400 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        Place Order
                    </button>
                </form>

                <div class="h-fit rounded-2xl bg-slate-50 p-6 ring-1 ring-slate-200">
                    <p class="mb-4 font-semibold text-brand-950">Order Summary</p>
                    <div class="space-y-3">
                        @foreach ($lines as $line)
                        <div class="flex items-center justify-between gap-3 text-sm">
                            <span class="text-slate-600">{{ $line['name'] }} × {{ (int) $line['quantity'] }}</span>
                            <span class="font-semibold text-slate-800">{{ number_format($line['subtotal'], 2) }}</span>
                        </div>
                        @endforeach
                    </div>
                    <div class="mt-4 flex items-center justify-between border-t border-slate-200 pt-4">
                        <span class="font-semibold text-slate-700">Total</span>
                        <span class="text-xl font-bold text-brand-950">{{ number_format($subtotal, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

</x-website-layout>
