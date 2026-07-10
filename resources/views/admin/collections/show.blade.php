<x-app-layout>
    <x-slot name="title">{{ $collection->collection_no }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ $collection->collection_no }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ __('Collection detail') }}</p>
        </div>
    </x-slot>

    @include('admin.vouchers._show', [
        'accent' => 'emerald',
        'stampLabel' => __('Received'),
        'party' => $collection->party,
        'partyRoleLabel' => __('Customer'),
        'accountLabel' => __('Received Into'),
        'account' => $collection->account,
        'amount' => $collection->amount,
        'currencyLabel' => \App\Models\CompanySetting::current()->currency_label,
        'date' => $collection->collection_date,
        'referenceNo' => $collection->reference_no,
        'creatorName' => $collection->creator->name ?? '—',
        'note' => $collection->note,
        'ledgerTransaction' => $collection->ledgerTransaction,
        'balanceLabel' => __('Current Receivable'),
        'currentBalance' => $collection->party->receivableBalance(),
        'indexUrl' => route('collections.index'),
        'backLabel' => __('Back to Collections'),
        'printUrl' => route('collections.print', $collection),
    ])
</x-app-layout>
