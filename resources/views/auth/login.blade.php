<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Sign In') }} — {{ config('app.name', 'Business ERP') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link
        href="https://fonts.bunny.net/css?family=figtree:400,500,600,700|fraunces:300,400,500,600italic,300italic|ibm-plex-mono:500,600&display=swap"
        rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .font-display {
            font-family: 'Fraunces', ui-serif, Georgia, serif;
        }

        .font-mono-label {
            font-family: 'IBM Plex Mono', ui-monospace, monospace;
        }

        .ledger-row {
            opacity: 0;
            transform: translateY(6px);
            animation: ledger-in .5s ease-out forwards;
        }

        .ledger-row:nth-child(1) {
            animation-delay: .12s;
        }

        .ledger-row:nth-child(2) {
            animation-delay: .22s;
        }

        .ledger-row:nth-child(3) {
            animation-delay: .32s;
        }

        .ledger-row:nth-child(4) {
            animation-delay: .42s;
        }

        .ledger-row:nth-child(5) {
            animation-delay: .52s;
        }

        .ledger-row:nth-child(6) {
            animation-delay: .62s;
        }

        @keyframes ledger-in {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .ledger-row {
                opacity: 1;
                transform: none;
                animation: none;
            }
        }
    </style>
</head>

<body class="font-sans antialiased text-slate-900">
    <div class="min-h-screen lg:grid lg:grid-cols-[minmax(0,42%)_1fr]">

        {{-- Left: brand + module ledger --}}
        <div
            class="relative overflow-hidden bg-gradient-to-b from-[#081c4e] to-[#050e2e] px-8 py-10 sm:px-12 sm:py-14 lg:flex lg:flex-col lg:justify-between">
            <div class="pointer-events-none absolute inset-0 opacity-[0.07]"
                style="background-image: linear-gradient(#67e8f9 1px, transparent 1px), linear-gradient(90deg, #67e8f9 1px, transparent 1px); background-size: 28px 28px;">
            </div>

            <div class="relative">
                <a href="/" class="inline-flex items-center gap-3">
                    <x-application-logo class="h-11 w-11 shrink-0" />
                    <span class="font-mono-label text-[11px] tracking-[0.15em] text-accent-300/80 uppercase">{{
                        __('Unified ERP for Retail, Wholesale & Online Business') }}</span>
                </a>

                <h1
                    class="font-display mt-10 max-w-sm text-[2.35rem] font-light leading-[1.15] text-white sm:text-[2.65rem]">
                    {{ __('Every sale.') }} {{ __('Every transaction.') }} <span class="italic text-accent-300">{{
                        __('One system.') }}</span>
                </h1>
                <p class="mt-4 max-w-xs text-sm leading-relaxed text-brand-200/80">
                    {{ __('The operational backbone for modern businesses — from purchasing and inventory to accounting,
                    POS, and eCommerce, all in one platform.') }}
                </p>
            </div>

            <div class="relative mt-12 lg:mt-0">
                <p class="font-mono-label mb-3 text-[10px] tracking-[0.18em] text-brand-300/60 uppercase">{{ __('What\'s
                    inside') }}</p>
                <div class="divide-y divide-white/10 border-y border-white/10">
                    @foreach ([__('Purchase, Sales & POS'), __('Inventory & Multi-Warehouse'), __('E-commerce & Delivery'), __('Accounts & Reports'), __('Customer & Supplier Management'), __('HRM')] as $module)
                    <div class="ledger-row flex items-center justify-between py-2.5">
                        <span class="text-sm font-medium text-brand-50">{{ $module }}</span>
                        <span
                            class="font-mono-label flex items-center gap-1.5 text-[10px] tracking-wide text-accent-300/90 uppercase">
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                            {{ __('Included') }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Right: sign-in form --}}
        <div class="flex flex-col justify-center bg-[#fbf9f4] px-6 py-12 sm:px-12 lg:px-20">
            <div class="mx-auto w-full max-w-sm">
                <p class="font-mono-label text-[10px] tracking-[0.18em] text-brand-400 uppercase">{{ __('Welcome back')
                    }}</p>
                <h2 class="font-display mt-1.5 text-3xl font-medium text-[#1c2333]">{{ __('Sign in') }}</h2>
                <p class="mt-2 text-sm text-slate-500">{{ __('Enter your workspace credentials to continue.') }}</p>

                @if (session('status'))
                <div
                    class="mt-5 rounded-lg bg-emerald-50 px-4 py-2.5 text-sm font-medium text-emerald-700 ring-1 ring-emerald-200">
                    {{ session('status') }}
                </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
                    @csrf

                    <div>
                        <label for="email"
                            class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{
                            __('Email') }}</label>
                        <div class="relative">
                            <svg class="pointer-events-none absolute left-3 top-1/2 h-4.5 w-4.5 -translate-y-1/2 text-slate-400"
                                fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                            </svg>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                                autocomplete="username"
                                class="w-full rounded-lg border-slate-300 bg-white pl-10 text-sm focus:border-brand-500 focus:ring-brand-500">
                        </div>
                        @error('email') <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="password"
                            class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{
                            __('Password') }}</label>
                        <div class="relative">
                            <svg class="pointer-events-none absolute left-3 top-1/2 h-4.5 w-4.5 -translate-y-1/2 text-slate-400"
                                fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                            </svg>
                            <input id="password" type="password" name="password" required
                                autocomplete="current-password"
                                class="w-full rounded-lg border-slate-300 bg-white pl-10 text-sm focus:border-brand-500 focus:ring-brand-500">
                        </div>
                        @error('password') <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <label for="remember_me" class="inline-flex items-center gap-2 text-sm text-slate-600">
                            <input id="remember_me" type="checkbox" name="remember"
                                class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                            {{ __('Remember me') }}
                        </label>

                        @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}"
                            class="text-sm font-medium text-brand-600 hover:text-brand-800">{{ __('Forgot password?')
                            }}</a>
                        @endif
                    </div>

                    <button type="submit"
                        class="w-full rounded-lg bg-gradient-to-r from-brand-700 to-brand-600 px-4 py-3 text-sm font-semibold text-white shadow-sm shadow-brand-900/20 transition hover:from-brand-600 hover:to-brand-500 focus:outline-none focus:ring-2 focus:ring-accent-500 focus:ring-offset-2">
                        {{ __('Sign in') }}
                    </button>
                </form>

                @if (app()->environment('local'))
                @php
                $demoAccounts = [
                ['role' => 'Super Admin', 'email' => 'admin@businesserp.test'],
                ['role' => 'Company Admin', 'email' => 'owner@businesserp.test'],
                ['role' => 'Branch Manager', 'email' => 'manager@businesserp.test'],
                ['role' => 'HR Executive', 'email' => 'hr@businesserp.test'],
                ['role' => 'Head Accountant', 'email' => 'accountant@businesserp.test'],
                ];
                @endphp
                <div class="mt-8 border-t border-slate-200 pt-5">
                    <p class="font-mono-label text-[10px] tracking-[0.18em] text-slate-400 uppercase">{{ __('Quick
                        fill') }} &middot; {{ __('dev only') }}</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach ($demoAccounts as $account)
                        <button type="button"
                            onclick="document.getElementById('email').value = '{{ $account['email'] }}'; document.getElementById('password').value = 'password';"
                            class="rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 hover:border-brand-300 hover:bg-brand-50 hover:text-brand-800">
                            {{ $account['role'] }}
                        </button>
                        @endforeach
                    </div>
                </div>
                @endif

                <p class="mt-10 text-center text-xs text-slate-400">
                    &copy; {{ date('Y') }} {{ config('app.name', 'Business ERP') }} &middot; {{ __('Enterprise Suite')
                    }}
                    &middot; {{ __('Powered by') }} <a href="https://vexasoft.net" target="_blank" rel="noopener"
                        class="font-semibold text-slate-500 hover:text-brand-700">Vexasoft</a>
                </p>
            </div>
        </div>
    </div>
</body>

</html>