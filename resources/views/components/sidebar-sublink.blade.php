@props(['active' => false])

@php
$classes = ($active ?? false)
    ? 'flex items-center gap-2 rounded-md px-3 py-1.5 text-sm font-semibold text-white bg-white/10'
    : 'flex items-center gap-2 rounded-md px-3 py-1.5 text-sm font-medium text-brand-300 hover:text-white hover:bg-white/5 transition-colors';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    <span class="h-1.5 w-1.5 rounded-full {{ ($active ?? false) ? 'bg-accent-400' : 'bg-brand-400/50' }}"></span>
    {{ $slot }}
</a>
