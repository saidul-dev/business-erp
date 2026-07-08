<x-app-layout>
    <x-slot name="title">{{ __('Add Attribute') }}</x-slot>
    <x-slot name="header"><h2 class="text-2xl font-bold text-brand-900">{{ __('Add Attribute') }}</h2></x-slot>

    <form method="POST" action="{{ route('attributes.store') }}">
        @csrf
        @include('admin.attributes._form')
    </form>
</x-app-layout>
