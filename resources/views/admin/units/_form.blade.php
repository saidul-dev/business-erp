@php $editing = isset($unit); @endphp

<div class="max-w-lg rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 space-y-5">
    <div>
        <x-input-label for="name" value="Unit Name" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                      :value="old('name', $unit->name ?? '')" required autofocus placeholder="e.g. Kilogram" />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    <div>
        <x-input-label for="short_name" value="Short Name" />
        <x-text-input id="short_name" name="short_name" type="text" class="mt-1 block w-full"
                      :value="old('short_name', $unit->short_name ?? '')" required placeholder="e.g. Kg" />
        <x-input-error class="mt-2" :messages="$errors->get('short_name')" />
    </div>
</div>

<div class="mt-5 flex items-center gap-3">
    <x-primary-button>{{ $editing ? 'Update Unit' : 'Create Unit' }}</x-primary-button>
    <a href="{{ route('units.index') }}" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50">Cancel</a>
</div>
