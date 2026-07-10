@props(['status'])

@php
$styles = [
    'pending' => 'bg-slate-100 text-slate-600 ring-slate-200',
    'partial' => 'bg-amber-50 text-amber-600 ring-amber-200',
    'received' => 'bg-emerald-50 text-emerald-600 ring-emerald-200',
    'cancelled' => 'bg-rose-50 text-rose-600 ring-rose-200',
];
$labels = [
    'pending' => __('Pending'),
    'partial' => __('Partially Received'),
    'received' => __('Received'),
    'cancelled' => __('Cancelled'),
];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 '.($styles[$status] ?? $styles['pending'])]) }}>
    {{ $labels[$status] ?? $status }}
</span>
