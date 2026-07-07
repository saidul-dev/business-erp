<x-app-layout>
    <x-slot name="title">Edit Role</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">Edit Role</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ $role->name }} — {{ $role->users()->count() }} user(s) assigned</p>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('roles.update', $role) }}">
        @csrf
        @method('PATCH')
        @include('admin.roles._form')
    </form>
</x-app-layout>
