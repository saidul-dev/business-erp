<x-app-layout>
    <x-slot name="title">{{ __('Consignment') }} — {{ $consignment->delivery->sale->sale_no }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('Consignment') }} — {{ $consignment->delivery->sale->sale_no }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ __('Booked via') }} {{ $consignment->deliveryPartner->name }}</p>
        </div>
    </x-slot>

    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 p-6 mb-4">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3 text-sm">
                <span class="font-semibold text-slate-800">{{ $consignment->delivery->sale->party->name }}</span>
                <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                <span class="font-semibold text-slate-800">{{ $consignment->delivery->sale->site->name }}</span>
            </div>
            <x-courier-consignment-status-badge :status="$consignment->status" class="!px-3 !py-1" />
        </div>

        <div class="mt-5 grid grid-cols-1 sm:grid-cols-4 gap-4 text-sm">
            <div>
                <span class="block text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ __('Courier') }}</span>
                <span class="block text-slate-700">{{ $consignment->deliveryPartner->name }}</span>
            </div>
            <div>
                <span class="block text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ __('Tracking No.') }}</span>
                <span class="block text-slate-700">{{ $consignment->tracking_no ?: '—' }}</span>
            </div>
            <div>
                <span class="block text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ __('COD Amount') }}</span>
                <span class="block font-semibold text-slate-800">{{ number_format($consignment->cod_amount, 2) }}</span>
            </div>
            <div>
                <span class="block text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ __('Booked By') }}</span>
                <span class="block text-slate-700">{{ $consignment->bookedBy->name ?? '—' }}</span>
            </div>
        </div>

        @if ($consignment->cod_settled_at)
        <div class="mt-5 rounded-xl bg-emerald-50 ring-1 ring-emerald-200 px-4 py-3 text-sm text-emerald-800">
            {{ __('COD settled on :date — Courier Fee :fee, Net Remitted :net', [
                'date' => $consignment->cod_settled_at->format('d M, Y'),
                'fee' => number_format($consignment->courier_fee, 2),
                'net' => number_format($consignment->cod_amount - $consignment->courier_fee, 2),
            ]) }}
        </div>
        @endif

        <div class="mt-2 text-right">
            <a href="{{ route('sales.show', $consignment->delivery->sale) }}" class="text-xs font-semibold text-accent-600 hover:text-accent-800">{{ __('View Sale') }} →</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        @can('delivery.edit')
        @if (! $consignment->cod_settled_at)
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <h3 class="font-bold text-brand-900 mb-4">{{ __('Update Status') }}</h3>
            <form method="POST" action="{{ route('courier-consignments.status', $consignment) }}" class="space-y-4">
                @csrf
                @method('PATCH')
                <div>
                    <x-input-label for="status" :value="__('Status')" />
                    <select id="status" name="status" class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">
                        @foreach (\App\Models\CourierConsignment::STATUSES as $s)
                        <option value="{{ $s }}" {{ $consignment->status === $s ? 'selected' : '' }}>{{ __(ucwords(str_replace('_', ' ', $s))) }}</option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('status')" />
                </div>
                <div>
                    <x-input-label for="tracking_no" :value="__('Tracking No.')" />
                    <x-text-input id="tracking_no" name="tracking_no" type="text" class="mt-1 block w-full" :value="old('tracking_no', $consignment->tracking_no)" />
                    <x-input-error class="mt-2" :messages="$errors->get('tracking_no')" />
                </div>
                <div>
                    <x-input-label for="note" :value="__('Note (optional)')" />
                    <textarea id="note" name="note" rows="2" class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">{{ old('note', $consignment->note) }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('note')" />
                </div>
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                    {{ __('Save Status') }}
                </button>
            </form>
        </div>
        @endif

        @if ($consignment->canSettleCod())
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <h3 class="font-bold text-brand-900 mb-1">{{ __('Settle COD') }}</h3>
            <p class="text-xs text-slate-400 mb-4">{{ __('Net remittance = COD amount minus the courier\'s fee — posted as one entry against the customer\'s receivable.') }}</p>
            <form method="POST" action="{{ route('courier-consignments.settle-cod', $consignment) }}" class="space-y-4"
                  x-data="{ fee: {{ (float) old('courier_fee', 0) }}, codAmount: {{ (float) $consignment->cod_amount }} }">
                @csrf
                <div>
                    <x-input-label for="ledger_account_id" :value="__('Received Into')" />
                    <p class="mt-1 text-sm font-semibold text-emerald-600">
                        {{ __('Amount you will receive from the delivery man') }}:
                        <span x-text="(codAmount - fee).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })"></span>
                    </p>
                    <div class="mt-1">
                        <x-searchable-select name="ledger_account_id" :options="$accounts" :selected="old('ledger_account_id')"
                                              placeholder="{{ __('Select Cash/Bank account…') }}" />
                    </div>
                    <x-input-error class="mt-2" :messages="$errors->get('ledger_account_id')" />
                </div>
                <div>
                    <x-input-label for="courier_fee" :value="__('Courier Fee')" />
                    <x-text-input id="courier_fee" name="courier_fee" type="number" step="0.01" min="0" max="{{ $consignment->cod_amount }}"
                                  class="mt-1 block w-full" :value="old('courier_fee', 0)" x-model.number="fee" />
                    <x-input-error class="mt-2" :messages="$errors->get('courier_fee')" />
                </div>
                <div>
                    <x-input-label for="settled_date" :value="__('Settlement Date')" />
                    <x-text-input id="settled_date" name="settled_date" type="date" class="mt-1 block w-full" :value="old('settled_date', now()->toDateString())" required />
                    <x-input-error class="mt-2" :messages="$errors->get('settled_date')" />
                </div>
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                    {{ __('Settle COD') }}
                </button>
            </form>
        </div>
        @endif
        @endcan
    </div>
</x-app-layout>
