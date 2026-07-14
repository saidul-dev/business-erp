@if ($company->mission_text || $company->vision_text || $company->values_text)
<!-- Mission, Vision & Values -->
<section class="bg-slate-50 py-20 sm:py-28">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto">
            <p class="text-sm font-semibold text-accent-600 tracking-wide uppercase">{{ __('What Drives Us') }}</p>
            <h2 class="mt-2 text-3xl sm:text-4xl font-extrabold text-brand-950 tracking-tight">
                {{ __('Mission, Vision & Values') }}
            </h2>
        </div>

        @php
            $missionCards = collect([
                $company->mission_text ? ['title' => __('Our Mission'), 'text' => $company->mission_text, 'gradient' => 'from-brand-600 to-brand-800', 'icon' => 'M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m-15.432 0A8.959 8.959 0 0 1 3 12c0-.778.099-1.533.284-2.253'] : null,
                $company->vision_text ? ['title' => __('Our Vision'), 'text' => $company->vision_text, 'gradient' => 'from-accent-500 to-accent-700', 'icon' => 'M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z|M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z'] : null,
                $company->values_text ? ['title' => __('Our Values'), 'text' => $company->values_text, 'gradient' => 'from-emerald-500 to-emerald-700', 'icon' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'] : null,
            ])->filter()->values();
        @endphp

        <div class="mt-14 grid gap-6 max-w-7xl mx-auto {{ $missionCards->count() === 1 ? '' : ($missionCards->count() === 2 ? 'sm:grid-cols-2' : 'sm:grid-cols-2 lg:grid-cols-3') }}">
            @foreach ($missionCards as $card)
            <div class="group rounded-2xl bg-white border border-slate-200 p-8 hover:shadow-xl hover:shadow-brand-100 hover:-translate-y-1 transition-all duration-300">
                <span class="grid h-14 w-14 place-items-center rounded-2xl bg-gradient-to-br {{ $card['gradient'] }} text-white shadow-lg">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                        @foreach (explode('|', $card['icon']) as $path)
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}"/>
                        @endforeach
                    </svg>
                </span>
                <h3 class="mt-5 text-lg font-bold text-brand-950">{{ $card['title'] }}</h3>
                <p class="mt-2 text-sm text-slate-600 leading-relaxed">{{ $card['text'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif
