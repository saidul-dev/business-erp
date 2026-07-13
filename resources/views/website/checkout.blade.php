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

            <div class="mt-6 grid gap-8 lg:grid-cols-[1fr_360px]"
                 x-data="{
                    zones: @js($deliveryZones->map(fn ($z) => ['id' => $z->id, 'name' => $z->name, 'charge' => (float) $z->charge])),
                    zoneId: '{{ old('delivery_zone_id') }}',
                    get zone() { return this.zones.find(z => z.id == this.zoneId) ?? null; },
                    submitting: false,
                 }">
                <form method="POST" action="{{ route('checkout.store') }}" @submit="submitting = true"
                      class="space-y-5 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                    @csrf
                    <input type="hidden" name="checkout_token" value="{{ $checkoutToken }}">

                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-400">Full Name</label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                               class="w-full rounded-lg border-slate-300 focus:border-accent-500 focus:ring-accent-500">
                        @error('name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-400">Phone Number</label>
                        <input type="tel" inputmode="numeric" name="phone" value="{{ old('phone') }}" required
                               placeholder="01XXXXXXXXX"
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

                    @if ($deliveryZones->isNotEmpty())
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-400">Delivery Area</label>
                        <select name="delivery_zone_id" x-model="zoneId" required
                                class="w-full rounded-lg border-slate-300 focus:border-accent-500 focus:ring-accent-500">
                            <option value="">Select your area…</option>
                            @foreach ($deliveryZones as $zone)
                            <option value="{{ $zone->id }}">{{ $zone->name }} — {{ number_format($zone->charge, 2) }}</option>
                            @endforeach
                        </select>
                        @error('delivery_zone_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        <p class="mt-1 text-xs text-slate-400">Delivery charge is collected by the courier on delivery — not included in the order total below.</p>
                    </div>
                    @endif

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

                    <button type="submit" :disabled="submitting || {{ $company->online_site_id ? 'false' : 'true' }}"
                            class="w-full rounded-lg bg-accent-500 px-6 py-3 text-sm font-semibold text-brand-950 hover:bg-accent-400 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!submitting">Place Order</span>
                        <span x-show="submitting" x-cloak>Placing Order…</span>
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
                        <span class="font-semibold text-slate-700">Total (Cash on Delivery)</span>
                        <span class="text-xl font-bold text-brand-950">{{ number_format($subtotal, 2) }}</span>
                    </div>

                    <template x-if="zone">
                        <div class="mt-3 flex items-center justify-between rounded-lg bg-amber-50 px-3 py-2 text-sm ring-1 ring-amber-200">
                            <span class="text-amber-700">Delivery (<span x-text="zone?.name"></span>) — pay courier</span>
                            <span class="font-semibold text-amber-800" x-text="Number(zone?.charge ?? 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </section>

</x-website-layout>
