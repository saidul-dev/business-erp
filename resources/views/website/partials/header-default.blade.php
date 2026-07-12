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
                <a href="{{ route('contact') }}" class="text-sm font-medium {{ request()->routeIs('contact') ? 'text-white' : 'text-brand-100 hover:text-white' }}">Contact</a>
            </div>

            <div class="hidden lg:flex items-center gap-3">
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
