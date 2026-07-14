@if ($branches->isNotEmpty())
<!-- Branch Showcase -->
<section class="bg-white py-20 sm:py-28">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto">
            <p class="text-sm font-semibold text-accent-600 tracking-wide uppercase">{{ __('Find Us') }}</p>
            <h2 class="mt-2 text-3xl sm:text-4xl font-extrabold text-brand-950 tracking-tight">
                {{ __('Our Branches') }}
            </h2>
        </div>

        <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($branches as $branch)
                <div class="rounded-2xl border border-slate-200 p-6">
                    <span class="grid h-11 w-11 place-items-center rounded-xl bg-brand-50 text-brand-700">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                    </span>
                    <h3 class="mt-4 text-lg font-bold text-brand-950">{{ $branch->name }}</h3>
                    @if ($branch->address)
                        <p class="mt-1 text-sm text-slate-600">{{ $branch->address }}</p>
                    @endif
                    @if ($branch->phone)
                        <p class="mt-2 text-sm text-slate-500">{{ $branch->phone }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif
