<x-app-layout>
    <x-slot name="title">{{ __('Edit User') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('Edit User') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ $user->name }} — {{ $user->email }}</p>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('users.update', $user) }}">
        @csrf
        @method('PATCH')
        @include('admin.users._form')
    </form>
</x-app-layout>
