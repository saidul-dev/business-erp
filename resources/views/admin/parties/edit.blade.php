<x-app-layout>
    <x-slot name="title">{{ __('Edit Party') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('Edit Party') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ $party->name }}</p>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('parties.update', $party) }}">
        @csrf
        @method('PUT')
        @include('admin.parties._form')
    </form>
</x-app-layout>
