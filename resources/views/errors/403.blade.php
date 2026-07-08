<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Access Denied — {{ config('app.name', 'Business ERP') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gradient-to-br from-brand-950 via-brand-900 to-brand-800 relative overflow-hidden">
            <div class="pointer-events-none absolute -top-32 -right-32 h-96 w-96 rounded-full bg-accent-500/20 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-32 -left-32 h-96 w-96 rounded-full bg-rose-500/20 blur-3xl"></div>

            <div class="relative z-10 flex flex-col items-center">
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <x-application-logo class="w-14 h-14" />
                    <div class="leading-tight">
                        <span class="block text-2xl font-bold text-white tracking-wide">Business ERP</span>
                        <span class="block text-xs font-medium text-accent-400">Any product business. Only the modules you need.</span>
                    </div>
                </a>
            </div>

            <div class="relative z-10 w-full sm:max-w-md mt-8 px-8 py-10 bg-white shadow-2xl shadow-brand-950/50 overflow-hidden sm:rounded-2xl text-center">
                <div class="mx-auto grid h-16 w-16 place-items-center rounded-2xl bg-rose-50 ring-1 ring-rose-200">
                    <svg class="h-8 w-8 text-rose-500" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/>
                    </svg>
                </div>

                <h1 class="mt-5 text-xl font-bold text-brand-950">Access Denied</h1>
                <p class="mt-2 text-sm text-slate-500">
                    {{ $exception->getMessage() ?: "You don't have permission to view this page." }}
                </p>
                <p class="mt-1 text-xs text-slate-400">
                    If you think this is a mistake, contact your administrator to review your role and permissions.
                </p>

                <div class="mt-6 flex flex-col sm:flex-row items-center justify-center gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}" class="w-full sm:w-auto rounded-lg bg-brand-800 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">
                            Back to Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="w-full sm:w-auto rounded-lg bg-brand-800 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">
                            Log In
                        </a>
                    @endauth
                </div>
            </div>

            <p class="relative z-10 mt-6 text-xs text-brand-300/70">&copy; {{ date('Y') }} Business ERP &middot; Enterprise Suite</p>
        </div>
    </body>
</html>
