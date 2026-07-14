<x-website-layout :title="null">

    <section class="bg-slate-100 py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-4 lg:grid-cols-[1fr_280px]">
                <!-- Hero slider -->
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-brand-950 via-brand-900 to-brand-800 aspect-[16/7] sm:aspect-[16/6]"
                     @if ($slides->count() > 1) x-data="{ active: 0, count: {{ $slides->count() }} }" x-init="setInterval(() => active = (active + 1) % count, 5000)" @endif>
                    @forelse ($slides as $i => $slide)
                        <div @if ($slides->count() > 1) x-show="active === {{ $i }}" x-transition.opacity @endif class="absolute inset-0">
                            @if ($slide->link_url)
                            <a href="{{ $slide->link_url }}" class="block h-full w-full">
                                <img src="{{ $slide->image_url }}" alt="{{ __('Slide :number', ['number' => $i + 1]) }}" class="h-full w-full object-cover">
                            </a>
                            @else
                            <img src="{{ $slide->image_url }}" alt="{{ __('Slide :number', ['number' => $i + 1]) }}" class="h-full w-full object-cover">
                            @endif
                        </div>
                    @empty
                        <div class="absolute inset-0 grid place-items-center text-center px-6">
                            <div>
                                <h1 class="text-2xl sm:text-4xl font-extrabold text-white">{{ $company->name ?? 'Our Store' }}</h1>
                                <p class="mt-3 text-brand-200/90">{{ $company->tagline ?: __('Shop the full catalog online.') }}</p>
                            </div>
                        </div>
                    @endforelse

                    @if ($slides->count() > 1)
                    <button @click="active = (active - 1 + count) % count" class="absolute left-3 top-1/2 -translate-y-1/2 grid h-9 w-9 place-items-center rounded-full bg-white/90 text-brand-950 hover:bg-white">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
                    </button>
                    <button @click="active = (active + 1) % count" class="absolute right-3 top-1/2 -translate-y-1/2 grid h-9 w-9 place-items-center rounded-full bg-white/90 text-brand-950 hover:bg-white">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                    </button>
                    @endif
                </div>

                <!-- Side banners -->
                @if ($company->side_banner_1_url || $company->side_banner_2_url)
                <div class="hidden lg:grid grid-rows-2 gap-4">
                    @if ($company->side_banner_1_url)
                    <div class="overflow-hidden rounded-2xl bg-slate-200">
                        <img src="{{ $company->side_banner_1_url }}" alt="{{ __('Promo') }}" class="h-full w-full object-cover">
                    </div>
                    @endif
                    @if ($company->side_banner_2_url)
                    <div class="overflow-hidden rounded-2xl bg-slate-200">
                        <img src="{{ $company->side_banner_2_url }}" alt="{{ __('Promo') }}" class="h-full w-full object-cover">
                    </div>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </section>

    <!-- Quick category grid -->
    @if ($categories->isNotEmpty())
    <section class="bg-white py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-4">
                @foreach ($categories as $category)
                <a href="{{ route('shop', ['category_id' => $category->id]) }}" class="flex flex-col items-center gap-2 rounded-xl p-3 text-center hover:bg-slate-50">
                    <span class="grid h-14 w-14 place-items-center overflow-hidden rounded-full bg-brand-50 text-brand-700 ring-1 ring-brand-100">
                        @if ($category->icon_url)
                            <img src="{{ $category->icon_url }}" alt="{{ $category->name }}" class="h-full w-full object-cover">
                        @else
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z"/></svg>
                        @endif
                    </span>
                    <span class="text-xs font-medium text-slate-700">{{ $category->name }}</span>
                </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- Flash Sale -->
    @if ($flashSaleProducts->isNotEmpty())
    <section class="bg-slate-50 py-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex items-center gap-2 mb-5">
                <svg class="h-5 w-5 text-accent-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5 9 21l6-9-6-9L3.75 13.5Z"/></svg>
                <h2 class="text-xl font-extrabold text-brand-950">{{ __('Flash Sale') }}</h2>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-5">
                @foreach ($flashSaleProducts as $product)
                <a href="{{ route('shop.product', $product) }}" class="group rounded-2xl border border-slate-200 overflow-hidden bg-white hover:shadow-md transition-shadow">
                    <div class="relative aspect-square bg-slate-100 grid place-items-center text-slate-300">
                        @if ($product->discount_percent)
                        <span class="absolute top-2 left-2 rounded-full bg-rose-600 px-2 py-0.5 text-[11px] font-bold text-white">-{{ $product->discount_percent }}%</span>
                        @endif
                        @if ($product->image_url)
                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                        @else
                            <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3 3h18M3 3l1.5 18h15L21 3H3Z"/></svg>
                        @endif
                    </div>
                    <div class="p-4">
                        <p class="font-semibold text-brand-950 truncate group-hover:text-accent-700">{{ $product->name }}</p>
                        <div class="mt-1.5 flex items-center gap-2">
                            <span class="font-bold text-accent-600">{{ number_format($product->selling_price, 2) }}</span>
                            @if ($product->discount_percent)
                            <span class="text-xs text-slate-400 line-through">{{ number_format($product->compare_at_price, 2) }}</span>
                            @endif
                        </div>
                        <span class="mt-3 inline-block w-full rounded-lg bg-brand-900 py-2 text-center text-xs font-semibold text-white group-hover:bg-brand-800">
                            {{ __('Buy Now') }}
                        </span>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- Featured / Latest / Best Sellers -->
    @if ($featuredProducts->isNotEmpty() || $latestProducts->isNotEmpty() || $bestSellers->isNotEmpty())
    <section class="bg-white py-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-6 lg:grid-cols-3">
                @foreach ([
                    ['title' => __('Featured Products'), 'icon' => 'star', 'items' => $featuredProducts],
                    ['title' => __('Latest Products'), 'icon' => 'trending', 'items' => $latestProducts],
                    ['title' => __('Best Sellers'), 'icon' => 'badge', 'items' => $bestSellers],
                ] as $column)
                    @if ($column['items']->isNotEmpty())
                    <div class="rounded-2xl border border-slate-200 p-5">
                        <div class="flex items-center gap-2 mb-4">
                            <svg class="h-4 w-4 text-accent-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z"/></svg>
                            <h3 class="font-bold text-brand-950">{{ $column['title'] }}</h3>
                        </div>

                        <div class="divide-y divide-slate-100">
                            @foreach ($column['items'] as $product)
                            <a href="{{ route('shop.product', $product) }}" class="flex items-center gap-3 py-3 group">
                                <span class="grid h-14 w-14 shrink-0 place-items-center overflow-hidden rounded-lg bg-slate-100 text-slate-300">
                                    @if ($product->image_url)
                                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                                    @else
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3 3h18M3 3l1.5 18h15L21 3H3Z"/></svg>
                                    @endif
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium text-slate-700 group-hover:text-accent-700">{{ $product->name }}</p>
                                    <div class="mt-1 flex items-center gap-2">
                                        <span class="text-sm font-bold text-accent-600">{{ number_format($product->selling_price, 2) }}</span>
                                        @if ($product->discount_percent)
                                        <span class="text-xs text-slate-400 line-through">{{ number_format($product->compare_at_price, 2) }}</span>
                                        @endif
                                    </div>
                                </div>
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif
                @endforeach
            </div>
        </div>
    </section>
    @endif

    @if ($company->about_text)
    <section class="bg-slate-50 py-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div x-data="{ open: false }" class="rounded-2xl bg-white p-6 ring-1 ring-slate-200">
                <button @click="open = !open" class="flex w-full items-center justify-between text-left">
                    <h3 class="font-bold text-brand-950">{{ $company->name }} — {{ $company->tagline ?: __('About Our Store') }}</h3>
                    <svg class="h-4 w-4 text-slate-400 shrink-0 transition-transform" :class="open && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                </button>
                <div x-show="open" x-transition style="display:none" class="mt-4 text-sm leading-relaxed text-slate-600">
                    {{ $company->about_text }}
                </div>
            </div>
        </div>
    </section>
    @endif

</x-website-layout>
