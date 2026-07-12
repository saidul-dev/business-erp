<x-app-layout>
    <x-slot name="title">{{ __('Edit Delivery Zone') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('Edit Delivery Zone') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ $zone->name }}</p>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('delivery-zones.update', $zone) }}">
        @csrf
        @method('PUT')
        @include('admin.delivery-zones._form')
    </form>
</x-app-layout>
