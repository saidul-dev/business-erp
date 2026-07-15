<x-app-layout>
    <x-slot name="title">{{ __('Edit Project') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('Edit Project') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ $project->name }}</p>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('projects.update', $project) }}">
        @csrf
        @method('PUT')
        @include('admin.projects._form')
    </form>
</x-app-layout>
