<x-app-layout>
    <x-slot name="title">{{ __('Add Brand') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('Add Brand') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ __('Manufacturer or brand your products belong to') }}</p>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('brands.store') }}" enctype="multipart/form-data">
        @csrf
        @include('admin.brands._form')
    </form>
</x-app-layout>
