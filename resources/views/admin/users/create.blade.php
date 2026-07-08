<x-app-layout>
    <x-slot name="title">{{ __('Add User') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('Add User') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ __('Create a team member account and assign roles') }}</p>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('users.store') }}">
        @csrf
        @include('admin.users._form')
    </form>
</x-app-layout>
