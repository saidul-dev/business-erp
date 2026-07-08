<x-app-layout>
    <x-slot name="title">{{ __('Edit Product') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('Edit Product') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ $product->name }}</p>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('products.update', $product) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.products._form')
    </form>
</x-app-layout>
