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
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
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
