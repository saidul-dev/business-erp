@props(['status'])

@php
$styles = [
    'pending' => 'bg-amber-50 text-amber-600 ring-amber-200',
    'paid' => 'bg-emerald-50 text-emerald-600 ring-emerald-200',
    'failed' => 'bg-rose-50 text-rose-600 ring-rose-200',
];
$labels = [
    'pending' => __('Payment Pending'),
    'paid' => __('Paid'),
    'failed' => __('Payment Failed'),
];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 '.($styles[$status] ?? $styles['pending'])]) }}>
    {{ $labels[$status] ?? $status }}
</span>
