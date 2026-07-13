<x-app-layout>
    <x-slot name="title">{{ __('Edit Campaign Page') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('Edit Campaign Page') }}</h2>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('campaign-pages.update', $page) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.campaign-pages._form')
    </form>
</x-app-layout>
