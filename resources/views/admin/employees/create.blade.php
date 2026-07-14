<x-app-layout>
    <x-slot name="title">{{ __('Add Employee') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('Add Employee') }}</h2>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('employees.store') }}" enctype="multipart/form-data">
        @csrf
        @include('admin.employees._form')
    </form>
</x-app-layout>
