<x-app-layout>
    <x-slot name="title">{{ __('Add Role') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('Add Role') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ __('Define a role and pick its module permissions') }}</p>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('roles.store') }}">
        @csrf
        @include('admin.roles._form')
    </form>
</x-app-layout>
