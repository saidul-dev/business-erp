<x-app-layout>
    <x-slot name="title">{{ __('Add Department') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('Add Department') }}</h2>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('departments.store') }}">
        @csrf
        @include('admin.departments._form')
    </form>
</x-app-layout>
