<x-app-layout>
    <x-slot name="title">{{ __('Add Branch') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('Add Branch') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ __('Register a branch, warehouse, outlet or other business location') }}</p>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('branches.store') }}">
        @csrf
        @include('admin.branches._form')
    </form>
</x-app-layout>
