<x-website-layout :title="'Enterprise ERP Software'">

    <!-- Hero -->
    <section class="relative overflow-hidden bg-gradient-to-br from-brand-950 via-brand-900 to-brand-800">
        <div class="pointer-events-none absolute -top-32 -right-32 h-96 w-96 rounded-full bg-accent-500/20 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-32 -left-32 h-96 w-96 rounded-full bg-brand-500/30 blur-3xl"></div>

        <div class="relative z-10 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-24 sm:py-32 text-center">
            <span class="inline-block rounded-full bg-white/10 px-4 py-1 text-xs font-semibold tracking-wide text-accent-300 ring-1 ring-white/20">
                One ERP. Every product business.
            </span>
            <h1 class="mt-6 text-4xl sm:text-5xl lg:text-6xl font-extrabold text-white tracking-tight">
                Run purchase, production, sales &amp;
                <span class="text-accent-400">accounting</span> — in one place
            </h1>
            <p class="mt-6 max-w-2xl mx-auto text-lg text-brand-200/90">
                {{ $company->name ?? 'Business ERP' }} brings sourcing, inventory, sales, delivery, HR and finance
                together under role-based access — with an optional online store you can switch on anytime.
            </p>
            <div class="mt-10 flex flex-wrap items-center justify-center gap-4">
                <a href="#contact" class="rounded-lg bg-accent-500 px-6 py-3 text-sm font-semibold text-brand-950 hover:bg-accent-400">
                    Get Started
                </a>
                <a href="{{ route('login') }}" class="rounded-lg border border-white/20 px-6 py-3 text-sm font-semibold text-white hover:bg-white/10">
                    Login to Dashboard
                </a>
            </div>

            <!-- Stats -->
            <div class="mt-16 grid grid-cols-2 sm:grid-cols-4 gap-6 max-w-3xl mx-auto">
                <div>
                    <p class="text-3xl font-extrabold text-white">8+</p>
                    <p class="mt-1 text-sm text-brand-300/80">Core Modules</p>
                </div>
                <div>
                    <p class="text-3xl font-extrabold text-white">100%</p>
                    <p class="mt-1 text-sm text-brand-300/80">Role-based Access</p>
                </div>
                <div>
                    <p class="text-3xl font-extrabold text-white">Real-time</p>
                    <p class="mt-1 text-sm text-brand-300/80">Reports &amp; Insights</p>
                </div>
                <div>
                    <p class="text-3xl font-extrabold text-white">Optional</p>
                    <p class="mt-1 text-sm text-brand-300/80">E-commerce Storefront</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section id="features" class="bg-white py-20 sm:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto">
                <p class="text-sm font-semibold text-accent-600 tracking-wide uppercase">Why {{ $company->name ?? 'Business ERP' }}</p>
                <h2 class="mt-2 text-3xl sm:text-4xl font-extrabold text-brand-950 tracking-tight">
                    Built for how growing businesses actually work
                </h2>
            </div>

            <div class="mt-14 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                @php
                    $features = [
                        ['title' => 'Role-based Access', 'desc' => 'Give every team member exactly the permissions their role needs — nothing more.', 'icon' => 'M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z'],
                        ['title' => 'Real-time Reports', 'desc' => 'Track sales, stock and cash flow as they happen, not at month end.', 'icon' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z'],
                        ['title' => 'Multi-branch Ready', 'desc' => 'Manage several branches or warehouses from a single dashboard.', 'icon' => 'm21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9'],
                        ['title' => 'Optional Online Store', 'desc' => 'Switch on e-commerce anytime from Settings — no separate platform needed.', 'icon' => 'M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z'],
                        ['title' => 'Cloud-based', 'desc' => 'Access your business from anywhere, on any device, securely.', 'icon' => 'M2.25 15a4.5 4.5 0 0 0 4.5 4.5H18a3.75 3.75 0 0 0 1.332-7.257 3 3 0 0 0-3.758-3.848 5.25 5.25 0 0 0-10.233 2.33A4.502 4.502 0 0 0 2.25 15Z'],
                        ['title' => 'Audit-ready Accounting', 'desc' => 'Every transaction is traceable — built for VAT, BIN and financial-year reporting.', 'icon' => 'M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z'],
                    ];
                @endphp

                @foreach ($features as $feature)
                    <div class="rounded-2xl border border-slate-200 p-6 hover:shadow-lg hover:shadow-brand-100 transition">
                        <div class="grid h-12 w-12 place-items-center rounded-xl bg-brand-50 text-brand-700">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $feature['icon'] }}"/></svg>
                        </div>
                        <h3 class="mt-4 text-lg font-bold text-brand-950">{{ $feature['title'] }}</h3>
                        <p class="mt-2 text-sm text-slate-600">{{ $feature['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Modules -->
    <section id="modules" class="bg-slate-50 py-20 sm:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto">
                <p class="text-sm font-semibold text-accent-600 tracking-wide uppercase">End-to-end coverage</p>
                <h2 class="mt-2 text-3xl sm:text-4xl font-extrabold text-brand-950 tracking-tight">
                    Every module your business needs, none you don't
                </h2>
            </div>

            <div class="mt-14 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    'Sourcing & Purchase', 'Production', 'Sales & POS', 'Inventory',
                    'Accounts & Finance', 'Delivery', 'HR & Payroll', 'Reports & Analytics',
                ] as $module)
                    <div class="flex items-center gap-3 rounded-xl bg-white border border-slate-200 px-5 py-4">
                        <span class="h-2.5 w-2.5 rounded-full bg-accent-500 shrink-0"></span>
                        <span class="font-semibold text-brand-950">{{ $module }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    @if ($company->ecommerce_enabled)
    <!-- E-commerce teaser -->
    <section class="bg-white py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-3xl bg-gradient-to-br from-brand-900 to-brand-950 px-8 py-14 sm:px-16 text-center relative overflow-hidden">
                <div class="pointer-events-none absolute -top-16 -right-16 h-64 w-64 rounded-full bg-accent-500/20 blur-3xl"></div>
                <span class="inline-block rounded-full bg-accent-500/20 px-4 py-1 text-xs font-semibold tracking-wide text-accent-300 ring-1 ring-accent-400/30">
                    Now Live
                </span>
                <h2 class="mt-4 text-3xl sm:text-4xl font-extrabold text-white">We're also selling online</h2>
                <p class="mt-4 max-w-xl mx-auto text-brand-200/90">
                    Browse our products and order directly from our online store — powered by the same ERP that runs our business.
                </p>
                <a href="{{ route('shop') }}" class="mt-8 inline-block rounded-lg bg-accent-500 px-6 py-3 text-sm font-semibold text-brand-950 hover:bg-accent-400">
                    Visit Our Shop
                </a>
            </div>
        </div>
    </section>
    @endif

    <!-- About -->
    <section id="about" class="bg-slate-50 py-20 sm:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 text-center">
            <p class="text-sm font-semibold text-accent-600 tracking-wide uppercase">About Us</p>
            <h2 class="mt-2 text-3xl sm:text-4xl font-extrabold text-brand-950 tracking-tight">
                {{ $company->legal_name ?? $company->name ?? 'Business ERP' }}
            </h2>
            <p class="mt-6 text-lg text-slate-600 max-w-3xl mx-auto">
                We help product-based businesses replace spreadsheets and disconnected tools with one system —
                from sourcing raw materials to delivering finished orders and closing the books.
            </p>
        </div>
    </section>

    <!-- Contact -->
    <section id="contact" class="bg-white py-20 sm:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto">
                <p class="text-sm font-semibold text-accent-600 tracking-wide uppercase">Get in Touch</p>
                <h2 class="mt-2 text-3xl sm:text-4xl font-extrabold text-brand-950 tracking-tight">
                    Let's talk about your business
                </h2>
            </div>

            <div class="mt-12 grid gap-10 lg:grid-cols-2">
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
                        <p class="text-slate-500">Contact details will appear here once added in Admin &gt; Settings.</p>
                    @endif
                </div>

                <form class="space-y-4">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <input type="text" placeholder="Your name" class="w-full rounded-lg border-slate-300 focus:border-accent-500 focus:ring-accent-500">
                        <input type="email" placeholder="Your email" class="w-full rounded-lg border-slate-300 focus:border-accent-500 focus:ring-accent-500">
                    </div>
                    <textarea rows="4" placeholder="How can we help?" class="w-full rounded-lg border-slate-300 focus:border-accent-500 focus:ring-accent-500"></textarea>
                    <button type="button" class="w-full sm:w-auto rounded-lg bg-brand-900 px-6 py-3 text-sm font-semibold text-white hover:bg-brand-800">
                        Send Message
                    </button>
                    <p class="text-xs text-slate-400">This form is a preview — message sending will be wired up soon.</p>
                </form>
            </div>
        </div>
    </section>

</x-website-layout>
