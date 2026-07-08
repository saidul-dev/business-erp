<x-app-layout>
    <x-slot name="title">Add Product</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">Add Product</h2>
            <p class="text-sm text-slate-500 mt-0.5">Product master data — stock levels are tracked per site separately</p>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data">
        @csrf
        @include('admin.products._form')
    </form>
</x-app-layout>
