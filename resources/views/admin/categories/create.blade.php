<x-app-layout>
    <x-slot name="title">Add Category</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">Add Category</h2>
            <p class="text-sm text-slate-500 mt-0.5">Organize your products into categories, optionally nested</p>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('categories.store') }}">
        @csrf
        @include('admin.categories._form')
    </form>
</x-app-layout>
