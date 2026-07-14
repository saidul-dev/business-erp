<x-app-layout>
    <x-slot name="title">{{ __('Add Designation') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('Add Designation') }}</h2>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('designations.store') }}">
        @csrf
        @include('admin.designations._form')
    </form>
</x-app-layout>
