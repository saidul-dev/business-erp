<x-app-layout>
    <x-slot name="title">{{ __('Edit Branch') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('Edit Branch') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ $branch->name }}</p>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('branches.update', $branch) }}">
        @csrf
        @method('PUT')
        @include('admin.branches._form')
    </form>
</x-app-layout>
