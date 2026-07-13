@php $editing = isset($partner); @endphp

<div x-data="{ provider: '{{ old('provider', $partner->provider ?? 'manual') }}' }" class="grid gap-6 lg:grid-cols-2">
    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 space-y-5">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Basic Info') }}</p>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <x-input-label for="name" :value="__('Name')" />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                              :value="old('name', $partner->name ?? '')" required autofocus placeholder="{{ __('e.g. Steadfast, RedX') }}" />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            <div>
                <x-input-label for="code" :value="__('Code')" />
                <x-text-input id="code" name="code" type="text" class="mt-1 block w-full"
                              :value="old('code', $partner->code ?? '')" required placeholder="{{ __('e.g. steadfast') }}" />
                <p class="mt-1 text-[11px] text-slate-400">{{ __('A short unique slug.') }}</p>
                <x-input-error class="mt-2" :messages="$errors->get('code')" />
            </div>
        </div>
    </div>

    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 space-y-5">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Contact (optional)') }}</p>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <x-input-label for="phone" :value="__('Phone')" />
                <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full"
                              :value="old('phone', $partner->phone ?? '')" />
                <x-input-error class="mt-2" :messages="$errors->get('phone')" />
            </div>

            <div>
                <x-input-label for="contact_person" :value="__('Contact Person')" />
                <x-text-input id="contact_person" name="contact_person" type="text" class="mt-1 block w-full"
                              :value="old('contact_person', $partner->contact_person ?? '')" />
                <x-input-error class="mt-2" :messages="$errors->get('contact_person')" />
            </div>
        </div>

        <div>
            <x-input-label for="notes" :value="__('Notes')" />
            <textarea id="notes" name="notes" rows="2"
                      class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">{{ old('notes', $partner->notes ?? '') }}</textarea>
            <x-input-error class="mt-2" :messages="$errors->get('notes')" />
        </div>
    </div>

    <div class="lg:col-span-2 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 space-y-5">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Booking Method') }}</p>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <label class="flex cursor-pointer items-start gap-3 rounded-xl p-4 ring-1 transition-colors"
                   :class="provider === 'manual' ? 'bg-brand-50/70 ring-brand-300' : 'bg-white ring-slate-200 hover:bg-slate-50'">
                <input type="radio" name="provider" value="manual" x-model="provider" class="mt-0.5 text-brand-800 focus:ring-accent-500">
                <span>
                    <span class="block text-sm font-semibold text-slate-800">{{ __('Manual') }}</span>
                    <span class="block text-xs text-slate-500 mt-0.5">{{ __('Book with the courier outside this system — enter the tracking no. by hand when you deliver.') }}</span>
                </span>
            </label>

            <label class="flex cursor-pointer items-start gap-3 rounded-xl p-4 ring-1 transition-colors"
                   :class="provider === 'steadfast' ? 'bg-brand-50/70 ring-brand-300' : 'bg-white ring-slate-200 hover:bg-slate-50'">
                <input type="radio" name="provider" value="steadfast" x-model="provider" class="mt-0.5 text-brand-800 focus:ring-accent-500">
                <span>
                    <span class="block text-sm font-semibold text-slate-800">{{ __('Steadfast') }}</span>
                    <span class="block text-xs text-slate-500 mt-0.5">{{ __('Books automatically via their API and syncs delivery status back — no typing tracking numbers.') }}</span>
                </span>
            </label>
        </div>
        <x-input-error class="mt-2" :messages="$errors->get('provider')" />

        <div x-show="provider !== 'manual'" x-cloak class="space-y-4 rounded-xl bg-slate-50 p-4 ring-1 ring-slate-200">
            <div class="flex items-center gap-2 text-xs font-medium text-slate-500">
                <svg class="h-4 w-4 shrink-0 text-accent-600" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z"/></svg>
                <span>{{ __('Get these from your Steadfast merchant panel.') }}</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="api_key" :value="__('Api Key')" />
                    <x-text-input id="api_key" name="api_key" type="text" class="mt-1 block w-full"
                                  :value="old('api_key', $partner->api_key ?? '')" />
                    <x-input-error class="mt-2" :messages="$errors->get('api_key')" />
                </div>
                <div>
                    <x-input-label for="secret_key" :value="__('Secret Key')" />
                    <x-text-input id="secret_key" name="secret_key" type="text" class="mt-1 block w-full"
                                  :value="old('secret_key', $partner->secret_key ?? '')" />
                    <x-input-error class="mt-2" :messages="$errors->get('secret_key')" />
                </div>
            </div>
            <p class="text-[11px] text-slate-400">
                {{ __('Also add') }} <code class="rounded bg-white px-1 py-0.5 ring-1 ring-slate-200">{{ url('/webhooks/steadfast/status') }}</code>
                {{ __('as the webhook URL in their panel so delivery status updates automatically.') }}
            </p>
        </div>
    </div>

    <div class="lg:col-span-2 flex items-center gap-3">
        <x-primary-button>{{ $editing ? __('Update Partner') : __('Create Partner') }}</x-primary-button>
        <a href="{{ route('delivery-partners.index') }}" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50">{{ __('Cancel') }}</a>
    </div>
</div>
