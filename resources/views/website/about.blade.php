<x-website-layout :title="'About'">

    <section class="bg-gradient-to-br from-brand-950 via-brand-900 to-brand-800 py-20 text-center">
        <div class="mx-auto max-w-3xl px-4">
            <span class="inline-block rounded-full bg-white/10 px-4 py-1 text-xs font-semibold tracking-wide text-accent-300 ring-1 ring-white/20">
                About Us
            </span>
            <h1 class="mt-6 text-4xl font-extrabold text-white">{{ $company->legal_name ?: ($company->name ?? 'Business ERP') }}</h1>
        </div>
    </section>

    <section class="bg-white py-20 sm:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 text-center">
            <p class="text-lg text-slate-600">
                {{ $company->about_text ?: 'Details about our business will appear here once added in Admin > Settings > Website Settings.' }}
            </p>
        </div>

        @if ($company->mission_text || $company->vision_text)
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 mt-16 grid gap-6 {{ $company->mission_text && $company->vision_text ? 'sm:grid-cols-2' : '' }}">
            @if ($company->mission_text)
            <div class="rounded-2xl border border-slate-200 p-8">
                <h3 class="text-lg font-bold text-brand-950">Our Mission</h3>
                <p class="mt-2 text-sm text-slate-600">{{ $company->mission_text }}</p>
            </div>
            @endif
            @if ($company->vision_text)
            <div class="rounded-2xl border border-slate-200 p-8">
                <h3 class="text-lg font-bold text-brand-950">Our Vision</h3>
                <p class="mt-2 text-sm text-slate-600">{{ $company->vision_text }}</p>
            </div>
            @endif
        </div>
        @endif
    </section>

</x-website-layout>
