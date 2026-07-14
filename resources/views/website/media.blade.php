<x-website-layout :title="__('Media')">

    <section class="bg-gradient-to-br from-brand-950 via-brand-900 to-brand-800 py-20 text-center">
        <div class="mx-auto max-w-3xl px-4">
            <span class="inline-block rounded-full bg-white/10 px-4 py-1 text-xs font-semibold tracking-wide text-accent-300 ring-1 ring-white/20">
                {{ __('Media') }}
            </span>
            <h1 class="mt-6 text-4xl font-extrabold text-white">{{ __('Photos & Press') }}</h1>
            <p class="mt-4 text-brand-200/90">{{ __('A look at our shop, products and team — updated regularly.') }}</p>
        </div>
    </section>

    <section class="bg-white py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @for ($i = 0; $i < 8; $i++)
                <div class="rounded-2xl border border-slate-200 overflow-hidden">
                    <div class="aspect-square bg-slate-100 grid place-items-center text-slate-300">
                        <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3 3h18M3 3l1.5 18h15L21 3H3Z"/></svg>
                    </div>
                    <div class="p-4">
                        <p class="font-semibold text-brand-950">{{ __('Coming soon') }}</p>
                        <p class="mt-1 text-sm text-slate-400">{{ __('Gallery placeholder') }}</p>
                    </div>
                </div>
            @endfor
        </div>
    </section>

</x-website-layout>
