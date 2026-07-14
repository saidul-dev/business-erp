<x-app-layout>
    <x-slot name="title">{{ __('Courier Consignments') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('Courier Consignments') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ __('Every Sale delivery booked out to a courier — track status and settle COD here') }}</p>
        </div>
    </x-slot>

    <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 mb-6">
        <form method="GET" action="{{ route('courier-consignments.index') }}" class="flex flex-nowrap items-end gap-4 overflow-x-auto">
            <div class="w-56 shrink-0">
                <x-input-label for="delivery_partner_id" :value="__('Courier')" />
                <div class="mt-1">
                    <x-searchable-select name="delivery_partner_id" :options="$partners" :selected="$partnerId"
                                          placeholder="{{ __('All couriers') }}" />
                </div>
            </div>
            <div class="w-48 shrink-0">
                <x-input-label for="status" :value="__('Status')" />
                <select name="status" class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">
                    <option value="">{{ __('All statuses') }}</option>
                    @foreach (\App\Models\CourierConsignment::STATUSES as $s)
                    <option value="{{ $s }}" {{ $status === $s ? 'selected' : '' }}>{{ __(ucwords(str_replace('_', ' ', $s))) }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="shrink-0 rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">{{ __('Filter') }}</button>
        </form>
    </div>

    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full min-w-[860px] text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <th class="px-5 py-3 font-semibold">{{ __('Sale') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Customer') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Courier') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Tracking No.') }}</th>
                    <th class="px-5 py-3 font-semibold text-right">{{ __('COD Amount') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Status') }}</th>
                    <th class="px-5 py-3 font-semibold text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($consignments as $consignment)
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-3 font-semibold text-slate-800">{{ $consignment->delivery->sale->sale_no }}</td>
                    <td class="px-5 py-3 text-slate-600">{{ $consignment->delivery->sale->party->name }}</td>
                    <td class="px-5 py-3 text-slate-600">{{ $consignment->deliveryPartner->name }}</td>
                    <td class="px-5 py-3 text-slate-600">{{ $consignment->tracking_no ?: '—' }}</td>
                    <td class="px-5 py-3 text-right text-slate-600">{{ $consignment->cod_amount > 0 ? number_format($consignment->cod_amount, 2) : '—' }}</td>
                    <td class="px-5 py-3">
                        <x-courier-consignment-status-badge :status="$consignment->status" />
                        @if ($consignment->cod_settled_at)
                        <span class="block text-xs text-emerald-600 mt-1">{{ __('COD settled') }}</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-right">
                        <a href="{{ route('courier-consignments.show', $consignment) }}"
                           class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-semibold text-slate-500 ring-1 ring-slate-200 hover:bg-brand-50 hover:text-brand-700 hover:ring-brand-200">
                            {{ __('View') }}
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-5 py-10 text-center text-slate-400">{{ __('No courier consignments yet — pick "Courier" as the fulfillment method when delivering a Sale.') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>

        @if ($consignments->hasPages())
        <div class="border-t border-slate-100 px-5 py-4">
            {{ $consignments->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
