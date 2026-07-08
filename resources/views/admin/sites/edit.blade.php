<x-app-layout>
    <x-slot name="title">Edit Site</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">Edit Site</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ $site->name }}</p>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('sites.update', $site) }}">
        @csrf
        @method('PUT')
        @include('admin.sites._form')
    </form>
</x-app-layout>
