@props(['disabled' => false, 'readonly' => false])

<input @disabled($disabled) @readonly($readonly) {{ $attributes->merge(['class' => 'border-slate-300 focus:border-accent-500 focus:ring-accent-500 rounded-lg shadow-sm']) }}>
