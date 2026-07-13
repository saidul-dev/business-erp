@props(['status'])

@php
$styles = [
    'pending' => 'bg-slate-100 text-slate-600 ring-slate-200',
    'approved' => 'bg-emerald-50 text-emerald-600 ring-emerald-200',
    'rejected' => 'bg-rose-50 text-rose-600 ring-rose-200',
];
$labels = [
    'pending' => __('Pending'),
    'approved' => __('Approved'),
    'rejected' => __('Rejected'),
];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 '.($styles[$status] ?? $styles['pending'])]) }}>
    {{ $labels[$status] ?? $status }}
</span>
