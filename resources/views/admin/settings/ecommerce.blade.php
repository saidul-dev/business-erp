<x-app-layout>
    <x-slot name="title">{{ __('E-commerce') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('E-commerce') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ __('Super Admin only — controls whether the online storefront is published') }}</p>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('settings.ecommerce.update') }}" enctype="multipart/form-data"
          x-data="{ preview1: @js($company->side_banner_1_url), removeBanner1: false, preview2: @js($company->side_banner_2_url), removeBanner2: false }">
        @csrf
        @method('PUT')

        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <label class="flex items-start gap-4 rounded-xl bg-slate-50 px-5 py-4 cursor-pointer">
                <input type="checkbox" name="ecommerce_enabled" value="1" class="peer sr-only"
                       @checked(old('ecommerce_enabled', $company->ecommerce_enabled))>
                <span class="relative mt-0.5 h-6 w-11 shrink-0 rounded-full bg-slate-300 transition-colors peer-checked:bg-accent-500 after:absolute after:left-0.5 after:top-0.5 after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow after:transition-transform peer-checked:after:translate-x-5"></span>
                <span>
                    <span class="block text-sm font-semibold text-slate-800">{{ __('Enable E-commerce') }}</span>
                    <span class="block text-xs text-slate-500 mt-0.5">
                        {{ __('Turn this on to publish the online storefront on the company website — product catalog, cart, checkout and order tracking will appear automatically, and the related admin menus (Online Orders, Marketing, ...) become visible. Leave it off to keep the website as an information-only company page.') }}
                    </span>
                </span>
            </label>
            <x-input-error class="mt-2" :messages="$errors->get('ecommerce_enabled')" />
        </div>

        <div class="mt-5 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <x-input-label for="online_site_id" :value="__('Online Store Site')" />
            <select id="online_site_id" name="online_site_id"
                    class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-accent-500 focus:ring-accent-500">
                <option value="">{{ __('— Not selected —') }}</option>
                @foreach ($sites as $site)
                <option value="{{ $site->id }}" @selected((int) old('online_site_id', $company->online_site_id) === $site->id)>{{ $site->name }}</option>
                @endforeach
            </select>
            <p class="mt-2 text-xs text-slate-500">
                {{ __('The Site that online orders draw stock from. Checkout stays disabled until this is set.') }}
            </p>
            <x-input-error class="mt-2" :messages="$errors->get('online_site_id')" />
        </div>

        <div class="mt-5 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <div class="mb-4">
                <h3 class="font-bold text-brand-900">{{ __('Homepage Side Banners') }}</h3>
                <p class="text-xs text-slate-400">{{ __('Two static promo boxes shown beside the hero slider on the online homepage. Optional — leave empty to hide.') }}</p>
            </div>

            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Banner 1') }}</p>
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                        <div class="h-24 w-full sm:w-40 overflow-hidden rounded-xl bg-slate-100 ring-1 ring-slate-200 grid place-items-center shrink-0">
                            <template x-if="preview1">
                                <img :src="preview1" alt="Banner 1 preview" class="h-full w-full object-cover">
                            </template>
                            <template x-if="!preview1">
                                <span class="text-xs font-medium text-slate-400">{{ __('No image') }}</span>
                            </template>
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="cursor-pointer rounded-lg bg-brand-800/5 px-4 py-2 text-xs font-semibold text-brand-800 hover:bg-brand-800/10 text-center">
                                {{ __('Choose Image') }}
                                <input type="file" name="side_banner_1" accept="image/*" class="hidden"
                                       @change="removeBanner1 = false; const f = $event.target.files[0]; if (f) preview1 = URL.createObjectURL(f)">
                            </label>
                            @if ($company->side_banner_1_url)
                            <label class="flex items-center gap-2 text-xs text-slate-500">
                                <input type="checkbox" name="remove_side_banner_1" value="1" x-model="removeBanner1"
                                       @change="if (removeBanner1) preview1 = null; else preview1 = @js($company->side_banner_1_url)"
                                       class="rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                                {{ __('Remove') }}
                            </label>
                            @endif
                        </div>
                    </div>
                    <x-input-error class="mt-2" :messages="$errors->get('side_banner_1')" />
                </div>

                <div>
                    <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Banner 2') }}</p>
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                        <div class="h-24 w-full sm:w-40 overflow-hidden rounded-xl bg-slate-100 ring-1 ring-slate-200 grid place-items-center shrink-0">
                            <template x-if="preview2">
                                <img :src="preview2" alt="Banner 2 preview" class="h-full w-full object-cover">
                            </template>
                            <template x-if="!preview2">
                                <span class="text-xs font-medium text-slate-400">{{ __('No image') }}</span>
                            </template>
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="cursor-pointer rounded-lg bg-brand-800/5 px-4 py-2 text-xs font-semibold text-brand-800 hover:bg-brand-800/10 text-center">
                                {{ __('Choose Image') }}
                                <input type="file" name="side_banner_2" accept="image/*" class="hidden"
                                       @change="removeBanner2 = false; const f = $event.target.files[0]; if (f) preview2 = URL.createObjectURL(f)">
                            </label>
                            @if ($company->side_banner_2_url)
                            <label class="flex items-center gap-2 text-xs text-slate-500">
                                <input type="checkbox" name="remove_side_banner_2" value="1" x-model="removeBanner2"
                                       @change="if (removeBanner2) preview2 = null; else preview2 = @js($company->side_banner_2_url)"
                                       class="rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                                {{ __('Remove') }}
                            </label>
                            @endif
                        </div>
                    </div>
                    <x-input-error class="mt-2" :messages="$errors->get('side_banner_2')" />
                </div>
            </div>
        </div>

        <div class="mt-5 flex items-center gap-3">
            <x-primary-button>{{ __('Save Changes') }}</x-primary-button>
            @if ($ecommerceEnabled ?? $company->ecommerce_enabled)
            <a href="{{ route('hero-slides.index') }}" class="text-sm font-semibold text-accent-600 hover:text-accent-800">{{ __('Manage Hero Slides →') }}</a>
            @endif
        </div>
    </form>
</x-app-layout>
