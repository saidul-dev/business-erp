@php $editing = isset($leaveType); @endphp

<div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 space-y-5 max-w-xl">
    <div>
        <x-input-label for="name" :value="__('Leave Type Name')" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                      :value="old('name', $leaveType->name ?? '')" required autofocus placeholder="{{ __('e.g. Casual Leave') }}" />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    <div>
        <x-input-label for="default_days_per_year" :value="__('Default Days / Year')" />
        <x-text-input id="default_days_per_year" name="default_days_per_year" type="number" min="0" class="mt-1 block w-full"
                      :value="old('default_days_per_year', $leaveType->default_days_per_year ?? '0')" required />
        <x-input-error class="mt-2" :messages="$errors->get('default_days_per_year')" />
    </div>

    <div class="flex items-center gap-3">
        <x-primary-button>{{ $editing ? __('Update Leave Type') : __('Create Leave Type') }}</x-primary-button>
        <a href="{{ route('leave-types.index') }}" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50">{{ __('Cancel') }}</a>
    </div>
</div>
