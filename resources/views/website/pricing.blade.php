<x-website-layout :title="__('Pricing')">

    <!-- Hero -->
    <section class="relative overflow-hidden bg-gradient-to-br from-brand-950 via-brand-900 to-brand-800">
        <div class="hero-blob pointer-events-none absolute -top-32 -right-32 h-96 w-96 rounded-full bg-accent-500/20 blur-3xl"></div>
        <div class="hero-blob hero-blob-delay pointer-events-none absolute -bottom-32 -left-32 h-96 w-96 rounded-full bg-brand-500/30 blur-3xl"></div>

        <div class="relative z-10 mx-auto max-w-3xl px-4 py-20 sm:py-28 text-center">
            <span class="hero-in inline-block rounded-full bg-white/10 px-4 py-1 text-xs font-semibold tracking-wide text-accent-300 ring-1 ring-white/20" style="animation-delay:.05s">
                {{ __('Pricing') }}
            </span>
            <h1 class="hero-in mt-6 text-4xl sm:text-5xl font-extrabold text-white tracking-tight" style="animation-delay:.15s">
                {{ __('Own Your Software, Not a Subscription') }}
            </h1>
            <p class="hero-in mt-5 text-lg text-brand-200/90" style="animation-delay:.25s">
                {{ __('A one-time setup cost and a small monthly maintenance fee — no per-user pricing, no rising SaaS bills, no surprises.') }}
            </p>
        </div>
    </section>

    <!-- Why self-hosted, not SaaS -->
    <section class="bg-white py-20 sm:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto">
                <p class="text-sm font-semibold text-accent-600 tracking-wide uppercase">{{ __('Why Not SaaS') }}</p>
                <h2 class="mt-2 text-3xl sm:text-4xl font-extrabold text-brand-950 tracking-tight">
                    {{ __('Why hosting on your own domain is the smarter choice') }}
                </h2>
                <p class="mt-4 text-slate-600">
                    {{ __("With a typical SaaS subscription, your business data lives on someone else's server, under someone else's terms. We install the same software directly on your own domain and hosting — so it's fully yours.") }}
                </p>
            </div>

            <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl border border-slate-200 p-6">
                    <span class="grid h-11 w-11 place-items-center rounded-xl bg-brand-50 text-brand-700">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/></svg>
                    </span>
                    <h3 class="mt-4 font-bold text-brand-950">{{ __('Your Data Stays Yours') }}</h3>
                    <p class="mt-2 text-sm text-slate-600">{{ __('No third party ever sees your sales, customers or accounts — nothing is shared with us or anyone else after handover.') }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 p-6">
                    <span class="grid h-11 w-11 place-items-center rounded-xl bg-brand-50 text-brand-700">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>
                    </span>
                    <h3 class="mt-4 font-bold text-brand-950">{{ __('You Own It, Forever') }}</h3>
                    <p class="mt-2 text-sm text-slate-600">{{ __("Pay once to set it up. There's no per-user fee, no forced plan upgrade, and access never disappears over a missed subscription payment.") }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 p-6">
                    <span class="grid h-11 w-11 place-items-center rounded-xl bg-brand-50 text-brand-700">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 0 1 9-9m-9 9a9 9 0 0 0 9 9m9-9a9 9 0 0 0-9-9m9 9a9 9 0 0 1-9 9m-9-9h18"/></svg>
                    </span>
                    <h3 class="mt-4 font-bold text-brand-950">{{ __('Your Own Domain & Brand') }}</h3>
                    <p class="mt-2 text-sm text-slate-600">{{ __('Runs on yourcompany.com, under your own name — customers see your brand, never ours.') }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 p-6">
                    <span class="grid h-11 w-11 place-items-center rounded-xl bg-brand-50 text-brand-700">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                    </span>
                    <h3 class="mt-4 font-bold text-brand-950">{{ __('Predictable Low Cost') }}</h3>
                    <p class="mt-2 text-sm text-slate-600">{{ __('One flat monthly maintenance fee covers updates and support — not a bill that climbs as your business grows.') }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing tiers -->
    <section class="bg-slate-50 py-20 sm:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto">
                <p class="text-sm font-semibold text-accent-600 tracking-wide uppercase">{{ __('Transparent Pricing') }}</p>
                <h2 class="mt-2 text-3xl sm:text-4xl font-extrabold text-brand-950 tracking-tight">{{ __('Setup Once, Maintain Monthly') }}</h2>
                <p class="mt-4 text-slate-600">
                    {{ __('Every plan below gets the full software — the same features, with nothing locked away. Plans differ by business scale and support level, not by what you can use.') }}
                </p>
            </div>

            <div class="mt-14 grid gap-8 lg:grid-cols-3 lg:items-start">

                <!-- Starter -->
                <div class="rounded-3xl border border-slate-200 bg-white p-8">
                    <h3 class="text-xl font-bold text-brand-950">{{ __('Starter') }}</h3>
                    <p class="mt-1 text-sm text-slate-500">{{ __('For a single shop or small business just getting organized.') }}</p>

                    <div class="mt-6">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('One-time Setup') }}</p>
                        <p class="mt-1 text-3xl font-extrabold text-brand-950">৳25,000</p>
                        <p class="mt-1 text-xs text-slate-400">{{ __('Includes domain + hosting for the 1st year') }}</p>
                    </div>
                    <div class="mt-5 border-t border-slate-100 pt-5">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Monthly Maintenance') }}</p>
                        <p class="mt-1 text-2xl font-extrabold text-brand-950">৳1,500<span class="text-sm font-medium text-slate-400">/{{ __('month') }}</span></p>
                    </div>

                    <a href="{{ route('contact') }}" class="mt-8 block rounded-lg border border-brand-900 px-6 py-3 text-center text-sm font-semibold text-brand-900 hover:bg-brand-900 hover:text-white">
                        {{ __('Request a Quote') }}
                    </a>

                    <ul class="mt-8 space-y-3 text-sm text-slate-600">
                        <li class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-accent-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            {{ __('1 branch / outlet') }}
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-accent-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            {{ __('Up to 3 staff logins') }}
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-accent-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            {{ __('POS, Inventory, Sales & Purchase') }}
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-accent-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            {{ __('Basic accounting & due reports') }}
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-accent-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            {{ __('Email & ticket support') }}
                        </li>
                    </ul>
                </div>

                <!-- Growth (Most Popular) -->
                <div class="relative rounded-3xl border-2 border-accent-500 bg-white p-8 shadow-xl shadow-accent-500/10 lg:-translate-y-4">
                    <span class="absolute -top-3.5 left-1/2 -translate-x-1/2 rounded-full bg-accent-500 px-4 py-1 text-xs font-bold tracking-wide text-brand-950">
                        {{ __('MOST POPULAR') }}
                    </span>
                    <h3 class="text-xl font-bold text-brand-950">{{ __('Growth') }}</h3>
                    <p class="mt-1 text-sm text-slate-500">{{ __('For a multi-branch business ready to run everything from one system.') }}</p>

                    <div class="mt-6">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('One-time Setup') }}</p>
                        <p class="mt-1 text-3xl font-extrabold text-brand-950">৳55,000</p>
                        <p class="mt-1 text-xs text-slate-400">{{ __('Includes domain + hosting for the 1st year') }}</p>
                    </div>
                    <div class="mt-5 border-t border-slate-100 pt-5">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Monthly Maintenance') }}</p>
                        <p class="mt-1 text-2xl font-extrabold text-brand-950">৳3,000<span class="text-sm font-medium text-slate-400">/{{ __('month') }}</span></p>
                    </div>

                    <a href="{{ route('contact') }}" class="mt-8 block rounded-lg bg-brand-900 px-6 py-3 text-center text-sm font-semibold text-white hover:bg-brand-800">
                        {{ __('Request a Quote') }}
                    </a>

                    <ul class="mt-8 space-y-3 text-sm text-slate-600">
                        <li class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-accent-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            {{ __('Up to 5 branches / outlets') }}
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-accent-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            {{ __('Up to 15 staff logins') }}
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-accent-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            {{ __('Everything in Starter') }}
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-accent-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            {{ __('Full accounting (ledger, trial balance, balance sheet, P&L)') }}
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-accent-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            {{ __('HRM — attendance, leave & payroll') }}
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-accent-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            {{ __('Inter-branch stock transfer & courier integration') }}
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-accent-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            {{ __('Priority support') }}
                        </li>
                    </ul>
                </div>

                <!-- Enterprise -->
                <div class="rounded-3xl border border-slate-200 bg-white p-8">
                    <h3 class="text-xl font-bold text-brand-950">{{ __('Enterprise') }}</h3>
                    <p class="mt-1 text-sm text-slate-500">{{ __('For large or fast-growing operations that need a custom fit.') }}</p>

                    <div class="mt-6">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('One-time Setup') }}</p>
                        <p class="mt-1 text-3xl font-extrabold text-brand-950">{{ __('Starting ৳1,00,000') }}</p>
                        <p class="mt-1 text-xs text-slate-400">{{ __('Scoped to your branches, data migration & integrations') }}</p>
                    </div>
                    <div class="mt-5 border-t border-slate-100 pt-5">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Monthly Maintenance') }}</p>
                        <p class="mt-1 text-2xl font-extrabold text-brand-950">{{ __('Starting ৳6,000') }}<span class="text-sm font-medium text-slate-400">/{{ __('month') }}</span></p>
                    </div>

                    <a href="{{ route('contact') }}" class="mt-8 block rounded-lg border border-brand-900 px-6 py-3 text-center text-sm font-semibold text-brand-900 hover:bg-brand-900 hover:text-white">
                        {{ __('Talk to Sales') }}
                    </a>

                    <ul class="mt-8 space-y-3 text-sm text-slate-600">
                        <li class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-accent-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            {{ __('Unlimited branches & staff logins') }}
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-accent-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            {{ __('Everything in Growth') }}
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-accent-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            {{ __('Online store (e-commerce) add-on') }}
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-accent-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            {{ __('Project & task management') }}
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-accent-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            {{ __('On-site training & data migration') }}
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-accent-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            {{ __('Dedicated account manager') }}
                        </li>
                    </ul>
                </div>

            </div>

            <p class="mt-10 text-center text-sm text-slate-500">
                {{ __('Domain and hosting renew every year after year one — at cost, with no markup.') }}
            </p>
        </div>
    </section>

    <!-- Custom feature development -->
    <section class="bg-white py-16 sm:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-brand-950 via-brand-900 to-brand-800 px-6 py-14 sm:px-14 sm:py-16">
                <div class="hero-blob pointer-events-none absolute -top-24 -right-24 h-72 w-72 rounded-full bg-accent-500/20 blur-3xl"></div>
                <div class="hero-blob hero-blob-delay pointer-events-none absolute -bottom-24 -left-24 h-72 w-72 rounded-full bg-brand-500/30 blur-3xl"></div>

                <div class="relative grid gap-8 lg:grid-cols-[1fr_auto] lg:items-center">
                    <div>
                        <span class="inline-block rounded-full bg-white/10 px-4 py-1 text-xs font-semibold tracking-wide text-accent-300 ring-1 ring-white/20">
                            {{ __('Custom Development') }}
                        </span>
                        <h2 class="mt-4 text-2xl sm:text-3xl font-extrabold text-white">{{ __('Need a feature specific to your business?') }}</h2>
                        <p class="mt-3 max-w-2xl text-brand-200/90">
                            {{ __("Every business works a little differently. New reports, custom workflows, third-party integrations — we build it and quote it individually, based on the scope of work, not a fixed plan.") }}
                        </p>
                    </div>
                    <a href="{{ route('contact') }}" class="inline-block whitespace-nowrap rounded-lg bg-accent-500 px-8 py-3 text-center text-sm font-semibold text-brand-950 hover:bg-accent-400">
                        {{ __('Request a Quotation') }}
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Full feature list -->
    <section class="bg-slate-50 py-20 sm:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto">
                <p class="text-sm font-semibold text-accent-600 tracking-wide uppercase">{{ __('Full Feature List') }}</p>
                <h2 class="mt-2 text-3xl sm:text-4xl font-extrabold text-brand-950 tracking-tight">{{ __('Everything your business needs, in one system') }}</h2>
                <p class="mt-4 text-slate-600">{{ __('No feature is locked behind a higher plan — every business gets the full system.') }}</p>
            </div>

            @php
                $featureGroups = [
                    ['title' => __('Sales & POS'), 'items' => [
                        __('Point of Sale (POS) billing'),
                        __('Sales orders & invoices'),
                        __('Sales quotations with approval flow'),
                        __('Sales returns'),
                        __('Delivery management'),
                        __('Multi-branch sales'),
                    ]],
                    ['title' => __('Inventory & Purchase'), 'items' => [
                        __('Products, variants, categories, brands & units'),
                        __('Barcode generation & printing'),
                        __('Purchase orders & requisitions'),
                        __('Purchase returns'),
                        __('Stock transfer between branches'),
                        __('Stock adjustment & internal consumption'),
                        __('Stock reports'),
                    ]],
                    ['title' => __('Accounting & Finance'), 'items' => [
                        __('Bank accounts & ledger'),
                        __('Payments & collections'),
                        __('Expenses & incomes'),
                        __('Fund transfers & capital transactions'),
                        __('Day book'),
                        __('Trial balance, balance sheet & profit-loss'),
                        __('Customer & supplier due report'),
                    ]],
                    ['title' => __('HRM & Payroll'), 'items' => [
                        __('Employee records & documents'),
                        __('Departments & designations'),
                        __('Attendance register & self check-in/out'),
                        __('Leave requests & approvals'),
                        __('Payroll runs & payslips'),
                    ]],
                    ['title' => __('Contacts & Delivery'), 'items' => [
                        __('Customer & supplier ledger'),
                        __('Due tracking'),
                        __('Delivery partner management'),
                        __('Courier consignment tracking & COD settlement'),
                        __('Delivery zones & charges'),
                    ]],
                    ['title' => __('Projects & Tasks'), 'items' => [
                        __('Projects & milestones'),
                        __('Tasks with comments'),
                        __('File attachments'),
                        __('Time logs'),
                    ]],
                    ['title' => __('Online Store (Add-on)'), 'items' => [
                        __('Product catalog & search'),
                        __('Cart & guest checkout'),
                        __('Order tracking'),
                        __('Product reviews'),
                        __('Campaign landing pages'),
                        __('SSLCommerz payment gateway'),
                    ]],
                    ['title' => __('Website & Access Control'), 'items' => [
                        __('Company website — About, Career, Media, Contact'),
                        __('Bengali + English language switch'),
                        __('Role-based user permissions'),
                        __('Unlimited branches / sites'),
                    ]],
                ];
            @endphp

            <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($featureGroups as $group)
                <div class="rounded-2xl border border-slate-200 bg-white p-6">
                    <h3 class="font-bold text-brand-950">{{ $group['title'] }}</h3>
                    <ul class="mt-4 space-y-2.5 text-sm text-slate-600">
                        @foreach ($group['items'] as $item)
                        <li class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-accent-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            <span>{{ $item }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- FAQ -->
    <section class="bg-white py-20 sm:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-3xl sm:text-4xl font-extrabold text-brand-950 tracking-tight">{{ __('Frequently Asked Questions') }}</h2>
            </div>

            <div class="mt-12 divide-y divide-slate-200 rounded-2xl border border-slate-200">

                <div class="p-6" x-data="{ open: true }">
                    <button @click="open = !open" type="button" class="flex w-full items-center justify-between gap-4 text-left">
                        <span class="font-semibold text-brand-950">{{ __('Is this a monthly subscription, like SaaS?') }}</span>
                        <svg class="h-4 w-4 shrink-0 text-slate-400 transition-transform" :class="open && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                    </button>
                    <p x-show="open" x-transition class="mt-3 text-sm text-slate-600">
                        {{ __("No. You pay a one-time setup charge — which covers installation, your domain and the first year of hosting — and then a small fixed monthly maintenance fee. There's no per-user or per-order charge that grows with your sales.") }}
                    </p>
                </div>

                <div class="p-6" x-data="{ open: false }">
                    <button @click="open = !open" type="button" class="flex w-full items-center justify-between gap-4 text-left">
                        <span class="font-semibold text-brand-950">{{ __('Why is hosting it ourselves better than a SaaS platform?') }}</span>
                        <svg class="h-4 w-4 shrink-0 text-slate-400 transition-transform" :class="open && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                    </button>
                    <p x-show="open" x-transition style="display:none" class="mt-3 text-sm text-slate-600">
                        {{ __("On a shared SaaS platform, your customer list, sales and financial data sit on the vendor's servers alongside every other business using it. With your own domain and hosting, that data never leaves your control — and you're never locked out if a subscription plan changes or a provider shuts down.") }}
                    </p>
                </div>

                <div class="p-6" x-data="{ open: false }">
                    <button @click="open = !open" type="button" class="flex w-full items-center justify-between gap-4 text-left">
                        <span class="font-semibold text-brand-950">{{ __('What does the monthly maintenance fee cover?') }}</span>
                        <svg class="h-4 w-4 shrink-0 text-slate-400 transition-transform" :class="open && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                    </button>
                    <p x-show="open" x-transition style="display:none" class="mt-3 text-sm text-slate-600">
                        {{ __('Server monitoring, security & software updates, backups, and standard support when something needs fixing. It does not include new custom features — those are quoted separately.') }}
                    </p>
                </div>

                <div class="p-6" x-data="{ open: false }">
                    <button @click="open = !open" type="button" class="flex w-full items-center justify-between gap-4 text-left">
                        <span class="font-semibold text-brand-950">{{ __('What happens after the first year of domain & hosting?') }}</span>
                        <svg class="h-4 w-4 shrink-0 text-slate-400 transition-transform" :class="open && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                    </button>
                    <p x-show="open" x-transition style="display:none" class="mt-3 text-sm text-slate-600">
                        {{ __('Domain and hosting renew annually at cost — we pass through the actual renewal price with no markup, billed once a year.') }}
                    </p>
                </div>

                <div class="p-6" x-data="{ open: false }">
                    <button @click="open = !open" type="button" class="flex w-full items-center justify-between gap-4 text-left">
                        <span class="font-semibold text-brand-950">{{ __('Can we request features that are not in the standard system?') }}</span>
                        <svg class="h-4 w-4 shrink-0 text-slate-400 transition-transform" :class="open && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                    </button>
                    <p x-show="open" x-transition style="display:none" class="mt-3 text-sm text-slate-600">
                        {{ __('Yes — custom features, reports and integrations are common. Tell us what you need and we will send a written quotation before any work starts.') }}
                    </p>
                </div>

                <div class="p-6" x-data="{ open: false }">
                    <button @click="open = !open" type="button" class="flex w-full items-center justify-between gap-4 text-left">
                        <span class="font-semibold text-brand-950">{{ __('What if we stop paying the monthly maintenance fee?') }}</span>
                        <svg class="h-4 w-4 shrink-0 text-slate-400 transition-transform" :class="open && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                    </button>
                    <p x-show="open" x-transition style="display:none" class="mt-3 text-sm text-slate-600">
                        {{ __("The software keeps running — it's installed on your own server, not ours. You simply stop receiving updates and priority support until the plan is resumed.") }}
                    </p>
                </div>

                <div class="p-6" x-data="{ open: false }">
                    <button @click="open = !open" type="button" class="flex w-full items-center justify-between gap-4 text-left">
                        <span class="font-semibold text-brand-950">{{ __('How secure is our data with this setup?') }}</span>
                        <svg class="h-4 w-4 shrink-0 text-slate-400 transition-transform" :class="open && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                    </button>
                    <p x-show="open" x-transition style="display:none" class="mt-3 text-sm text-slate-600">
                        {{ __("Since everything runs on your own domain and server, only your team has access. There's no shared multi-tenant database and no other business anywhere near your data.") }}
                    </p>
                </div>

            </div>
        </div>
    </section>

    <!-- Final CTA -->
    <section class="bg-slate-50 pb-20 sm:pb-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-brand-950 via-brand-900 to-brand-800 px-6 py-14 sm:px-14 sm:py-16 text-center">
                <div class="hero-blob pointer-events-none absolute -top-24 -right-24 h-72 w-72 rounded-full bg-accent-500/20 blur-3xl"></div>
                <div class="hero-blob hero-blob-delay pointer-events-none absolute -bottom-24 -left-24 h-72 w-72 rounded-full bg-brand-500/30 blur-3xl"></div>

                <div class="relative">
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-white">{{ __('Ready to run your business on your own system?') }}</h2>
                    <p class="mt-3 max-w-xl mx-auto text-brand-200/90">{{ __('Tell us about your business and we will recommend the right plan and setup timeline.') }}</p>
                    <a href="{{ route('contact') }}" class="mt-8 inline-block rounded-lg bg-accent-500 px-8 py-3 text-sm font-semibold text-brand-950 hover:bg-accent-400">
                        {{ __('Get a Free Consultation') }}
                    </a>
                </div>
            </div>
        </div>
    </section>

</x-website-layout>
