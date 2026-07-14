<x-app-layout>
    <x-slot name="title">{{ __('Edit Designation') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('Edit Designation') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ $designation->name }}</p>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('designations.update', $designation) }}">
        @csrf
        @method('PUT')
        @include('admin.designations._form')
    </form>
</x-app-layout>
