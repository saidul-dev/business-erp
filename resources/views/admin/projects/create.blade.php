<x-app-layout>
    <x-slot name="title">{{ __('New Project') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('New Project') }}</h2>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('projects.store') }}">
        @csrf
        @include('admin.projects._form')
    </form>
</x-app-layout>
