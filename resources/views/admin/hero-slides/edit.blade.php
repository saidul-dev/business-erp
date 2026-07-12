<x-app-layout>
    <x-slot name="title">{{ __('Edit Hero Slide') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('Edit Hero Slide') }}</h2>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('hero-slides.update', $slide) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.hero-slides._form')
    </form>
</x-app-layout>
