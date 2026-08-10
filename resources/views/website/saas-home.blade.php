<x-website-layout>

    <!-- Hero -->
    <section class="bg-gradient-to-br from-brand-950 via-brand-900 to-brand-800 py-24 text-center">
        <div class="mx-auto max-w-3xl px-4">
            <span class="inline-block rounded-full bg-white/10 px-4 py-1 text-xs font-semibold tracking-wide text-accent-300 ring-1 ring-white/20">
                {{ __('Restaurant ERP & POS, built for Bangladesh') }}
            </span>
            <h1 class="mt-6 text-4xl sm:text-5xl font-extrabold text-white">
                {{ __('Run your restaurant, every branch, one dashboard') }}
            </h1>
            <p class="mt-4 max-w-xl mx-auto text-brand-200/90">
                {{ __('Menu, inventory, staff, and accounts — for a single outlet or a growing chain. Start free, upgrade only when you outgrow the trial.') }}
            </p>
            <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                <a href="{{ route('register') }}" class="rounded-lg bg-accent-600 px-6 py-3 text-sm font-semibold text-white hover:bg-accent-700">
                    {{ __('Start Free Trial') }}
                </a>
                <a href="{{ route('login') }}" class="rounded-lg bg-white/10 px-6 py-3 text-sm font-semibold text-white ring-1 ring-white/20 hover:bg-white/20">
                    {{ __('Login') }}
                </a>
            </div>
            <p class="mt-4 text-xs text-brand-300/70">{{ __('30-day free trial · No card required · 1 branch included') }}</p>
        </div>
    </section>

    <!-- Feature highlights -->
    <section class="bg-white py-20 sm:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto">
                <h2 class="text-2xl sm:text-3xl font-bold text-brand-950">{{ __('Everything a restaurant needs to run') }}</h2>
                <p class="mt-3 text-slate-500">{{ __('One platform for the floor, the kitchen, the books, and the branches.') }}</p>
            </div>

            <div class="mt-12 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach ([
                    ['title' => __('Multi-Branch'), 'desc' => __('Outlets, central kitchen, and warehouse under one account.')],
                    ['title' => __('Menu & Inventory'), 'desc' => __('Categories, items, variants, units — stock tracked per branch.')],
                    ['title' => __('HR & Payroll'), 'desc' => __('Attendance, leave, salary structures, and payroll runs.')],
                    ['title' => __('Accounting'), 'desc' => __('Cash book, bank accounts, ledger, P&L, and balance sheet.')],
                ] as $feature)
                <div class="rounded-2xl bg-slate-50 p-6 ring-1 ring-slate-100">
                    <span class="grid h-11 w-11 place-items-center rounded-xl bg-brand-900 text-white">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                    </span>
                    <h3 class="mt-4 font-bold text-brand-950">{{ $feature['title'] }}</h3>
                    <p class="mt-2 text-sm text-slate-500">{{ $feature['desc'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Pricing -->
    <section class="bg-slate-50 py-20 sm:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto">
                <h2 class="text-2xl sm:text-3xl font-bold text-brand-950">{{ __('Simple, branch-based pricing') }}</h2>
                <p class="mt-3 text-slate-500">{{ __('Start on the free trial. Upgrade only when you open more branches.') }}</p>
            </div>

            <div class="mt-12 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach ($plans as $plan)
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 flex flex-col">
                    <h3 class="font-bold text-brand-950">{{ $plan->name }}</h3>
                    <p class="mt-1 text-xs text-slate-400">{{ $plan->description }}</p>
                    <p class="mt-4 text-2xl font-extrabold text-brand-950">
                        {{ $plan->price > 0 ? number_format($plan->price).' '.__('BDT') : __('Free') }}
                        <span class="text-xs font-medium text-slate-400">/ {{ __(ucfirst($plan->billing_cycle)) }}</span>
                    </p>
                    <p class="mt-2 text-sm text-slate-500">{{ __('Up to :count branches', ['count' => $plan->max_branches]) }}</p>
                    <a href="{{ route('register') }}" class="mt-6 block w-full rounded-lg bg-brand-900 px-4 py-2.5 text-center text-sm font-semibold text-white hover:bg-brand-800">
                        {{ __('Get Started') }}
                    </a>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="bg-brand-950 py-16 text-center">
        <div class="mx-auto max-w-2xl px-4">
            <h2 class="text-2xl sm:text-3xl font-bold text-white">{{ __('Ready to run your restaurant better?') }}</h2>
            <p class="mt-3 text-brand-200/90">{{ __('Register your restaurant and be up and running in minutes.') }}</p>
            <a href="{{ route('register') }}" class="mt-6 inline-block rounded-lg bg-accent-600 px-6 py-3 text-sm font-semibold text-white hover:bg-accent-700">
                {{ __('Start Free Trial') }}
            </a>
        </div>
    </section>

</x-website-layout>
