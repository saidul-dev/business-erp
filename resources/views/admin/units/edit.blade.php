<x-app-layout>
    <x-slot name="title">{{ __('Edit Unit') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('Edit Unit') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ $unit->name }}</p>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('units.update', $unit) }}">
        @csrf
        @method('PUT')
        @include('admin.units._form')
    </form>
</x-app-layout>
