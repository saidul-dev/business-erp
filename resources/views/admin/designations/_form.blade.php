@php $editing = isset($designation); @endphp

<div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 space-y-5 max-w-xl">
    <div>
        <x-input-label for="name" :value="__('Designation Name')" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                      :value="old('name', $designation->name ?? '')" required autofocus placeholder="{{ __('e.g. Production Manager') }}" />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    <div class="flex items-center gap-3">
        <x-primary-button>{{ $editing ? __('Update Designation') : __('Create Designation') }}</x-primary-button>
        <a href="{{ route('designations.index') }}" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50">{{ __('Cancel') }}</a>
    </div>
</div>
