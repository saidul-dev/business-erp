@php $editing = isset($site); @endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    <!-- Basic Information -->
    <div class="lg:col-span-2 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 space-y-5">
        <div class="flex items-center gap-3">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-700 ring-1 ring-brand-100">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" /></svg>
            </span>
            <div>
                <h3 class="font-bold text-brand-900">{{ __('Basic Information') }}</h3>
                <p class="text-xs text-slate-400">{{ __('Identity and contact details for this location') }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="name" :value="__('Site Name')" />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                              :value="old('name', $site->name ?? '')" required autofocus placeholder="{{ __('e.g. Dhaka Central Warehouse') }}" />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            <div>
                <x-input-label for="code" :value="__('Site Code')" />
                <x-text-input id="code" name="code" type="text" class="mt-1 block w-full uppercase"
                              :value="old('code', $site->code ?? '')" required placeholder="{{ __('e.g. DHK-BR') }}" />
                <x-input-error class="mt-2" :messages="$errors->get('code')" />
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="phone" :value="__('Phone (optional)')" />
                <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full"
                              :value="old('phone', $site->phone ?? '')" placeholder="{{ __('e.g. 01700-000000') }}" />
                <x-input-error class="mt-2" :messages="$errors->get('phone')" />
            </div>

            <div>
                <x-input-label for="email" :value="__('Email (optional)')" />
                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                              :value="old('email', $site->email ?? '')" placeholder="{{ __('site@company.com') }}" />
                <x-input-error class="mt-2" :messages="$errors->get('email')" />
            </div>
        </div>
    </div>

    <!-- Site Type -->
    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 h-fit">
        <div class="mb-4 flex items-center gap-3">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-700 ring-1 ring-brand-100">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" /></svg>
            </span>
            <div>
                <h3 class="font-bold text-brand-900">{{ __('Site Type') }}</h3>
                <p class="text-xs text-slate-400">{{ __('What kind of location is this?') }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 gap-2.5">
            @foreach ($types as $type)
            <label class="cursor-pointer">
                <input type="radio" name="type" value="{{ $type }}" class="peer sr-only"
                       @checked(old('type', $site->type ?? '') === $type) required>
                <div class="flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50 peer-checked:border-accent-500 peer-checked:bg-accent-50 peer-checked:text-accent-700 peer-focus-visible:ring-2 peer-focus-visible:ring-accent-500 peer-focus-visible:ring-offset-1">
                    <svg class="h-4 w-4 shrink-0 opacity-60 peer-checked:opacity-100" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" /></svg>
                    <span class="truncate">{{ $type }}</span>
                </div>
            </label>
            @endforeach
        </div>
        <x-input-error class="mt-3" :messages="$errors->get('type')" />
    </div>

    <!-- Address -->
    <div class="lg:col-span-3 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <div class="mb-4 flex items-center gap-3">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-700 ring-1 ring-brand-100">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>
            </span>
            <div>
                <h3 class="font-bold text-brand-900">{{ __('Address') }}</h3>
                <p class="text-xs text-slate-400">{{ __('Physical location, used on documents and delivery slips') }}</p>
            </div>
        </div>

        <textarea id="address" name="address" rows="3"
                  class="block w-full rounded-lg border-slate-300 focus:border-accent-500 focus:ring-accent-500"
                  placeholder="{{ __('Street, area, city, postal code') }}">{{ old('address', $site->address ?? '') }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('address')" />
    </div>
</div>

<div class="mt-5 flex items-center gap-3">
    <x-primary-button>{{ $editing ? __('Update Site') : __('Create Site') }}</x-primary-button>
    <a href="{{ route('sites.index') }}" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50">{{ __('Cancel') }}</a>
</div>
