<x-app-layout>
    <x-slot name="title">{{ __('Add Campaign Page') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('Add Campaign Page') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ __('New single-product landing page for ad traffic') }}</p>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('campaign-pages.store') }}" enctype="multipart/form-data">
        @csrf
        @include('admin.campaign-pages._form')
    </form>
</x-app-layout>
