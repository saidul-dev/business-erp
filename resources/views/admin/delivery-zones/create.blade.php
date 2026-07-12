<x-app-layout>
    <x-slot name="title">{{ __('Add Delivery Zone') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('Add Delivery Zone') }}</h2>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('delivery-zones.store') }}">
        @csrf
        @include('admin.delivery-zones._form')
    </form>
</x-app-layout>
