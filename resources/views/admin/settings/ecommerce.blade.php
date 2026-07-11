<x-app-layout>
    <x-slot name="title">{{ __('E-commerce') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('E-commerce') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ __('Super Admin only — controls whether the online storefront is published') }}</p>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('settings.ecommerce.update') }}">
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

        <div class="mt-5 flex items-center gap-3">
            <x-primary-button>{{ __('Save Changes') }}</x-primary-button>
        </div>
    </form>
</x-app-layout>
