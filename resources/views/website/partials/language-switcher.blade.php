@php
    $availableLocales = config('app.available_locales', ['en' => 'English']);
    $currentLocale = app()->getLocale();
@endphp
<div class="relative" x-data="{ langOpen: false }" @click.outside="langOpen = false">
    <button @click="langOpen = !langOpen" type="button"
            class="flex items-center gap-1 rounded-lg px-2 py-2 text-sm font-medium text-white/80 hover:bg-white/10 hover:text-white">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Zm0 0c-2.485 0-4.5-4.03-4.5-9s2.015-9 4.5-9 4.5 4.03 4.5 9-2.015 9-4.5 9Zm-9-9h18"/></svg>
        <span>{{ $availableLocales[$currentLocale] ?? strtoupper($currentLocale) }}</span>
        <svg class="h-3 w-3" :class="langOpen && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
    </button>
    <div x-show="langOpen" x-transition style="display:none"
         class="absolute right-0 top-full z-50 mt-1 w-32 rounded-xl bg-white py-1 shadow-lg ring-1 ring-black/5">
        @foreach ($availableLocales as $code => $label)
        <form method="POST" action="{{ route('language.switch', $code) }}">
            @csrf
            <button type="submit"
                    class="block w-full px-4 py-2 text-left text-sm {{ $currentLocale === $code ? 'font-semibold text-accent-600' : 'text-slate-700 hover:bg-slate-50' }}">
                {{ $label }}
            </button>
        </form>
        @endforeach
    </div>
</div>
