@php $editing = isset($slide); @endphp

<div class="max-w-lg rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 space-y-5"
     x-data="{ preview: @js($slide->image_url ?? null) }">
    <div>
        <x-input-label :value="__('Slide Image')" />
        <p class="text-xs text-slate-400 mb-2">{{ __('Recommended wide banner, JPG or PNG, up to 4MB') }}</p>

        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
            <div class="h-24 w-full sm:w-48 overflow-hidden rounded-xl bg-slate-100 ring-1 ring-slate-200 grid place-items-center shrink-0">
                <template x-if="preview">
                    <img :src="preview" alt="Slide preview" class="h-full w-full object-cover">
                </template>
                <template x-if="!preview">
                    <span class="text-xs font-medium text-slate-400">{{ __('No image') }}</span>
                </template>
            </div>
            <label class="cursor-pointer rounded-lg bg-brand-800/5 px-4 py-2 text-xs font-semibold text-brand-800 hover:bg-brand-800/10 text-center">
                {{ __('Choose Image') }}
                <input type="file" name="image" accept="image/*" class="hidden" @if(!$editing) required @endif
                       @change="const f = $event.target.files[0]; if (f) preview = URL.createObjectURL(f)">
            </label>
        </div>
        <x-input-error class="mt-2" :messages="$errors->get('image')" />
    </div>

    <div>
        <x-input-label for="link_url" :value="__('Link URL (optional)')" />
        <x-text-input id="link_url" name="link_url" type="text" class="mt-1 block w-full"
                      :value="old('link_url', $slide->link_url ?? '')" placeholder="https://... or /shop" />
        <x-input-error class="mt-2" :messages="$errors->get('link_url')" />
    </div>

    <div>
        <x-input-label for="sort_order" :value="__('Sort Order')" />
        <x-text-input id="sort_order" name="sort_order" type="number" min="0" class="mt-1 block w-full"
                      :value="old('sort_order', $slide->sort_order ?? 0)" required />
        <p class="mt-1 text-[11px] text-slate-400">{{ __('Lower numbers show first') }}</p>
        <x-input-error class="mt-2" :messages="$errors->get('sort_order')" />
    </div>

    <label class="flex items-center gap-2 text-sm text-slate-700">
        <input type="checkbox" name="status" value="1" class="rounded border-slate-300 text-accent-600 focus:ring-accent-500"
               @checked(old('status', $slide->status ?? true))>
        {{ __('Active') }}
    </label>
</div>

<div class="mt-5 flex items-center gap-3">
    <x-primary-button>{{ $editing ? __('Update Slide') : __('Add Slide') }}</x-primary-button>
    <a href="{{ route('hero-slides.index') }}" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50">{{ __('Cancel') }}</a>
</div>
