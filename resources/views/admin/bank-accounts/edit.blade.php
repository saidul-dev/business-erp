<x-app-layout>
    <x-slot name="title">{{ __('Edit Bank Account') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('Edit Bank Account') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ $bankAccount->name }}</p>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('bank-accounts.update', $bankAccount) }}">
        @csrf
        @method('PUT')
        @include('admin.bank-accounts._form')
    </form>
</x-app-layout>
