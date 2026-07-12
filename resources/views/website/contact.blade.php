<x-website-layout :title="'Contact'">

    <section class="bg-gradient-to-br from-brand-950 via-brand-900 to-brand-800 py-20 text-center">
        <div class="mx-auto max-w-3xl px-4">
            <span class="inline-block rounded-full bg-white/10 px-4 py-1 text-xs font-semibold tracking-wide text-accent-300 ring-1 ring-white/20">
                Get in Touch
            </span>
            <h1 class="mt-6 text-4xl font-extrabold text-white">Let's talk about your business</h1>
        </div>
    </section>

    <section class="bg-white py-20 sm:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-10 lg:grid-cols-2">
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

                <div>
                    @include('website.partials.contact-form')
                </div>
            </div>
        </div>
    </section>

    <!-- Why Reach Out -->
    <section class="bg-slate-50 py-16 sm:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-brand-950 via-brand-900 to-brand-800 px-6 py-12 sm:px-12 sm:py-16">
                <div class="hero-blob pointer-events-none absolute -top-24 -right-24 h-72 w-72 rounded-full bg-accent-500/20 blur-3xl"></div>
                <div class="hero-blob hero-blob-delay pointer-events-none absolute -bottom-24 -left-24 h-72 w-72 rounded-full bg-brand-500/30 blur-3xl"></div>

                <div class="relative grid gap-10 sm:grid-cols-3 text-center">
                    <div>
                        <span class="mx-auto grid h-12 w-12 place-items-center rounded-2xl bg-white/10 text-accent-400 ring-1 ring-white/20">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                        </span>
                        <h3 class="mt-4 text-base font-bold text-white">Quick Response</h3>
                        <p class="mt-2 text-sm text-brand-200/90">We typically reply within 24 hours, every working day.</p>
                    </div>
                    <div>
                        <span class="mx-auto grid h-12 w-12 place-items-center rounded-2xl bg-white/10 text-accent-400 ring-1 ring-white/20">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z"/></svg>
                        </span>
                        <h3 class="mt-4 text-base font-bold text-white">Real Support</h3>
                        <p class="mt-2 text-sm text-brand-200/90">Talk to a real person who knows your account, not a bot.</p>
                    </div>
                    <div>
                        <span class="mx-auto grid h-12 w-12 place-items-center rounded-2xl bg-white/10 text-accent-400 ring-1 ring-white/20">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                        </span>
                        <h3 class="mt-4 text-base font-bold text-white">Multiple Locations</h3>
                        <p class="mt-2 text-sm text-brand-200/90">Prefer to visit? Drop by any of our branches below.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @include('website.partials.branches')

</x-website-layout>
