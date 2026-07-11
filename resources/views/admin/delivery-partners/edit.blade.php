<x-app-layout>
    <x-slot name="title">{{ __('Edit Delivery Partner') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('Edit Delivery Partner') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ $partner->name }}</p>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('delivery-partners.update', $partner) }}">
        @csrf
        @method('PUT')
        @include('admin.delivery-partners._form')
    </form>
</x-app-layout>
