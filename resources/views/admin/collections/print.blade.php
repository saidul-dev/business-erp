<x-app-layout>
    <x-slot name="title">{{ __('Print') }} — {{ $collection->collection_no }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('Money Receipt') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ $collection->collection_no }}</p>
        </div>
    </x-slot>

    <div class="no-print mb-4 flex justify-end gap-3">
        <a href="{{ route('collections.show', $collection) }}"
           class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">
            {{ __('Back') }}
        </a>
        <button type="button" onclick="window.print()"
                class="inline-flex items-center gap-2 rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
            {{ __('Print') }}
        </button>
    </div>

    @include('admin.vouchers._voucher', [
        'kind' => 'collection',
        'accent' => 'emerald',
        'stampLabel' => __('RECEIVED'),
        'voucherTitle' => __('Money Receipt'),
        'voucherNo' => $collection->collection_no,
        'voucherDate' => $collection->collection_date,
        'partyLabel' => __('Received From'),
        'party' => $collection->party,
        'accountLabel' => __('Received Into'),
        'account' => $collection->account,
        'amount' => $collection->amount,
        'referenceNo' => $collection->reference_no,
        'note' => $collection->note,
        'preparedByLabel' => __('Received By'),
        'preparedByName' => $collection->creator->name ?? '—',
        'counterSignLabel' => __('Customer Signature'),
        'company' => $company,
    ])

    <script>
        window.addEventListener('load', function () { window.print(); });
    </script>
</x-app-layout>
