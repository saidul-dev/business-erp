<x-app-layout>
    <x-slot name="title">{{ __('Edit Department') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('Edit Department') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ $department->name }}</p>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('departments.update', $department) }}">
        @csrf
        @method('PUT')
        @include('admin.departments._form')
    </form>
</x-app-layout>
