<x-website-layout :title="'About'">

    <!-- Hero -->
    <section class="relative overflow-hidden bg-gradient-to-br from-brand-950 via-brand-900 to-brand-800">
        <div class="hero-blob pointer-events-none absolute -top-32 -right-32 h-96 w-96 rounded-full bg-accent-500/20 blur-3xl"></div>
        <div class="hero-blob hero-blob-delay pointer-events-none absolute -bottom-32 -left-32 h-96 w-96 rounded-full bg-brand-500/30 blur-3xl"></div>

        <div class="relative z-10 mx-auto max-w-3xl px-4 py-20 sm:py-28 text-center">
            <span class="hero-in inline-block rounded-full bg-white/10 px-4 py-1 text-xs font-semibold tracking-wide text-accent-300 ring-1 ring-white/20" style="animation-delay:.05s">
                About Us
            </span>
            <h1 class="hero-in mt-6 text-4xl sm:text-5xl font-extrabold text-white tracking-tight" style="animation-delay:.15s">
                {{ $company->legal_name ?: ($company->name ?? 'Business ERP') }}
            </h1>
            @if ($company->tagline)
            <p class="hero-in mt-4 text-lg text-brand-200/90" style="animation-delay:.25s">
                {{ $company->tagline }}
            </p>
            @endif

            @if ($stats['products'] > 0 || $stats['categories'] > 0 || $stats['branches'] > 1)
            <div class="hero-in mt-14 flex flex-wrap items-center justify-center gap-x-12 gap-y-6" style="animation-delay:.35s">
                @if ($stats['products'] > 0)
                <div>
                    <p class="text-3xl font-extrabold text-white">{{ $stats['products'] }}+</p>
                    <p class="mt-1 text-sm text-brand-300/80">Products</p>
                </div>
                @endif
                @if ($stats['categories'] > 0)
                <div>
                    <p class="text-3xl font-extrabold text-white">{{ $stats['categories'] }}</p>
                    <p class="mt-1 text-sm text-brand-300/80">{{ trans_choice('Category|Categories', $stats['categories']) }}</p>
                </div>
                @endif
                @if ($stats['branches'] > 1)
                <div>
                    <p class="text-3xl font-extrabold text-white">{{ $stats['branches'] }}</p>
                    <p class="mt-1 text-sm text-brand-300/80">Branches</p>
                </div>
                @endif
            </div>
            @endif
        </div>
    </section>

    <!-- Our Story -->
    <section class="bg-white py-20 sm:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 text-center">
            <span class="mx-auto grid h-12 w-12 place-items-center rounded-2xl bg-brand-50 text-brand-700">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334c.13.093.259.192.386.296m-9.731 8.038a2.101 2.101 0 0 1-.545-.142m9.345-8.334C18.882 8.184 17.36 8 15.75 8c-2.001 0-3.958.213-5.845.616M6.75 15.75c-.681.045-1.362.096-2.043.153-1.132.094-1.98 1.057-1.98 2.193v4.286c0 .97.616 1.814 1.5 2.097M6.75 15.75l-3 3M6.75 15.75c1.326 0 2.652.055 3.978.163M15.75 8c0-1.708-.086-3.395-.254-5.058A2.25 2.25 0 0 0 13.365 1H10.634a2.25 2.25 0 0 0-2.14 1.943c-.086.7-.16 1.404-.222 2.11M15.75 8c-1.708 0-3.395.086-5.058.254M8.272 5.053a48.937 48.937 0 0 0-1.522.121m1.522-.121-.221 2.11"/></svg>
            </span>
            <h2 class="mt-6 text-2xl sm:text-3xl font-extrabold text-brand-950 tracking-tight">Our Story</h2>
            <p class="mt-5 text-lg leading-relaxed text-slate-600">
                {{ $company->about_text ?: 'Details about our business will appear here once added in Admin > Settings > Website Settings.' }}
            </p>
        </div>
    </section>

    @include('website.partials.mission-vision-values')

    @if ($company->legal_name || $company->vat_registration_no || $company->bin_no)
    <!-- Company Details -->
    <section class="bg-slate-50 py-20 sm:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto">
                <p class="text-sm font-semibold text-accent-600 tracking-wide uppercase">Registered &amp; Verified</p>
                <h2 class="mt-2 text-3xl sm:text-4xl font-extrabold text-brand-950 tracking-tight">
                    Company Details
                </h2>
            </div>

            <div class="mt-14 mx-auto max-w-3xl rounded-2xl bg-white border border-slate-200 divide-y divide-slate-100">
                @if ($company->legal_name)
                <div class="flex items-center justify-between gap-4 px-6 py-4">
                    <span class="text-sm font-medium text-slate-500">Legal Name</span>
                    <span class="text-sm font-semibold text-brand-950 text-right">{{ $company->legal_name }}</span>
                </div>
                @endif
                @if ($company->vat_registration_no)
                <div class="flex items-center justify-between gap-4 px-6 py-4">
                    <span class="text-sm font-medium text-slate-500">VAT Registration No.</span>
                    <span class="text-sm font-semibold text-brand-950 text-right">{{ $company->vat_registration_no }}</span>
                </div>
                @endif
                @if ($company->bin_no)
                <div class="flex items-center justify-between gap-4 px-6 py-4">
                    <span class="text-sm font-medium text-slate-500">BIN No.</span>
                    <span class="text-sm font-semibold text-brand-950 text-right">{{ $company->bin_no }}</span>
                </div>
                @endif
            </div>
        </div>
    </section>
    @endif

</x-website-layout>
