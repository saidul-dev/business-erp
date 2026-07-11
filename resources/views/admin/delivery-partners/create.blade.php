<x-app-layout>
    <x-slot name="title">{{ __('Add Delivery Partner') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('Add Delivery Partner') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ __('A courier used to fulfil Sale deliveries (Pathao, RedX, Steadfast...)') }}</p>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('delivery-partners.store') }}">
        @csrf
        @include('admin.delivery-partners._form')
    </form>
</x-app-layout>
