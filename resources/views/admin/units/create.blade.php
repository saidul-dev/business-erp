<x-app-layout>
    <x-slot name="title">{{ __('Add Unit') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('Add Unit') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ __('Unit of measurement used by products (Pcs, Kg, Litre...)') }}</p>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('units.store') }}">
        @csrf
        @include('admin.units._form')
    </form>
</x-app-layout>
