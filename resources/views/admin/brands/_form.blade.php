@php $editing = isset($brand); @endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4" x-data="{ preview: @js($brand->logo_url ?? null), removeLogo: false }">
    <!-- Logo -->
    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <div class="mb-4">
            <h3 class="font-bold text-brand-900">{{ __('Brand Logo') }}</h3>
            <p class="text-xs text-slate-400">{{ __('PNG or JPG, up to 2MB') }}</p>
        </div>

        <div class="flex flex-col items-center gap-4">
            <div class="grid h-28 w-28 place-items-center overflow-hidden rounded-2xl bg-slate-100 ring-1 ring-slate-200">
                <template x-if="preview">
                    <img :src="preview" alt="Logo preview" class="h-full w-full object-cover">
                </template>
                <template x-if="!preview">
                    <svg class="h-10 w-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3 3h18M3 3l1.5 18h15L21 3H3Z"/></svg>
                </template>
            </div>

            <label class="cursor-pointer rounded-lg bg-brand-800/5 px-4 py-2 text-xs font-semibold text-brand-800 hover:bg-brand-800/10">
                {{ __('Choose Image') }}
                <input type="file" name="logo" accept="image/*" class="hidden"
                       @change="removeLogo = false; const f = $event.target.files[0]; if (f) preview = URL.createObjectURL(f)">
            </label>

            @if ($editing && $brand->logo_url)
            <label class="flex items-center gap-2 text-xs text-slate-500">
                <input type="checkbox" name="remove_logo" value="1" x-model="removeLogo"
                       @change="if (removeLogo) preview = null; else preview = @js($brand->logo_url)"
                       class="rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                {{ __('Remove current logo') }}
            </label>
            @endif
        </div>
        <x-input-error class="mt-3" :messages="$errors->get('logo')" />
    </div>

    <!-- Details -->
    <div class="lg:col-span-2 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 space-y-5">
        <div>
            <h3 class="font-bold text-brand-900">{{ __('Brand Details') }}</h3>
            <p class="text-xs text-slate-400">{{ __('Shown on products and reports') }}</p>
        </div>

        <div>
            <x-input-label for="name" :value="__('Brand Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                          :value="old('name', $brand->name ?? '')" required autofocus />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>
    </div>
</div>

<div class="mt-5 flex items-center gap-3">
    <x-primary-button>{{ $editing ? __('Update Brand') : __('Create Brand') }}</x-primary-button>
    <a href="{{ route('brands.index') }}" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50">{{ __('Cancel') }}</a>
</div>
