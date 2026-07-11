@props(['status'])

@php
$styles = [
    'booked' => 'bg-slate-100 text-slate-600 ring-slate-200',
    'picked_up' => 'bg-sky-50 text-sky-600 ring-sky-200',
    'in_transit' => 'bg-amber-50 text-amber-600 ring-amber-200',
    'delivered' => 'bg-emerald-50 text-emerald-600 ring-emerald-200',
    'returned' => 'bg-rose-50 text-rose-600 ring-rose-200',
    'lost' => 'bg-rose-50 text-rose-600 ring-rose-200',
];
$labels = [
    'booked' => __('Booked'),
    'picked_up' => __('Picked Up'),
    'in_transit' => __('In Transit'),
    'delivered' => __('Delivered'),
    'returned' => __('Returned'),
    'lost' => __('Lost'),
];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 '.($styles[$status] ?? $styles['booked'])]) }}>
    {{ $labels[$status] ?? $status }}
</span>
