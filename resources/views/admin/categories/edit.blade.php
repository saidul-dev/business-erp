<x-app-layout>
    <x-slot name="title">{{ __('Edit Category') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('Edit Category') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ $category->name }}</p>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('categories.update', $category) }}">
        @csrf
        @method('PUT')
        @include('admin.categories._form')
    </form>
</x-app-layout>
