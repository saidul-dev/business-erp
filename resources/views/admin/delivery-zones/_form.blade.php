@php $editing = isset($zone); @endphp

<div class="max-w-lg rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 space-y-5">
    <div>
        <x-input-label for="name" :value="__('Zone Name')" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                      :value="old('name', $zone->name ?? '')" required autofocus placeholder="{{ __('e.g. Inside Dhaka') }}" />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    <div>
        <x-input-label for="charge" :value="__('Delivery Charge')" />
        <x-text-input id="charge" name="charge" type="number" step="0.01" min="0" class="mt-1 block w-full"
                      :value="old('charge', $zone->charge ?? '0')" required />
        <p class="mt-1 text-[11px] text-slate-400">{{ __('Shown to the customer at checkout — never added to the order total; the courier collects this separately') }}</p>
        <x-input-error class="mt-2" :messages="$errors->get('charge')" />
    </div>

    <div>
        <x-input-label for="sort_order" :value="__('Sort Order')" />
        <x-text-input id="sort_order" name="sort_order" type="number" min="0" class="mt-1 block w-full"
                      :value="old('sort_order', $zone->sort_order ?? 0)" required />
        <p class="mt-1 text-[11px] text-slate-400">{{ __('Lower numbers show first in the checkout dropdown') }}</p>
        <x-input-error class="mt-2" :messages="$errors->get('sort_order')" />
    </div>

    <label class="flex items-center gap-2 text-sm text-slate-700">
        <input type="checkbox" name="status" value="1" class="rounded border-slate-300 text-accent-600 focus:ring-accent-500"
               @checked(old('status', $zone->status ?? true))>
        {{ __('Active') }}
    </label>
</div>

<div class="mt-5 flex items-center gap-3">
    <x-primary-button>{{ $editing ? __('Update Zone') : __('Add Zone') }}</x-primary-button>
    <a href="{{ route('delivery-zones.index') }}" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50">{{ __('Cancel') }}</a>
</div>
