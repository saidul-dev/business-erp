<x-website-layout :title="'Career'">

    <section class="bg-gradient-to-br from-brand-950 via-brand-900 to-brand-800 py-20 text-center">
        <div class="mx-auto max-w-3xl px-4">
            <span class="inline-block rounded-full bg-white/10 px-4 py-1 text-xs font-semibold tracking-wide text-accent-300 ring-1 ring-white/20">
                Careers
            </span>
            <h1 class="mt-6 text-4xl font-extrabold text-white">We're Growing — Join Us</h1>
            <p class="mt-4 max-w-xl mx-auto text-brand-200/90">
                We're always looking for good people. Send us your CV and we'll reach out when a role fits.
            </p>
        </div>
    </section>

    <section class="bg-white py-20 sm:py-28">
        <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8 text-center">
            <span class="grid h-14 w-14 mx-auto place-items-center rounded-2xl bg-brand-50 text-brand-700">
                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
            </span>
            <h2 class="mt-6 text-2xl font-bold text-brand-950">No open positions listed right now</h2>
            <p class="mt-3 text-slate-600">
                That doesn't mean we're not hiring. Email your CV and a short note about what you're looking for, and we'll keep it on file.
            </p>
            @if ($company->email)
            <a href="mailto:{{ $company->email }}" class="mt-8 inline-block rounded-lg bg-brand-900 px-6 py-3 text-sm font-semibold text-white hover:bg-brand-800">
                Email Your CV
            </a>
            @else
            <p class="mt-8 text-sm text-slate-400">Contact details will appear here once added in Admin &gt; Settings.</p>
            @endif
        </div>
    </section>

</x-website-layout>
