@php $editing = isset($attribute); @endphp

<div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 space-y-5 max-w-xl"
     x-data="{ values: {{ Illuminate\Support\Js::from(old('values', $editing ? $attribute->values->pluck('value')->all() : [''])) }} }">
    <div>
        <x-input-label for="name" :value="__('Attribute Name')" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                      :value="old('name', $attribute->name ?? '')" required autofocus placeholder="{{ __('e.g. Color') }}" />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    <div>
        <x-input-label :value="__('Values')" />
        <p class="text-xs text-slate-400 mb-2">{{ __('e.g. Red, Blue, Black') }}</p>
        <template x-for="(value, i) in values" :key="i">
            <div class="mt-2 flex items-center gap-2">
                <input type="text" :name="'values[' + i + ']'" x-model="values[i]"
                       class="block w-full rounded-lg border-slate-300 focus:border-accent-500 focus:ring-accent-500" placeholder="{{ __('Value') }}">
                <button type="button" @click="values.splice(i, 1)" x-show="values.length > 1"
                        class="shrink-0 rounded-lg px-3 py-2 text-xs font-semibold text-rose-600 ring-1 ring-rose-200 hover:bg-rose-50">{{ __('Remove') }}</button>
            </div>
        </template>
        <button type="button" @click="values.push('')"
                class="mt-3 rounded-lg bg-brand-800/5 px-4 py-2 text-xs font-semibold text-brand-800 hover:bg-brand-800/10">+ {{ __('Add Value') }}</button>
        <x-input-error class="mt-2" :messages="$errors->get('values')" />
    </div>
</div>

<div class="mt-5 flex items-center gap-3">
    <x-primary-button>{{ $editing ? __('Update Attribute') : __('Create Attribute') }}</x-primary-button>
    <a href="{{ route('attributes.index') }}" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50">{{ __('Cancel') }}</a>
</div>
