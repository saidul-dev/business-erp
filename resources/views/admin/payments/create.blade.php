<x-app-layout>
    <x-slot name="title">{{ __('New Payment') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('New Payment') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ __('Pay a supplier — this reduces their Accounts Payable balance and the paying account\'s balance.') }}</p>
        </div>
    </x-slot>

    <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 mb-6">
        <form method="GET" action="{{ route('payments.create') }}" class="flex flex-nowrap items-end gap-4 overflow-x-auto">
            <div class="w-64 shrink-0">
                <x-input-label for="party_id" :value="__('Supplier')" />
                <div class="mt-1">
                    <x-searchable-select name="party_id" :options="$suppliers" :selected="$party?->id"
                                          placeholder="{{ __('Select a supplier…') }}" :auto-submit="true" />
                </div>
            </div>
        </form>

        @if ($party)
        <div class="mt-4 flex items-center gap-3 rounded-lg bg-slate-50 px-4 py-3 text-sm ring-1 ring-slate-100">
            <span class="text-slate-500">{{ __('Current Payable') }}:</span>
            <span class="font-bold {{ $payable > 0 ? 'text-amber-600' : 'text-slate-800' }}">{{ number_format($payable, 2) }}</span>
            @if ($payable <= 0)
            <span class="text-xs text-slate-400">{{ __('Nothing currently due — a payment here records an advance.') }}</span>
            @endif
        </div>
        @endif
    </div>

    @if (! $party)
        <div class="rounded-2xl bg-white p-10 text-center text-slate-400 shadow-sm ring-1 ring-slate-200">
            {{ __('Pick a supplier above to record a payment.') }}
        </div>
    @else
        <form method="POST" action="{{ route('payments.store') }}">
            @csrf
            <input type="hidden" name="party_id" value="{{ $party->id }}">

            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 space-y-5 max-w-xl">
                <div>
                    <x-input-label for="ledger_account_id" :value="__('Pay From')" />
                    <div class="mt-1">
                        <x-searchable-select name="ledger_account_id" :options="$accounts" :selected="old('ledger_account_id')"
                                              placeholder="{{ __('Select a cash/bank account…') }}" />
                    </div>
                    <x-input-error class="mt-2" :messages="$errors->get('ledger_account_id')" />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="amount" :value="__('Amount')" />
                        <x-text-input id="amount" name="amount" type="number" step="0.01" min="0.01" class="mt-1 block w-full"
                                      :value="old('amount')" required />
                        <x-input-error class="mt-2" :messages="$errors->get('amount')" />
                    </div>
                    <div>
                        <x-input-label for="payment_date" :value="__('Payment Date')" />
                        <x-text-input id="payment_date" name="payment_date" type="date" class="mt-1 block w-full"
                                      :value="old('payment_date', now()->toDateString())" required />
                        <x-input-error class="mt-2" :messages="$errors->get('payment_date')" />
                    </div>
                </div>

                <div>
                    <x-input-label for="reference_no" :value="__('Reference No. (optional)')" />
                    <x-text-input id="reference_no" name="reference_no" type="text" class="mt-1 block w-full"
                                  :value="old('reference_no')" placeholder="{{ __('Cheque no. / transaction ID') }}" />
                    <x-input-error class="mt-2" :messages="$errors->get('reference_no')" />
                </div>

                <div>
                    <x-input-label for="note" :value="__('Note (optional)')" />
                    <textarea id="note" name="note" rows="2"
                              class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">{{ old('note') }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('note')" />
                </div>
            </div>

            <div class="mt-5">
                <x-primary-button>{{ __('Record Payment') }}</x-primary-button>
            </div>
        </form>
    @endif
</x-app-layout>
