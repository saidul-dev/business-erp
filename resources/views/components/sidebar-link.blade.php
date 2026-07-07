@props(['active' => false])

@php
$classes = ($active ?? false)
    ? 'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-semibold bg-white/10 text-white border-l-2 border-accent-400'
    : 'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-brand-200 hover:bg-white/5 hover:text-white transition-colors';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
