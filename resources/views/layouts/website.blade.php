<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? '' ? ($company->name ?? config('app.name', 'Business ERP')).' — '.$title : ($company->name ?? config('app.name', 'Business ERP')) }}</title>
        <meta name="description" content="{{ $description ?? $company->tagline ?? $company->about_text ?? 'Quality products and trusted service.' }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-900 antialiased" x-data="{ mobileNavOpen: false }">

        <!-- Navbar -->
        <header class="sticky top-0 z-40 bg-brand-950/95 backdrop-blur border-b border-white/10">
            <nav class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <a href="{{ route('home') }}" class="flex items-center gap-3 shrink-0">
                        @if ($company->logo_url)
                            <img src="{{ $company->logo_url }}" alt="{{ $company->name }}" class="h-9 w-9 rounded-lg object-cover">
                        @else
                            <x-application-logo class="h-9 w-9" />
                        @endif
                        <div class="leading-tight">
                            <span class="block text-lg font-bold text-white tracking-wide">{{ $company->name ?? 'Business ERP' }}</span>
                            <span class="block text-[11px] font-medium text-accent-400">Enterprise Suite</span>
                        </div>
                    </a>

                    <div class="hidden lg:flex items-center gap-8">
                        <a href="{{ route('home') }}" class="text-sm font-medium {{ request()->routeIs('home') ? 'text-white' : 'text-brand-100 hover:text-white' }}">Home</a>
                        <a href="{{ route('about') }}" class="text-sm font-medium {{ request()->routeIs('about') ? 'text-white' : 'text-brand-100 hover:text-white' }}">About</a>
                        <a href="{{ route('media') }}" class="text-sm font-medium {{ request()->routeIs('media') ? 'text-white' : 'text-brand-100 hover:text-white' }}">Media</a>
                        <a href="{{ route('career') }}" class="text-sm font-medium {{ request()->routeIs('career') ? 'text-white' : 'text-brand-100 hover:text-white' }}">Career</a>
                        @if ($company->ecommerce_enabled)
                            <a href="{{ route('shop') }}" class="text-sm font-medium {{ request()->routeIs('shop', 'shop.product') ? 'text-white' : 'text-brand-100 hover:text-white' }}">Shop</a>
                            <a href="{{ route('track-order') }}" class="text-sm font-medium {{ request()->routeIs('track-order*') ? 'text-white' : 'text-brand-100 hover:text-white' }}">Track Order</a>
                        @endif
                        <a href="{{ route('contact') }}" class="text-sm font-medium {{ request()->routeIs('contact') ? 'text-white' : 'text-brand-100 hover:text-white' }}">Contact</a>
                    </div>

                    <div class="hidden lg:flex items-center gap-3">
                        @if ($company->ecommerce_enabled)
                            <a href="{{ route('cart') }}" class="relative rounded-lg p-2 text-white hover:bg-white/10">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg>
                                @if ($cartCount > 0)
                                <span class="absolute -top-1 -right-1 grid h-4 w-4 place-items-center rounded-full bg-accent-500 text-[10px] font-bold text-brand-950">{{ $cartCount }}</span>
                                @endif
                            </a>
                        @endif
                        @auth
                            <a href="{{ route('dashboard') }}" class="rounded-lg bg-accent-500 px-4 py-2 text-sm font-semibold text-brand-950 hover:bg-accent-400">
                                Go to Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="rounded-lg border border-white/20 px-4 py-2 text-sm font-semibold text-white hover:bg-white/10">
                                Login
                            </a>
                        @endauth
                    </div>

                    <button @click="mobileNavOpen = !mobileNavOpen" class="lg:hidden text-white p-2">
                        <svg x-show="!mobileNavOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                        <svg x-show="mobileNavOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="display:none"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Mobile nav -->
                <div x-show="mobileNavOpen" x-transition class="lg:hidden pb-4 space-y-1" style="display:none">
                    <a href="{{ route('home') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-brand-100 hover:bg-white/10">Home</a>
                    <a href="{{ route('about') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-brand-100 hover:bg-white/10">About</a>
                    <a href="{{ route('media') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-brand-100 hover:bg-white/10">Media</a>
                    <a href="{{ route('career') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-brand-100 hover:bg-white/10">Career</a>
                    @if ($company->ecommerce_enabled)
                        <a href="{{ route('shop') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-brand-100 hover:bg-white/10">Shop</a>
                        <a href="{{ route('cart') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-brand-100 hover:bg-white/10">Cart @if ($cartCount > 0)({{ $cartCount }})@endif</a>
                        <a href="{{ route('track-order') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-brand-100 hover:bg-white/10">Track Order</a>
                    @endif
                    <a href="{{ route('contact') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-brand-100 hover:bg-white/10">Contact</a>
                    <div class="pt-2">
                        @auth
                            <a href="{{ route('dashboard') }}" class="block rounded-lg bg-accent-500 px-3 py-2 text-center text-sm font-semibold text-brand-950">Go to Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="block rounded-lg border border-white/20 px-3 py-2 text-center text-sm font-semibold text-white">Login</a>
                        @endauth
                    </div>
                </div>
            </nav>
        </header>

        <main>
            {{ $slot }}
        </main>

        <!-- Footer -->
        <footer class="bg-brand-950 text-brand-200/80 border-t border-white/10">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-12 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <div class="flex items-center gap-3 mb-3">
                        <x-application-logo class="h-8 w-8" />
                        <span class="text-base font-bold text-white">{{ $company->name ?? 'Business ERP' }}</span>
                    </div>
                    <p class="text-sm">{{ $company->tagline ?: 'Quality products, trusted service.' }}</p>
                </div>

                <div>
                    <p class="text-sm font-semibold text-white mb-3">Company</p>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('about') }}" class="hover:text-white">About</a></li>
                        <li><a href="{{ route('media') }}" class="hover:text-white">Media</a></li>
                        <li><a href="{{ route('career') }}" class="hover:text-white">Career</a></li>
                        @if ($company->ecommerce_enabled)
                            <li><a href="{{ route('shop') }}" class="hover:text-white">Online Store</a></li>
                        @endif
                    </ul>
                </div>

                <div>
                    <p class="text-sm font-semibold text-white mb-3">Get in Touch</p>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('contact') }}" class="hover:text-white">Contact Us</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-white">Staff Login</a></li>
                    </ul>
                </div>

                <div>
                    <p class="text-sm font-semibold text-white mb-3">Contact</p>
                    <ul class="space-y-2 text-sm">
                        @if ($company->email)
                            <li>{{ $company->email }}</li>
                        @endif
                        @if ($company->phone)
                            <li>{{ $company->phone }}</li>
                        @endif
                        @if ($company->address)
                            <li>{{ $company->address }}</li>
                        @endif
                    </ul>
                </div>
            </div>

            <div class="border-t border-white/10 py-6 text-center text-xs text-brand-300/70">
                &copy; {{ date('Y') }} {{ $company->name ?? 'Business ERP' }} &middot; Enterprise Suite
            </div>
        </footer>
    </body>
</html>
