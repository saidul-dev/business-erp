@php $editing = isset($partner); @endphp

<div class="max-w-lg rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 space-y-5"
     x-data="{ provider: '{{ old('provider', $partner->provider ?? 'manual') }}' }">
    <div>
        <x-input-label for="name" :value="__('Name')" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                      :value="old('name', $partner->name ?? '')" required autofocus placeholder="{{ __('e.g. Pathao Courier') }}" />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    <div>
        <x-input-label for="code" :value="__('Code')" />
        <x-text-input id="code" name="code" type="text" class="mt-1 block w-full"
                      :value="old('code', $partner->code ?? '')" required placeholder="{{ __('e.g. pathao') }}" />
        <p class="mt-1 text-xs text-slate-400">{{ __('A short unique slug.') }}</p>
        <x-input-error class="mt-2" :messages="$errors->get('code')" />
    </div>

    <div>
        <x-input-label for="provider" :value="__('Booking')" />
        <select id="provider" name="provider" x-model="provider"
                class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">
            <option value="manual">{{ __('Manual — book outside this system, enter tracking no. by hand') }}</option>
            <option value="steadfast">{{ __('Steadfast — book automatically via their API') }}</option>
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('provider')" />
    </div>

    <div x-show="provider !== 'manual'" x-cloak class="space-y-5 rounded-xl bg-slate-50 p-4 ring-1 ring-slate-200">
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

    <div>
        <x-input-label for="phone" :value="__('Phone (optional)')" />
        <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full"
                      :value="old('phone', $partner->phone ?? '')" />
        <x-input-error class="mt-2" :messages="$errors->get('phone')" />
    </div>

    <div>
        <x-input-label for="contact_person" :value="__('Contact Person (optional)')" />
        <x-text-input id="contact_person" name="contact_person" type="text" class="mt-1 block w-full"
                      :value="old('contact_person', $partner->contact_person ?? '')" />
        <x-input-error class="mt-2" :messages="$errors->get('contact_person')" />
    </div>

    <div>
        <x-input-label for="notes" :value="__('Notes (optional)')" />
        <textarea id="notes" name="notes" rows="2"
                  class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">{{ old('notes', $partner->notes ?? '') }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('notes')" />
    </div>
</div>

<div class="mt-5 flex items-center gap-3">
    <x-primary-button>{{ $editing ? __('Update Partner') : __('Create Partner') }}</x-primary-button>
    <a href="{{ route('delivery-partners.index') }}" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50">{{ __('Cancel') }}</a>
</div>
