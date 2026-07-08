<x-app-layout>
    <x-slot name="title">Add Site</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">Add Site</h2>
            <p class="text-sm text-slate-500 mt-0.5">Register a branch, warehouse, outlet or other business location</p>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('sites.store') }}">
        @csrf
        @include('admin.sites._form')
    </form>
</x-app-layout>
