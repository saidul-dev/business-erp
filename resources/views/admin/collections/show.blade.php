<x-app-layout>
    <x-slot name="title">{{ $collection->collection_no }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ $collection->collection_no }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ __('Collection detail') }}</p>
        </div>
    </x-slot>

    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 p-6 max-w-2xl">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 text-sm">
            <div>
                <span class="block text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ __('Customer') }}</span>
                <a href="{{ route('parties.ledger', $collection->party) }}" class="block font-semibold text-accent-600 hover:text-accent-800">{{ $collection->party->name }}</a>
            </div>
            <div>
                <span class="block text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ __('Received Into') }}</span>
                <span class="block text-slate-700">{{ $collection->account->name }}</span>
            </div>
            <div>
                <span class="block text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ __('Amount') }}</span>
                <span class="block text-xl font-bold text-brand-900">{{ number_format($collection->amount, 2) }}</span>
            </div>
            <div>
                <span class="block text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ __('Date') }}</span>
                <span class="block text-slate-700">{{ $collection->collection_date->format('d M, Y') }}</span>
            </div>
            @if ($collection->reference_no)
            <div>
                <span class="block text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ __('Reference No.') }}</span>
                <span class="block text-slate-700">{{ $collection->reference_no }}</span>
            </div>
            @endif
            <div>
                <span class="block text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ __('Recorded By') }}</span>
                <span class="block text-slate-700">{{ $collection->creator->name ?? '—' }}</span>
            </div>
            @if ($collection->note)
            <div class="sm:col-span-2">
                <span class="block text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ __('Note') }}</span>
                <span class="block text-slate-700">{{ $collection->note }}</span>
            </div>
            @endif
        </div>
    </div>

    <div class="mt-5">
        <a href="{{ route('collections.index') }}" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50">{{ __('Back to Collections') }}</a>
    </div>
</x-app-layout>
