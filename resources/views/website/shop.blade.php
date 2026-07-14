<x-website-layout :title="__('Shop')">

    <section class="bg-gradient-to-br from-brand-950 via-brand-900 to-brand-800 py-16 text-center">
        <div class="mx-auto max-w-3xl px-4">
            <span class="inline-block rounded-full bg-white/10 px-4 py-1 text-xs font-semibold tracking-wide text-accent-300 ring-1 ring-white/20">
                {{ __('Online Store') }}
            </span>
            <h1 class="mt-6 text-4xl font-extrabold text-white">{{ __('Shop :company', ['company' => $company->name ?? __('our products')]) }}</h1>
            <p class="mt-4 text-brand-200/90">{{ __('Browse the full catalog and order online.') }}</p>
        </div>
    </section>

    <section class="bg-white py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            @if (session('success'))
            <div class="mb-6 flex items-center gap-3 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 ring-1 ring-emerald-200">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                <span>{{ session('success') }}</span>
            </div>
            @endif

            <div class="grid gap-8 lg:grid-cols-[240px_1fr]">
                <!-- Filters -->
                <aside class="lg:sticky lg:top-24 lg:self-start">
                    <form method="GET" action="{{ route('shop') }}" class="space-y-6">
                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Search') }}</label>
                            <input type="search" name="q" value="{{ $q }}" placeholder="{{ __('Product name or SKU…') }}"
                                   class="w-full rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">
                        </div>

                        @if ($categories->isNotEmpty())
                        <div>
                            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Category') }}</p>
                            <div class="space-y-1.5">
                                <label class="flex items-center gap-2 text-sm text-slate-600">
                                    <input type="radio" name="category_id" value="" class="text-accent-600 focus:ring-accent-500" @checked(! $categoryId)>
                                    {{ __('All Categories') }}
                                </label>
                                @foreach ($categories as $category)
                                <label class="flex items-center justify-between gap-2 text-sm text-slate-600">
                                    <span class="flex items-center gap-2">
                                        <input type="radio" name="category_id" value="{{ $category->id }}" class="text-accent-600 focus:ring-accent-500" @checked($categoryId === $category->id)>
                                        {{ $category->name }}
                                    </span>
                                    <span class="text-xs text-slate-400">{{ $category->products_count }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        @if ($brands->isNotEmpty())
                        <div>
                            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Brand') }}</p>
                            <div class="space-y-1.5">
                                <label class="flex items-center gap-2 text-sm text-slate-600">
                                    <input type="radio" name="brand_id" value="" class="text-accent-600 focus:ring-accent-500" @checked(! $brandId)>
                                    {{ __('All Brands') }}
                                </label>
                                @foreach ($brands as $brand)
                                <label class="flex items-center gap-2 text-sm text-slate-600">
                                    <input type="radio" name="brand_id" value="{{ $brand->id }}" class="text-accent-600 focus:ring-accent-500" @checked($brandId === $brand->id)>
                                    {{ $brand->name }}
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <div>
                            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Price Range') }}</p>
                            <div class="flex items-center gap-2">
                                <input type="number" name="min_price" value="{{ $minPrice }}" placeholder="{{ __('Min') }}" min="0"
                                       class="w-full rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">
                                <span class="text-slate-400">–</span>
                                <input type="number" name="max_price" value="{{ $maxPrice }}" placeholder="{{ __('Max') }}" min="0"
                                       class="w-full rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">
                            </div>
                        </div>

                        <div>
                            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Sort By') }}</p>
                            <select name="sort" class="w-full rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">
                                <option value="newest" @selected($sort === 'newest')>{{ __('Newest') }}</option>
                                <option value="price_asc" @selected($sort === 'price_asc')>{{ __('Price: Low to High') }}</option>
                                <option value="price_desc" @selected($sort === 'price_desc')>{{ __('Price: High to Low') }}</option>
                            </select>
                        </div>

                        <div class="flex items-center gap-3">
                            <button type="submit" class="rounded-lg bg-brand-900 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-800">
                                {{ __('Apply') }}
                            </button>
                            @if ($categoryId || $brandId || $q || $minPrice !== null || $maxPrice !== null || $sort !== 'newest')
                            <a href="{{ route('shop') }}" class="text-sm font-semibold text-slate-500 hover:text-slate-700">{{ __('Clear') }}</a>
                            @endif
                        </div>
                    </form>
                </aside>

                <!-- Product grid -->
                <div>
                    <p class="mb-4 text-sm text-slate-500">{{ trans_choice(':count product found|:count products found', $products->total(), ['count' => $products->total()]) }}</p>

                    @if ($products->isEmpty())
                    <div class="rounded-2xl border border-dashed border-slate-200 py-20 text-center">
                        <p class="font-semibold text-slate-500">{{ __('No products match your filters.') }}</p>
                        <a href="{{ route('shop') }}" class="mt-2 inline-block text-sm font-semibold text-accent-600 hover:text-accent-800">{{ __('Clear filters') }}</a>
                    </div>
                    @else
                    <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach ($products as $product)
                        <a href="{{ route('shop.product', $product) }}" class="group rounded-2xl border border-slate-200 overflow-hidden bg-white hover:shadow-md transition-shadow">
                            <div class="relative aspect-square bg-slate-100 grid place-items-center text-slate-300">
                                @if ($product->image_url)
                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                                @else
                                    <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3 3h18M3 3l1.5 18h15L21 3H3Z"/></svg>
                                @endif
                                <span class="absolute top-3 left-3 rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $product->in_stock ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-500' }}">
                                    {{ $product->in_stock ? __('In Stock') : __('Out of Stock') }}
                                </span>
                                @if ($product->discount_percent)
                                <span class="absolute top-3 right-3 rounded-full bg-rose-600 px-2 py-0.5 text-[11px] font-bold text-white">-{{ $product->discount_percent }}%</span>
                                @endif
                            </div>
                            <div class="p-4">
                                @if ($product->category)
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ $product->category->name }}</p>
                                @endif
                                <p class="mt-1 font-semibold text-brand-950 group-hover:text-accent-700 transition-colors">{{ $product->name }}</p>
                                <div class="mt-1.5 flex items-center gap-2">
                                    <span class="text-sm font-bold text-accent-600">
                                        @if ($product->price_from === $product->price_to)
                                            {{ number_format($product->price_from, 2) }}
                                        @else
                                            {{ number_format($product->price_from, 2) }} – {{ number_format($product->price_to, 2) }}
                                        @endif
                                    </span>
                                    @if ($product->discount_percent)
                                    <span class="text-xs text-slate-400 line-through">{{ number_format($product->compare_at_price, 2) }}</span>
                                    @endif
                                </div>
                            </div>
                        </a>
                        @endforeach
                    </div>

                    <div class="mt-10">
                        {{ $products->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

</x-website-layout>
