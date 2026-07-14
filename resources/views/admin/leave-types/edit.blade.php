<x-app-layout>
    <x-slot name="title">{{ __('Edit Leave Type') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('Edit Leave Type') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ $leaveType->name }}</p>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('leave-types.update', $leaveType) }}">
        @csrf
        @method('PUT')
        @include('admin.leave-types._form')
    </form>
</x-app-layout>
