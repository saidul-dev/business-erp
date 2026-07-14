<x-app-layout>
    <x-slot name="title">{{ __('Add Leave Type') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('Add Leave Type') }}</h2>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('leave-types.store') }}">
        @csrf
        @include('admin.leave-types._form')
    </form>
</x-app-layout>
