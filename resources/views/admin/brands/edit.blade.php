<x-app-layout>
    <x-slot name="title">Edit Brand</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">Edit Brand</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ $brand->name }}</p>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('brands.update', $brand) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.brands._form')
    </form>
</x-app-layout>
