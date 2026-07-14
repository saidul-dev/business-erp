@php $editing = isset($department); @endphp

<div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 space-y-5 max-w-xl">
    <div>
        <x-input-label for="name" :value="__('Department Name')" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                      :value="old('name', $department->name ?? '')" required autofocus placeholder="{{ __('e.g. Production') }}" />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    <div class="flex items-center gap-3">
        <x-primary-button>{{ $editing ? __('Update Department') : __('Create Department') }}</x-primary-button>
        <a href="{{ route('departments.index') }}" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50">{{ __('Cancel') }}</a>
    </div>
</div>
