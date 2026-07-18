<x-website-layout>

    @if ($company->hero_image_url)
    <!-- Hero (photo) -->
    <section>
        <div class="relative h-[440px] sm:h-[520px] overflow-hidden">
            <img src="{{ $company->hero_image_url }}" alt="{{ $company->name }}" class="absolute inset-0 h-full w-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-brand-950/85 via-brand-950/20 to-transparent"></div>

            <div class="absolute inset-x-0 bottom-0 pb-10 sm:pb-14">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="hero-in max-w-lg rounded-2xl bg-brand-950/80 backdrop-blur-sm px-6 py-6 sm:px-8 sm:py-8 ring-1 ring-white/10" style="animation-delay:.05s">
                        <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                            {{ $company->name ?? 'Business ERP' }}
                        </h1>
                        @if ($company->tagline)
                        <p class="mt-3 text-sm sm:text-base text-brand-200/90">{{ $company->tagline }}</p>
                        @endif
                        <div class="mt-5 flex flex-wrap items-center gap-3">
                            <a href="{{ route('contact') }}" class="rounded-lg bg-accent-500 px-5 py-2.5 text-sm font-semibold text-brand-950 hover:bg-accent-400 transition-colors">
                                {{ __('Get in Touch') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if ($heroStats['products'] > 0 || $heroStats['categories'] > 0 || $heroStats['branches'] > 1)
        <div class="bg-white border-b border-slate-100">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6 flex flex-wrap items-center justify-center gap-x-12 gap-y-4 divide-x divide-slate-200">
                @if ($heroStats['products'] > 0)
                <div class="pl-0 pr-6 first:pl-0">
                    <span class="text-2xl font-extrabold text-brand-900">{{ $heroStats['products'] }}+</span>
                    <span class="ml-2 text-sm text-slate-500">{{ __('Products') }}</span>
                </div>
                @endif
                @if ($heroStats['categories'] > 0)
                <div class="pl-6">
                    <span class="text-2xl font-extrabold text-brand-900">{{ $heroStats['categories'] }}</span>
                    <span class="ml-2 text-sm text-slate-500">{{ trans_choice('Category|Categories', $heroStats['categories']) }}</span>
                </div>
                @endif
                @if ($heroStats['branches'] > 1)
                <div class="pl-6">
                    <span class="text-2xl font-extrabold text-brand-900">{{ $heroStats['branches'] }}</span>
                    <span class="ml-2 text-sm text-slate-500">{{ __('Branches') }}</span>
                </div>
                @endif
            </div>
        </div>
        @endif
    </section>
    @else
    <!-- Hero (gradient fallback — no photo uploaded in Website Settings) -->
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
                    {{ __('Get in Touch') }}
                </a>
            </div>

            @if ($heroStats['products'] > 0 || $heroStats['categories'] > 0 || $heroStats['branches'] > 1)
            <div class="hero-in mt-16 flex flex-wrap items-center justify-center gap-x-12 gap-y-6" style="animation-delay:.35s">
                @if ($heroStats['products'] > 0)
                <div>
                    <p class="text-3xl font-extrabold text-white">{{ $heroStats['products'] }}+</p>
                    <p class="mt-1 text-sm text-brand-300/80">{{ __('Products') }}</p>
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
                    <p class="mt-1 text-sm text-brand-300/80">{{ __('Branches') }}</p>
                </div>
                @endif
            </div>
            @endif
        </div>
    </section>
    @endif

    @if ($featuredProducts->isNotEmpty())
    <!-- Featured Products -->
    <section class="bg-white py-16 sm:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-6 sm:grid-cols-3">
                @foreach ($featuredProducts as $product)
                    <div class="rounded-2xl border border-slate-200 overflow-hidden hover:shadow-lg hover:shadow-brand-100 transition">
                        <div class="aspect-[16/10] bg-slate-100 grid place-items-center text-slate-300">
                            @if ($product->image_url)
                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                            @else
                                <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3 3h18M3 3l1.5 18h15L21 3H3Z"/></svg>
                            @endif
                        </div>
                        <div class="p-5">
                            <h3 class="font-bold text-brand-950">{{ $product->name }}</h3>
                            <p class="mt-2 text-sm text-slate-500">{{ $product->description ? Str::limit($product->description, 90) : __('Available now — ask us for details.') }}</p>
                            <p class="mt-3 font-semibold text-accent-600">{{ number_format($product->selling_price, 2) }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    @if ($categories->isNotEmpty())
    <!-- What We Offer -->
    <section id="categories" class="bg-slate-50 py-20 sm:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto">
                <p class="text-sm font-semibold text-accent-600 tracking-wide uppercase">{{ __('What We Offer') }}</p>
                <h2 class="mt-2 text-3xl sm:text-4xl font-extrabold text-brand-950 tracking-tight">
                    {{ __('Our Product Categories') }}
                </h2>
            </div>

            <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($categories as $category)
                    @php $thumb = $category->products->first()?->image_url; @endphp
                    <div class="rounded-2xl bg-white border border-slate-200 overflow-hidden hover:shadow-lg hover:shadow-brand-100 transition">
                        <div class="aspect-[4/3] bg-slate-100 grid place-items-center text-slate-300">
                            @if ($thumb)
                                <img src="{{ $thumb }}" alt="{{ $category->name }}" class="h-full w-full object-cover">
                            @else
                                <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m6 4.125 2.25 2.25m0 0 2.25-2.25M12 13.875V6.75m-8.25.75h16.5A2.25 2.25 0 0 0 18 5.25H6A2.25 2.25 0 0 0 3.75 7.5Z"/></svg>
                            @endif
                        </div>
                        <div class="p-5">
                            <h3 class="font-bold text-brand-950">{{ $category->name }}</h3>
                            <p class="mt-1 text-sm text-slate-400">{{ trans_choice(':count item|:count items', $category->products_count, ['count' => $category->products_count]) }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    @if ($products->isNotEmpty())
    <!-- Product Slider -->
    <section class="bg-white py-20 sm:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center">
            <p class="text-sm font-semibold text-accent-600 tracking-wide uppercase">{{ __('Fresh From The Catalog') }}</p>
            <h2 class="mt-2 text-3xl sm:text-4xl font-extrabold text-brand-950 tracking-tight">
                {{ __('Our Products') }}
            </h2>
        </div>

        <div class="marquee mt-14" style="--marquee-duration: 40s;">
            <div class="marquee-track">
                @for ($i = 0; $i < 2; $i++)
                    @foreach ($products as $product)
                        <div class="w-52 shrink-0 rounded-2xl border border-slate-200 overflow-hidden bg-white">
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

    @include('website.partials.mission-vision-values')

    @include('website.partials.branches')

    <!-- Quick Links CTA -->
    <section class="bg-slate-50 py-16 sm:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-brand-950 via-brand-900 to-brand-800 px-6 py-12 sm:px-12 sm:py-16">
                <div class="hero-blob pointer-events-none absolute -top-24 -right-24 h-72 w-72 rounded-full bg-accent-500/20 blur-3xl"></div>
                <div class="hero-blob hero-blob-delay pointer-events-none absolute -bottom-24 -left-24 h-72 w-72 rounded-full bg-brand-500/30 blur-3xl"></div>

                <div class="relative flex flex-col lg:flex-row items-center justify-between gap-8">
                    <div class="text-center lg:text-left max-w-lg">
                        <p class="text-sm font-semibold text-accent-400 tracking-wide uppercase">{{ __('Ready When You Are') }}</p>
                        <h2 class="mt-2 text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                            {{ __('Explore what :company can do for you', ['company' => $company->name ?? __('we')]) }}
                        </h2>
                        <p class="mt-3 text-sm text-brand-200/90">
                            {{ __("Browse our catalog, reach out with a question, or see current openings — whatever brings you here, we're glad to help.") }}
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center justify-center gap-3 shrink-0">
                        <a href="{{ route('contact') }}" class="inline-flex items-center gap-2 rounded-lg border border-white/20 px-5 py-2.5 text-sm font-semibold text-white hover:bg-white/10 transition-colors">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a1.5 1.5 0 0 0 1.5-1.5v-3.379a1.5 1.5 0 0 0-1.06-1.436l-4.318-1.318a1.5 1.5 0 0 0-1.567.44l-1.03 1.235a11.25 11.25 0 0 1-5.632-5.633l1.235-1.03a1.5 1.5 0 0 0 .44-1.566L7.755 3.31a1.5 1.5 0 0 0-1.436-1.06H3a1.5 1.5 0 0 0-1.5 1.5v3Z"/></svg>
                            {{ __('Contact Us') }}
                        </a>
                        <a href="{{ route('career') }}" class="inline-flex items-center gap-2 rounded-lg border border-white/20 px-5 py-2.5 text-sm font-semibold text-white hover:bg-white/10 transition-colors">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                            {{ __('Careers') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact -->
    <section id="contact" class="bg-white py-20 sm:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto">
                <p class="text-sm font-semibold text-accent-600 tracking-wide uppercase">{{ __('Get in Touch') }}</p>
                <h2 class="mt-2 text-3xl sm:text-4xl font-extrabold text-brand-950 tracking-tight">
                    {{ __("Let's Talk About Your Business") }}
                </h2>
            </div>

            <div class="mt-14 mx-auto max-w-7xl grid gap-10 lg:grid-cols-2">
                <div class="space-y-5">
                    @if ($company->email)
                        <div class="flex items-center gap-3">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-brand-50 text-brand-700">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
                            </span>
                            <span class="text-slate-700">{{ $company->email }}</span>
                        </div>
                    @endif
                    @if ($company->phone)
                        <div class="flex items-center gap-3">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-brand-50 text-brand-700">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a1.5 1.5 0 0 0 1.5-1.5v-3.379a1.5 1.5 0 0 0-1.06-1.436l-4.318-1.318a1.5 1.5 0 0 0-1.567.44l-1.03 1.235a11.25 11.25 0 0 1-5.632-5.633l1.235-1.03a1.5 1.5 0 0 0 .44-1.566L7.755 3.31a1.5 1.5 0 0 0-1.436-1.06H3a1.5 1.5 0 0 0-1.5 1.5v3Z"/></svg>
                            </span>
                            <span class="text-slate-700">{{ $company->phone }}</span>
                        </div>
                    @endif
                    @if ($company->address)
                        <div class="flex items-start gap-3">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-brand-50 text-brand-700">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                            </span>
                            <span class="text-slate-700">{{ $company->address }}</span>
                        </div>
                    @endif
                    @if (!$company->email && !$company->phone && !$company->address)
                        <p class="text-slate-500">{{ __('Contact details will appear here once added in Admin > Settings.') }}</p>
                    @endif
                </div>

                <div>
                    @include('website.partials.contact-form')
                </div>
            </div>
        </div>
    </section>

    <style>
        .marquee { overflow: hidden; -webkit-mask-image: linear-gradient(90deg, transparent, #000 8%, #000 92%, transparent); mask-image: linear-gradient(90deg, transparent, #000 8%, #000 92%, transparent); }
        .marquee-track { display: flex; width: max-content; gap: 1rem; padding: 0 1rem; animation: marqueeScroll var(--marquee-duration, 32s) linear infinite; }
        .marquee:hover .marquee-track { animation-play-state: paused; }
        @keyframes marqueeScroll { from { transform: translateX(0); } to { transform: translateX(-50%); } }

        @media (prefers-reduced-motion: reduce) {
            .marquee-track { animation: none; }
        }
    </style>

</x-website-layout>
