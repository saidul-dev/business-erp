<x-app-layout>
    <x-slot name="title">{{ __('Add Party') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('Add Party') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ __('A customer, a supplier, or both') }}</p>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('parties.store') }}">
        @csrf
        @include('admin.parties._form')
    </form>
</x-app-layout>
