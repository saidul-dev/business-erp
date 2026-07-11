<x-app-layout>
    <x-slot name="title">{{ __('Website Settings') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('Website Settings') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ __('Content shown on your public company homepage') }}</p>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('settings.website.update') }}" enctype="multipart/form-data"
          x-data="{ preview: @js($company->hero_image_url), removeHero: false }">
        @csrf
        @method('PUT')

        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 mb-4">
            <div class="mb-4">
                <h3 class="font-bold text-brand-900">{{ __('Hero Background Photo') }}</h3>
                <p class="text-xs text-slate-400">{{ __('Shown behind your homepage banner. JPG or PNG, up to 4MB. Leave empty to use the default gradient background.') }}</p>
            </div>

            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                <div class="h-28 w-full sm:w-48 overflow-hidden rounded-xl bg-gradient-to-br from-brand-800 to-brand-950 ring-1 ring-slate-200 grid place-items-center shrink-0">
                    <template x-if="preview">
                        <img :src="preview" alt="Hero preview" class="h-full w-full object-cover">
                    </template>
                    <template x-if="!preview">
                        <span class="text-xs font-medium text-brand-200">{{ __('No photo set') }}</span>
                    </template>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="cursor-pointer rounded-lg bg-brand-800/5 px-4 py-2 text-xs font-semibold text-brand-800 hover:bg-brand-800/10 text-center">
                        {{ __('Choose Image') }}
                        <input type="file" name="hero_image" accept="image/*" class="hidden"
                               @change="removeHero = false; const f = $event.target.files[0]; if (f) preview = URL.createObjectURL(f)">
                    </label>
                    @if ($company->hero_image_url)
                    <label class="flex items-center gap-2 text-xs text-slate-500">
                        <input type="checkbox" name="remove_hero_image" value="1" x-model="removeHero"
                               @change="if (removeHero) preview = null; else preview = @js($company->hero_image_url)"
                               class="rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                        {{ __('Remove current photo') }}
                    </label>
                    @endif
                </div>
            </div>
            <x-input-error class="mt-3" :messages="$errors->get('hero_image')" />
        </div>

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
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="mission_text" :value="__('Mission')" />
                    <textarea id="mission_text" name="mission_text" rows="4"
                              class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-accent-500 focus:ring-accent-500"
                              placeholder="{{ __('What your business does, day to day, for its customers.') }}">{{ old('mission_text', $company->mission_text) }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('mission_text')" />
                </div>
                <div>
                    <x-input-label for="vision_text" :value="__('Vision')" />
                    <textarea id="vision_text" name="vision_text" rows="4"
                              class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-accent-500 focus:ring-accent-500"
                              placeholder="{{ __('Where you want the business to be in the future.') }}">{{ old('vision_text', $company->vision_text) }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('vision_text')" />
                </div>
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
