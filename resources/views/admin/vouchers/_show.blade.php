@php
    $palette = match ($accent) {
        'rose' => ['solid' => '#e11d48', 'soft' => '#fff1f2', 'border' => '#fecdd3', 'ink' => '#9f1239', 'ring' => 'ring-rose-100', 'chip' => 'bg-rose-50 text-rose-700 ring-rose-200'],
        'emerald' => ['solid' => '#059669', 'soft' => '#ecfdf5', 'border' => '#a7f3d0', 'ink' => '#065f46', 'ring' => 'ring-emerald-100', 'chip' => 'bg-emerald-50 text-emerald-700 ring-emerald-200'],
        default => ['solid' => '#0b2a6b', 'soft' => '#eef4fd', 'border' => '#b3ccf4', 'ink' => '#081c4e', 'ring' => 'ring-brand-100', 'chip' => 'bg-brand-50 text-brand-700 ring-brand-200'],
    };
    $initials = collect(explode(' ', trim($party->name)))
        ->filter()
        ->take(2)
        ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))
        ->implode('');
@endphp

<div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 max-w-2xl">
    <div class="h-1.5" style="background:{{ $palette['solid'] }}"></div>

    <div class="p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full text-sm font-bold text-white ring-4 {{ $palette['ring'] }}"
                      style="background:{{ $palette['solid'] }}">
                    {{ $initials ?: '?' }}
                </span>
                <div>
                    <a href="{{ route('parties.ledger', $party) }}" class="block font-semibold text-slate-800 hover:text-accent-600">{{ $party->name }}</a>
                    <span class="text-xs text-slate-400">{{ $partyRoleLabel }}</span>
                </div>
            </div>
            <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold ring-1 {{ $palette['chip'] }}">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                {{ $stampLabel }}
            </span>
        </div>

        <div class="mt-5 rounded-xl border-2 px-5 py-4" style="background:{{ $palette['soft'] }}; border-color:{{ $palette['border'] }}">
            <span class="text-[11px] font-semibold uppercase tracking-wide" style="color:{{ $palette['ink'] }}">{{ __('Amount') }}</span>
            <div class="mt-1 font-mono text-3xl font-black tabular-nums" style="color:{{ $palette['ink'] }}">{{ number_format($amount, 2) }}</div>
        </div>

        <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-5 text-sm">
            <div>
                <span class="block text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ $accountLabel }}</span>
                <span class="block font-semibold text-slate-700">{{ $account->name }}</span>
            </div>
            <div>
                <span class="block text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ __('Date') }}</span>
                <span class="block text-slate-700">{{ $date->format('d M, Y') }}</span>
            </div>
            @if ($referenceNo)
            <div>
                <span class="block text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ __('Reference No.') }}</span>
                <span class="block text-slate-700">{{ $referenceNo }}</span>
            </div>
            @endif
            <div>
                <span class="block text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ __('Recorded By') }}</span>
                <span class="block text-slate-700">{{ $creatorName }}</span>
            </div>
        </div>

        @if ($note)
        <div class="mt-5 rounded-lg bg-slate-50 px-4 py-3 text-sm text-slate-600 ring-1 ring-slate-100">
            <span class="block text-[11px] font-semibold uppercase tracking-wide text-slate-400 mb-1">{{ __('Note') }}</span>
            {{ $note }}
        </div>
        @endif
    </div>
</div>

<div class="mt-5 flex items-center gap-3">
    <a href="{{ $indexUrl }}" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50">{{ $backLabel }}</a>
    <a href="{{ $printUrl }}" target="_blank"
       class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold text-white hover:opacity-90"
       style="background:{{ $palette['solid'] }}">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z"/></svg>
        {{ __('Print') }}
    </a>
</div>
