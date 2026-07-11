<x-app-layout>
    <x-slot name="title">{{ __('Website Settings') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('Website Settings') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ __('Content shown on your public company homepage') }}</p>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('settings.website.update') }}">
        @csrf
        @method('PUT')

        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 space-y-5">
            <div>
                <x-input-label for="tagline" :value="__('Homepage Tagline')" />
                <x-text-input id="tagline" name="tagline" type="text" class="mt-1 block w-full"
                              :value="old('tagline', $company->tagline)" placeholder="{{ __('e.g. Quality furniture, delivered across Dhaka') }}" />
                <p class="mt-1 text-xs text-slate-400">{{ __('One line shown under your business name on the public homepage.') }}</p>
                <x-input-error class="mt-2" :messages="$errors->get('tagline')" />
            </div>
            <div>
                <x-input-label for="about_text" :value="__('About Your Business')" />
                <textarea id="about_text" name="about_text" rows="5"
                          class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-accent-500 focus:ring-accent-500"
                          placeholder="{{ __('A short paragraph about what your business does — shown in the About section of your public homepage.') }}">{{ old('about_text', $company->about_text) }}</textarea>
                <x-input-error class="mt-2" :messages="$errors->get('about_text')" />
            </div>
        </div>

        <div class="mt-5 flex items-center gap-3">
            <x-primary-button>{{ __('Save Changes') }}</x-primary-button>
            <a href="{{ route('home') }}" target="_blank" class="text-sm font-semibold text-accent-600 hover:text-accent-800">
                {{ __('View Homepage') }} &rarr;
            </a>
        </div>
    </form>
</x-app-layout>
