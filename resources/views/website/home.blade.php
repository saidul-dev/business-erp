<x-website-layout>

    <!-- Hero -->
    <section class="relative overflow-hidden bg-gradient-to-br from-brand-950 via-brand-900 to-brand-800">
        <div class="hero-blob pointer-events-none absolute -top-32 -right-32 h-96 w-96 rounded-full bg-accent-500/20 blur-3xl"></div>
        <div class="hero-blob hero-blob-delay pointer-events-none absolute -bottom-32 -left-32 h-96 w-96 rounded-full bg-brand-500/30 blur-3xl"></div>

        <div class="relative z-10 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-24 sm:py-32 text-center">
            <h1 class="hero-in text-4xl sm:text-5xl lg:text-6xl font-extrabold text-white tracking-tight" style="animation-delay:.05s">
                {{ $company->name ?? 'Business ERP' }}
            </h1>
            @if ($company->tagline)
            <p class="hero-in mt-6 max-w-2xl mx-auto text-lg text-brand-200/90" style="animation-delay:.15s">
                {{ $company->tagline }}
            </p>
            @endif
            <div class="hero-in mt-10 flex flex-wrap items-center justify-center gap-4" style="animation-delay:.25s">
                <a href="{{ route('contact') }}" class="rounded-lg bg-accent-500 px-6 py-3 text-sm font-semibold text-brand-950 hover:bg-accent-400 transition-colors">
                    Get in Touch
                </a>
                @if ($company->ecommerce_enabled)
                <a href="{{ route('shop') }}" class="rounded-lg border border-white/20 px-6 py-3 text-sm font-semibold text-white hover:bg-white/10 transition-colors">
                    Visit Our Shop
                </a>
                @endif
            </div>

            @if ($heroStats['products'] > 0 || $heroStats['categories'] > 0 || $heroStats['branches'] > 1)
            <div class="hero-in mt-16 flex flex-wrap items-center justify-center gap-x-12 gap-y-6" style="animation-delay:.35s">
                @if ($heroStats['products'] > 0)
                <div>
                    <p class="text-3xl font-extrabold text-white">{{ $heroStats['products'] }}+</p>
                    <p class="mt-1 text-sm text-brand-300/80">Products</p>
                </div>
                @endif
                @if ($heroStats['categories'] > 0)
                <div>
                    <p class="text-3xl font-extrabold text-white">{{ $heroStats['categories'] }}</p>
                    <p class="mt-1 text-sm text-brand-300/80">{{ trans_choice('Category|Categories', $heroStats['categories']) }}</p>
                </div>
                @endif
                @if ($heroStats['branches'] > 1)
                <div>
                    <p class="text-3xl font-extrabold text-white">{{ $heroStats['branches'] }}</p>
                    <p class="mt-1 text-sm text-brand-300/80">Branches</p>
                </div>
                @endif
            </div>
            @endif
        </div>
    </section>

    @if ($company->mission_text || $company->vision_text)
    <!-- Mission & Vision -->
    <section class="bg-white py-20 sm:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-6 {{ $company->mission_text && $company->vision_text ? 'sm:grid-cols-2' : '' }} max-w-4xl mx-auto">
                @if ($company->mission_text)
                <div class="rounded-2xl border border-slate-200 p-8">
                    <span class="grid h-12 w-12 place-items-center rounded-xl bg-brand-50 text-brand-700">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m-15.432 0A8.959 8.959 0 0 1 3 12c0-.778.099-1.533.284-2.253"/></svg>
                    </span>
                    <h3 class="mt-4 text-lg font-bold text-brand-950">Our Mission</h3>
                    <p class="mt-2 text-sm text-slate-600">{{ $company->mission_text }}</p>
                </div>
                @endif
                @if ($company->vision_text)
                <div class="rounded-2xl border border-slate-200 p-8">
                    <span class="grid h-12 w-12 place-items-center rounded-xl bg-brand-50 text-brand-700">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                    </span>
                    <h3 class="mt-4 text-lg font-bold text-brand-950">Our Vision</h3>
                    <p class="mt-2 text-sm text-slate-600">{{ $company->vision_text }}</p>
                </div>
                @endif
            </div>
        </div>
    </section>
    @endif

    @if ($categories->isNotEmpty())
    <!-- Product Categories -->
    <section id="categories" class="bg-slate-50 py-20 sm:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center">
            <p class="text-sm font-semibold text-accent-600 tracking-wide uppercase">What We Offer</p>
            <h2 class="mt-2 text-3xl sm:text-4xl font-extrabold text-brand-950 tracking-tight">
                Our Product Categories
            </h2>
        </div>

        <div class="marquee mt-14">
            <div class="marquee-track">
                @for ($i = 0; $i < 2; $i++)
                    @foreach ($categories as $category)
                        <div class="flex items-center gap-3 shrink-0 rounded-xl bg-white border border-slate-200 px-5 py-4">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-brand-50 text-brand-700">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m6 4.125 2.25 2.25m0 0 2.25-2.25M12 13.875V6.75m-8.25.75h16.5A2.25 2.25 0 0 0 18 5.25H6A2.25 2.25 0 0 0 3.75 7.5Z"/></svg>
                            </span>
                            <span class="whitespace-nowrap">
                                <span class="block font-semibold text-brand-950">{{ $category->name }}</span>
                                <span class="block text-xs text-slate-400">{{ trans_choice(':count item|:count items', $category->products_count, ['count' => $category->products_count]) }}</span>
                            </span>
                        </div>
                    @endforeach
                @endfor
            </div>
        </div>
    </section>
    @endif

    @if ($products->isNotEmpty())
    <!-- Product Slider -->
    <section class="bg-white py-20 sm:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center">
            <p class="text-sm font-semibold text-accent-600 tracking-wide uppercase">Fresh From The Catalog</p>
            <h2 class="mt-2 text-3xl sm:text-4xl font-extrabold text-brand-950 tracking-tight">
                Our Products
            </h2>
        </div>

        <div class="marquee mt-14" style="--marquee-duration: 40s;">
            <div class="marquee-track">
                @for ($i = 0; $i < 2; $i++)
                    @foreach ($products as $product)
                        <div class="w-52 shrink-0 rounded-2xl border border-slate-200 overflow-hidden">
                            <div class="aspect-square bg-slate-100 grid place-items-center text-slate-300">
                                @if ($product->image_url)
                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                                @else
                                    <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3 3h18M3 3l1.5 18h15L21 3H3Z"/></svg>
                                @endif
                            </div>
                            <div class="p-4">
                                <p class="font-semibold text-brand-950 truncate">{{ $product->name }}</p>
                                <p class="mt-1 text-sm text-accent-600 font-bold">{{ number_format($product->selling_price, 2) }}</p>
                            </div>
                        </div>
                    @endforeach
                @endfor
            </div>
        </div>
    </section>
    @endif

    @if ($company->ecommerce_enabled)
    <!-- E-commerce teaser -->
    <section class="bg-slate-50 py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-3xl bg-gradient-to-br from-brand-900 to-brand-950 px-8 py-14 sm:px-16 text-center relative overflow-hidden">
                <div class="pointer-events-none absolute -top-16 -right-16 h-64 w-64 rounded-full bg-accent-500/20 blur-3xl"></div>
                <span class="inline-block rounded-full bg-accent-500/20 px-4 py-1 text-xs font-semibold tracking-wide text-accent-300 ring-1 ring-accent-400/30">
                    Now Live
                </span>
                <h2 class="mt-4 text-3xl sm:text-4xl font-extrabold text-white">We're also selling online</h2>
                <p class="mt-4 max-w-xl mx-auto text-brand-200/90">
                    Browse our products and order directly from our online store.
                </p>
                <a href="{{ route('shop') }}" class="mt-8 inline-block rounded-lg bg-accent-500 px-6 py-3 text-sm font-semibold text-brand-950 hover:bg-accent-400">
                    Visit Our Shop
                </a>
            </div>
        </div>
    </section>
    @endif

    @if ($branches->isNotEmpty())
    <!-- Branch Showcase -->
    <section class="bg-white py-20 sm:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto">
                <p class="text-sm font-semibold text-accent-600 tracking-wide uppercase">Find Us</p>
                <h2 class="mt-2 text-3xl sm:text-4xl font-extrabold text-brand-950 tracking-tight">
                    Our Branches
                </h2>
            </div>

            <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($branches as $branch)
                    <div class="rounded-2xl border border-slate-200 p-6">
                        <span class="grid h-11 w-11 place-items-center rounded-xl bg-brand-50 text-brand-700">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                        </span>
                        <h3 class="mt-4 text-lg font-bold text-brand-950">{{ $branch->name }}</h3>
                        @if ($branch->address)
                            <p class="mt-1 text-sm text-slate-600">{{ $branch->address }}</p>
                        @endif
                        @if ($branch->phone)
                            <p class="mt-2 text-sm text-slate-500">{{ $branch->phone }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- Business Stats -->
    <section class="bg-gradient-to-br from-brand-950 via-brand-900 to-brand-800 py-16 sm:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-3 gap-6 text-center">
                <div>
                    <p class="text-3xl sm:text-4xl font-extrabold text-white">{{ $businessStats['employees'] }}+</p>
                    <p class="mt-1 text-sm text-brand-300/80">{{ trans_choice('Employee|Employees', $businessStats['employees']) }}</p>
                </div>
                <div>
                    <p class="text-3xl sm:text-4xl font-extrabold text-white">{{ $businessStats['clients'] }}+</p>
                    <p class="mt-1 text-sm text-brand-300/80">{{ trans_choice('Client|Clients', $businessStats['clients']) }}</p>
                </div>
                <div>
                    <p class="text-3xl sm:text-4xl font-extrabold text-white">{{ $businessStats['suppliers'] }}+</p>
                    <p class="mt-1 text-sm text-brand-300/80">{{ trans_choice('Supplier|Suppliers', $businessStats['suppliers']) }}</p>
                </div>
            </div>
        </div>
    </section>

    <style>
        @keyframes heroFadeUp { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }
        .hero-in { opacity: 0; animation: heroFadeUp .7s ease-out forwards; }

        @keyframes heroBlobFloat { 0%, 100% { transform: translate(0, 0) scale(1); } 50% { transform: translate(-16px, 20px) scale(1.08); } }
        .hero-blob { animation: heroBlobFloat 10s ease-in-out infinite; }
        .hero-blob-delay { animation-delay: -5s; }

        .marquee { overflow: hidden; -webkit-mask-image: linear-gradient(90deg, transparent, #000 8%, #000 92%, transparent); mask-image: linear-gradient(90deg, transparent, #000 8%, #000 92%, transparent); }
        .marquee-track { display: flex; width: max-content; gap: 1rem; padding: 0 1rem; animation: marqueeScroll var(--marquee-duration, 32s) linear infinite; }
        .marquee:hover .marquee-track { animation-play-state: paused; }
        @keyframes marqueeScroll { from { transform: translateX(0); } to { transform: translateX(-50%); } }

        @media (prefers-reduced-motion: reduce) {
            .hero-in { animation: none; opacity: 1; }
            .hero-blob { animation: none; }
            .marquee-track { animation: none; }
        }
    </style>

</x-website-layout>
