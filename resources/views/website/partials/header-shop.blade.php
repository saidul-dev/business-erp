<!-- Shop Navbar -->
<header class="sticky top-0 z-40 bg-brand-950" x-data="{ categoryOpen: false }">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center gap-4">
            <a href="{{ route('home') }}" class="flex items-center gap-3 shrink-0">
                @if ($company->logo_url)
                    <img src="{{ $company->logo_url }}" alt="{{ $company->name }}" class="h-9 w-9 rounded-lg object-cover">
                @else
                    <x-application-logo class="h-9 w-9" />
                @endif
                <span class="hidden sm:block text-lg font-bold text-white tracking-wide">{{ $company->name ?? 'Business ERP' }}</span>
            </a>

            <!-- All Category dropdown -->
            @if ($navCategories->isNotEmpty())
            <div class="relative hidden md:block shrink-0" @click.outside="categoryOpen = false">
                <button @click="categoryOpen = !categoryOpen" type="button"
                        class="flex items-center gap-2 rounded-lg bg-white/10 px-3 py-2 text-sm font-medium text-white hover:bg-white/15">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                    {{ __('All Category') }}
                    <svg class="h-3.5 w-3.5" :class="categoryOpen && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                </button>
                <div x-show="categoryOpen" x-transition style="display:none"
                     class="absolute left-0 top-full z-50 mt-1 w-64 rounded-xl bg-white py-2 shadow-lg ring-1 ring-black/5">
                    @foreach ($navCategories as $category)
                    <a href="{{ route('shop', ['category_id' => $category->id]) }}"
                       class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-accent-600">
                        {{ $category->name }}
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Search -->
            <form method="GET" action="{{ route('shop') }}" class="hidden sm:flex flex-1 min-w-0">
                <div class="relative w-full">
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ __('Search for products...') }}"
                           class="w-full rounded-lg border-0 bg-white py-2 pl-4 pr-10 text-sm text-slate-800 focus:ring-2 focus:ring-accent-500">
                    <button type="submit" class="absolute inset-y-0 right-0 grid w-10 place-items-center text-slate-400 hover:text-accent-600">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                    </button>
                </div>
            </form>

            <!-- Icons -->
            <div class="flex items-center gap-1 shrink-0 ml-auto">
                <div class="hidden sm:block">
                    @include('website.partials.language-switcher')
                </div>
                <a href="javascript:void(0)" title="{{ __('Wishlist — coming soon') }}" class="relative rounded-lg p-2 text-white/70 hover:bg-white/10 hover:text-white">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/></svg>
                    <span class="absolute -top-1 -right-1 grid h-4 w-4 place-items-center rounded-full bg-white/20 text-[10px] font-bold text-white">0</span>
                </a>
                <a href="javascript:void(0)" title="{{ __('Compare — coming soon') }}" class="relative rounded-lg p-2 text-white/70 hover:bg-white/10 hover:text-white">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg>
                    <span class="absolute -top-1 -right-1 grid h-4 w-4 place-items-center rounded-full bg-white/20 text-[10px] font-bold text-white">0</span>
                </a>
                <a href="{{ route('cart') }}" title="{{ __('Cart') }}" class="relative rounded-lg p-2 text-white hover:bg-white/10">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg>
                    @if ($cartCount > 0)
                    <span class="absolute -top-1 -right-1 grid h-4 w-4 place-items-center rounded-full bg-accent-500 text-[10px] font-bold text-brand-950">{{ $cartCount }}</span>
                    @else
                    <span class="absolute -top-1 -right-1 grid h-4 w-4 place-items-center rounded-full bg-white/20 text-[10px] font-bold text-white">0</span>
                    @endif
                </a>
                @auth
                <a href="{{ route('dashboard') }}" title="{{ __('Dashboard') }}" class="rounded-lg p-2 text-white hover:bg-white/10">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                </a>
                @else
                <a href="{{ route('login') }}" title="{{ __('Login') }}" class="rounded-lg p-2 text-white hover:bg-white/10">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                </a>
                @endauth

                <button @click="mobileNavOpen = !mobileNavOpen" class="lg:hidden text-white p-2">
                    <svg x-show="!mobileNavOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                    <svg x-show="mobileNavOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="display:none"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        <!-- Category nav bar (desktop) -->
        @if ($navCategories->isNotEmpty())
        <nav class="hidden lg:flex items-center gap-6 border-t border-white/10 py-2.5 overflow-x-auto">
            @foreach ($navCategories as $category)
            <a href="{{ route('shop', ['category_id' => $category->id]) }}"
               class="shrink-0 text-xs font-medium text-brand-100 hover:text-white">{{ $category->name }}</a>
            @endforeach
        </nav>
        @endif

        <!-- Mobile nav -->
        <div x-show="mobileNavOpen" x-transition class="lg:hidden pb-4 space-y-3" style="display:none">
            <form method="GET" action="{{ route('shop') }}">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ __('Search for products...') }}"
                       class="w-full rounded-lg border-0 bg-white py-2 px-4 text-sm text-slate-800 focus:ring-2 focus:ring-accent-500">
            </form>
            <div class="space-y-1">
                <a href="{{ route('home') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-brand-100 hover:bg-white/10">{{ __('Home') }}</a>
                <a href="{{ route('shop') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-brand-100 hover:bg-white/10">{{ __('Shop') }}</a>
                @foreach ($navCategories as $category)
                <a href="{{ route('shop', ['category_id' => $category->id]) }}" class="block rounded-lg px-3 py-2 pl-6 text-sm text-brand-200 hover:bg-white/10">{{ $category->name }}</a>
                @endforeach
                <a href="{{ route('cart') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-brand-100 hover:bg-white/10">{{ __('Cart') }} @if ($cartCount > 0)({{ $cartCount }})@endif</a>
                <a href="{{ route('track-order') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-brand-100 hover:bg-white/10">{{ __('Track Order') }}</a>
                <a href="{{ route('about') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-brand-100 hover:bg-white/10">{{ __('About') }}</a>
                <a href="{{ route('contact') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-brand-100 hover:bg-white/10">{{ __('Contact') }}</a>
            </div>
            <div class="flex items-center gap-2">
                @foreach (config('app.available_locales', ['en' => 'English']) as $code => $label)
                <form method="POST" action="{{ route('language.switch', $code) }}">
                    @csrf
                    <button type="submit" class="rounded-lg px-3 py-1.5 text-xs font-semibold {{ app()->getLocale() === $code ? 'bg-accent-500 text-brand-950' : 'bg-white/10 text-white' }}">{{ $label }}</button>
                </form>
                @endforeach
            </div>
            <div class="pt-1">
                @auth
                    <a href="{{ route('dashboard') }}" class="block rounded-lg bg-accent-500 px-3 py-2 text-center text-sm font-semibold text-brand-950">{{ __('Go to Dashboard') }}</a>
                @else
                    <a href="{{ route('login') }}" class="block rounded-lg border border-white/20 px-3 py-2 text-center text-sm font-semibold text-white">{{ __('Login') }}</a>
                @endauth
            </div>
        </div>
    </div>
</header>
