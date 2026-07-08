<x-app-layout>
    <x-slot name="title">{{ __('Edit Attribute') }}</x-slot>
    <x-slot name="header"><h2 class="text-2xl font-bold text-brand-900">{{ __('Edit Attribute') }}</h2></x-slot>

    <form method="POST" action="{{ route('attributes.update', $attribute) }}">
        @csrf
        @method('PUT')
        @include('admin.attributes._form')
    </form>
</x-app-layout>
