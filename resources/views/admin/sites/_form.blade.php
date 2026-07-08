@php $editing = isset($site); @endphp

<div class="max-w-2xl rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 space-y-5">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <x-input-label for="name" value="Site Name" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                          :value="old('name', $site->name ?? '')" required autofocus />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="code" value="Site Code" />
            <x-text-input id="code" name="code" type="text" class="mt-1 block w-full"
                          :value="old('code', $site->code ?? '')" required placeholder="e.g. DHK-BR" />
            <x-input-error class="mt-2" :messages="$errors->get('code')" />
        </div>
    </div>

    <div>
        <x-input-label for="type" value="Site Type" />
        <select id="type" name="type" required
                class="mt-1 block w-full rounded-lg border-slate-300 focus:border-accent-500 focus:ring-accent-500">
            <option value="">Select a type</option>
            @foreach ($types as $type)
                <option value="{{ $type }}" @selected(old('type', $site->type ?? '') === $type)>{{ $type }}</option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('type')" />
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <x-input-label for="phone" value="Phone" />
            <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full"
                          :value="old('phone', $site->phone ?? '')" />
            <x-input-error class="mt-2" :messages="$errors->get('phone')" />
        </div>

        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                          :value="old('email', $site->email ?? '')" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>
    </div>

    <div>
        <x-input-label for="address" value="Address" />
        <textarea id="address" name="address" rows="3"
                  class="mt-1 block w-full rounded-lg border-slate-300 focus:border-accent-500 focus:ring-accent-500">{{ old('address', $site->address ?? '') }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('address')" />
    </div>
</div>

<div class="mt-5 flex items-center gap-3">
    <x-primary-button>{{ $editing ? 'Update Site' : 'Create Site' }}</x-primary-button>
    <a href="{{ route('sites.index') }}" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50">Cancel</a>
</div>
