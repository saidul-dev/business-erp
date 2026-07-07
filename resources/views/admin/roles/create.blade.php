<x-app-layout>
    <x-slot name="title">Add Role</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">Add Role</h2>
            <p class="text-sm text-slate-500 mt-0.5">Define a role and pick its module permissions</p>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('roles.store') }}">
        @csrf
        @include('admin.roles._form')
    </form>
</x-app-layout>
