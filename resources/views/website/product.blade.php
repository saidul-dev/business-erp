<x-website-layout :title="$product->name">

    <section class="bg-white py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            @if (session('success'))
            <div class="mb-6 flex items-center gap-3 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 ring-1 ring-emerald-200">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                <span>{{ session('success') }}</span>
            </div>
            @endif
            @if (session('error'))
            <div class="mb-6 flex items-center gap-3 rounded-xl bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700 ring-1 ring-rose-200">
                <span>{{ session('error') }}</span>
            </div>
            @endif

            <nav class="mb-6 text-sm text-slate-500">
                <a href="{{ route('shop') }}" class="hover:text-accent-600">Shop</a>
                @if ($product->category)
                    <span class="mx-1.5">/</span>
                    <a href="{{ route('shop', ['category_id' => $product->category_id]) }}" class="hover:text-accent-600">{{ $product->category->name }}</a>
                @endif
                <span class="mx-1.5">/</span>
                <span class="text-slate-700">{{ $product->name }}</span>
            </nav>

            @php
                $galleryImages = collect([$product->image_url])
                    ->merge($product->images->pluck('url'))
                    ->filter()
                    ->values();
            @endphp

            <div class="grid gap-10 lg:grid-cols-2"
                 x-data="{
                    variants: @js($variantOptions),
                    selected: @js($variantOptions->first()['id'] ?? null),
                    hasVariants: {{ $product->has_variants ? 'true' : 'false' }},
                    gallery: @js($galleryImages),
                    activeImage: @js($galleryImages->first()),
                    get current() {
                        if (! this.hasVariants) return null;
                        return this.variants.find(v => v.id === this.selected);
                    },
                    get price() {
                        return this.hasVariants ? (this.current?.price ?? 0) : {{ (float) $product->selling_price }};
                    },
                    get inStock() {
                        return this.hasVariants ? (this.current?.in_stock ?? false) : {{ $inStock ? 'true' : 'false' }};
                    }
                 }">

                <div>
                    <div class="aspect-square rounded-2xl bg-slate-100 grid place-items-center text-slate-300 overflow-hidden">
                        <template x-if="activeImage">
                            <img :src="activeImage" alt="{{ $product->name }}" class="h-full w-full object-cover">
                        </template>
                        <template x-if="!activeImage">
                            <svg class="h-16 w-16" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3 3h18M3 3l1.5 18h15L21 3H3Z"/></svg>
                        </template>
                    </div>

                    <div class="mt-3 flex gap-3 overflow-x-auto" x-show="gallery.length > 1" x-cloak>
                        <template x-for="img in gallery" :key="img">
                            <button type="button" @click="activeImage = img"
                                    class="h-16 w-16 shrink-0 overflow-hidden rounded-lg ring-2 transition-colors"
                                    :class="activeImage === img ? 'ring-accent-500' : 'ring-transparent hover:ring-slate-200'">
                                <img :src="img" alt="" class="h-full w-full object-cover">
                            </button>
                        </template>
                    </div>
                </div>

                <div>
                    @if ($product->brand)
                    <p class="text-xs font-semibold uppercase tracking-wide text-accent-600">{{ $product->brand->name }}</p>
                    @endif
                    <h1 class="mt-1 text-3xl font-extrabold text-brand-950">{{ $product->name }}</h1>

                    <div class="mt-4 flex items-center gap-3">
                        <p class="text-2xl font-bold text-accent-600" x-text="Number(price).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></p>
                        @if ($product->discount_percent)
                        <p class="text-base text-slate-400 line-through">{{ number_format($product->compare_at_price, 2) }}</p>
                        <span class="rounded-full bg-rose-600 px-2 py-0.5 text-xs font-bold text-white">-{{ $product->discount_percent }}%</span>
                        @endif
                    </div>

                    <p class="mt-2">
                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold"
                              :class="inStock ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-500'"
                              x-text="inStock ? 'In Stock' : 'Out of Stock'"></span>
                    </p>

                    @if ($product->short_description)
                    <p class="mt-4 whitespace-pre-line text-sm leading-relaxed text-slate-600">{{ $product->short_description }}</p>
                    @endif

                    <form method="POST" action="{{ route('cart.add') }}" class="mt-6 space-y-4">
                        @csrf

                        @if ($product->has_variants)
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-400">Options</label>
                            <select name="item" x-model="selected" required
                                    class="w-full rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">
                                @foreach ($variantOptions as $variant)
                                <option value="{{ $variant['id'] }}">{{ $variant['label'] }} — {{ number_format($variant['price'], 2) }}{{ $variant['in_stock'] ? '' : ' (Out of Stock)' }}</option>
                                @endforeach
                            </select>
                        </div>
                        @else
                        <input type="hidden" name="item" value="product-{{ $product->id }}">
                        @endif

                        <div class="flex items-center gap-3">
                            <label class="text-xs font-semibold uppercase tracking-wide text-slate-400">Qty</label>
                            <input type="number" name="quantity" value="1" min="1" step="1"
                                   class="w-24 rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">
                        </div>

                        <button type="submit"
                                class="inline-flex items-center gap-2 rounded-lg bg-accent-500 px-6 py-3 text-sm font-semibold text-brand-950 hover:bg-accent-400 transition-colors">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg>
                            Add to Cart
                        </button>
                    </form>
                </div>
            </div>

            @if ($product->description)
            <div class="mt-16">
                <h2 class="text-xl font-extrabold text-brand-950 mb-4">Product Description</h2>
                <div class="trix-content rounded-2xl border border-slate-200 p-6 text-sm leading-relaxed text-slate-600">
                    {!! $product->description !!}
                </div>
            </div>
            @endif

            @if ($relatedProducts->isNotEmpty())
            <div class="mt-16" x-data="{ scroll(dir) { this.$refs.track.scrollBy({ left: dir * 320, behavior: 'smooth' }); } }">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-extrabold text-brand-950">Related Products</h2>
                    <div class="flex items-center gap-2">
                        <button @click="scroll(-1)" type="button" class="grid h-9 w-9 place-items-center rounded-full ring-1 ring-slate-200 text-slate-500 hover:bg-slate-50">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
                        </button>
                        <button @click="scroll(1)" type="button" class="grid h-9 w-9 place-items-center rounded-full ring-1 ring-slate-200 text-slate-500 hover:bg-slate-50">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                        </button>
                    </div>
                </div>

                <div x-ref="track" class="flex gap-5 overflow-x-auto scroll-smooth snap-x pb-2" style="scrollbar-width: none;">
                    @foreach ($relatedProducts as $related)
                    <a href="{{ route('shop.product', $related) }}" class="group w-48 shrink-0 snap-start rounded-2xl border border-slate-200 overflow-hidden bg-white hover:shadow-md transition-shadow">
                        <div class="relative aspect-square bg-slate-100 grid place-items-center text-slate-300">
                            @if ($related->discount_percent)
                            <span class="absolute top-2 left-2 rounded-full bg-rose-600 px-2 py-0.5 text-[11px] font-bold text-white">-{{ $related->discount_percent }}%</span>
                            @endif
                            @if ($related->image_url)
                                <img src="{{ $related->image_url }}" alt="{{ $related->name }}" class="h-full w-full object-cover">
                            @else
                                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3 3h18M3 3l1.5 18h15L21 3H3Z"/></svg>
                            @endif
                        </div>
                        <div class="p-3">
                            <p class="text-sm font-semibold text-brand-950 truncate group-hover:text-accent-700">{{ $related->name }}</p>
                            <div class="mt-1 flex items-center gap-1.5">
                                <span class="text-sm font-bold text-accent-600">{{ number_format($related->selling_price, 2) }}</span>
                                @if ($related->discount_percent)
                                <span class="text-xs text-slate-400 line-through">{{ number_format($related->compare_at_price, 2) }}</span>
                                @endif
                            </div>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </section>

</x-website-layout>
