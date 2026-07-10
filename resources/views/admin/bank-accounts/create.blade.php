<x-app-layout>
    <x-slot name="title">{{ __('Add Bank Account') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('Add Bank Account') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ __('Register a bank account or cash drawer for the accounting ledger') }}</p>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('bank-accounts.store') }}">
        @csrf
        @include('admin.bank-accounts._form')
    </form>
</x-app-layout>
