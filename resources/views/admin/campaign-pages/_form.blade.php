@php
    $editing = isset($page);
    $initialProduct = $page->product ?? null;
@endphp

<div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_360px]"
     x-data="{
        products: @js($products->map(fn ($p) => [
            'id' => (string) $p->id,
            'name' => $p->name,
            'short_description' => $p->short_description,
            'image_url' => $p->image_url,
            'selling_price' => (float) $p->selling_price,
            'compare_at_price' => $p->compare_at_price ? (float) $p->compare_at_price : null,
            'discount_percent' => $p->discount_percent,
        ])),
        selectedProduct: @js($initialProduct ? [
            'id' => (string) $initialProduct->id,
            'name' => $initialProduct->name,
            'short_description' => $initialProduct->short_description,
            'image_url' => $initialProduct->image_url,
            'selling_price' => (float) $initialProduct->selling_price,
            'compare_at_price' => $initialProduct->compare_at_price ? (float) $initialProduct->compare_at_price : null,
            'discount_percent' => $initialProduct->discount_percent,
        ] : null),
        headline: @js(old('headline', $page->headline ?? '')),
        subheadline: @js(old('subheadline', $page->subheadline ?? '')),
        slug: @js(old('slug', $page->slug ?? '')),
        slugTouched: {{ $editing ? 'true' : 'false' }},
        features: @js(old('features', $page->features ?? '')),
        bannerPreview: @js($page->banner_image_url ?? null),
        customBannerChosen: false,
        slugify(text) {
            return (text || '').toString().toLowerCase().trim()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');
        },
        onProductSelect(p) {
            if (! p) { this.selectedProduct = null; return; }
            this.selectedProduct = p;
            this.headline = p.name;
            this.subheadline = p.short_description || '';
            if (! this.customBannerChosen) this.bannerPreview = p.image_url;
            this.slug = this.slugify(p.name);
            this.slugTouched = false;
        },
        onHeadlineInput() {
            if (! this.slugTouched) this.slug = this.slugify(this.headline);
        },
        get featureList() {
            return this.features.split('\n').map(f => f.trim()).filter(f => f);
        },
     }"
     @option-selected-product_id.window="onProductSelect($event.detail)">

    {{-- Fields --}}
    <div class="space-y-6">
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 space-y-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Product') }}</p>

            <div>
                <x-input-label :value="__('Which product is this campaign for?')" />
                <x-searchable-select name="product_id" :options="$products" :selected="old('product_id', $page->product_id ?? null)"
                                      :extra="['short_description', 'image_url', 'selling_price', 'compare_at_price', 'discount_percent']"
                                      placeholder="{{ __('Select product…') }}" />
                <p class="mt-1 text-[11px] text-slate-400">{{ __('Picking a product fills in the headline, subheadline, URL and image below — tweak anything you like afterward.') }}</p>
                <x-input-error class="mt-2" :messages="$errors->get('product_id')" />
            </div>

            <div>
                <x-input-label for="slug" :value="__('URL Slug')" />
                <div class="mt-1 flex items-center gap-1">
                    <span class="text-sm text-slate-400 shrink-0">/campaign/</span>
                    <input id="slug" name="slug" type="text" x-model="slug" @input="slugTouched = true" required
                           class="block w-full rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500" />
                </div>
                <p class="mt-1 text-[11px] text-slate-400">{{ __('Auto-filled from the product name — edit freely, letters/numbers/dashes only') }}</p>
                <x-input-error class="mt-2" :messages="$errors->get('slug')" />
            </div>
        </div>

        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 space-y-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Content') }}</p>

            <div>
                <x-input-label for="headline" :value="__('Headline')" />
                <input id="headline" name="headline" type="text" x-model="headline" @input="onHeadlineInput()" required
                       class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500" />
                <x-input-error class="mt-2" :messages="$errors->get('headline')" />
            </div>

            <div>
                <x-input-label for="subheadline" :value="__('Subheadline (optional)')" />
                <input id="subheadline" name="subheadline" type="text" x-model="subheadline"
                       class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500" />
                <x-input-error class="mt-2" :messages="$errors->get('subheadline')" />
            </div>

            <div>
                <x-input-label for="features" :value="__('Feature Bullets (optional)')" />
                <textarea id="features" name="features" rows="4" x-model="features"
                          class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500"
                          placeholder="Premium Quality&#10;Free Home Delivery&#10;7-Day Return"></textarea>
                <p class="mt-1 text-[11px] text-slate-400">{{ __('One bullet per line') }}</p>
                <x-input-error class="mt-2" :messages="$errors->get('features')" />
            </div>
        </div>

        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 space-y-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Banner Image') }}</p>

            <div>
                <p class="text-xs text-slate-400 mb-2">{{ __('Defaults to the product photo — upload a wider custom banner if you want something different (JPG/PNG, up to 4MB)') }}</p>

                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                    <div class="h-24 w-full sm:w-48 overflow-hidden rounded-xl bg-slate-100 ring-1 ring-slate-200 grid place-items-center shrink-0">
                        <template x-if="bannerPreview">
                            <img :src="bannerPreview" alt="Banner preview" class="h-full w-full object-cover">
                        </template>
                        <template x-if="!bannerPreview">
                            <span class="text-xs font-medium text-slate-400">{{ __('No image') }}</span>
                        </template>
                    </div>
                    <label class="cursor-pointer rounded-lg bg-brand-800/5 px-4 py-2 text-xs font-semibold text-brand-800 hover:bg-brand-800/10 text-center">
                        {{ __('Choose Custom Image') }}
                        <input type="file" name="banner_image" accept="image/*" class="hidden"
                               @change="const f = $event.target.files[0]; if (f) { customBannerChosen = true; bannerPreview = URL.createObjectURL(f); }">
                    </label>
                </div>
                <x-input-error class="mt-2" :messages="$errors->get('banner_image')" />
            </div>

            <label class="flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" name="status" value="1" class="rounded border-slate-300 text-accent-600 focus:ring-accent-500"
                       @checked(old('status', $page->status ?? true))>
                {{ __('Active') }}
            </label>
        </div>

        <div class="flex items-center gap-3">
            <x-primary-button>{{ $editing ? __('Update Campaign Page') : __('Create Campaign Page') }}</x-primary-button>
            <a href="{{ route('campaign-pages.index') }}" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50">{{ __('Cancel') }}</a>
        </div>
    </div>

    {{-- Live preview --}}
    <div class="h-fit lg:sticky lg:top-6">
        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Live Preview') }}</p>
        <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
            <div class="aspect-[16/9] bg-slate-100 grid place-items-center overflow-hidden">
                <template x-if="bannerPreview">
                    <img :src="bannerPreview" alt="" class="h-full w-full object-cover">
                </template>
                <template x-if="!bannerPreview">
                    <svg class="h-10 w-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3 3h18M3 3l1.5 18h15L21 3H3Z"/></svg>
                </template>
            </div>
            <div class="p-5">
                <p class="text-lg font-extrabold leading-snug text-brand-950" x-text="headline || '{{ __('Your headline appears here') }}'"></p>
                <p class="mt-1 text-sm text-slate-500" x-text="subheadline" x-show="subheadline"></p>

                <div class="mt-3 flex items-center gap-2" x-show="selectedProduct">
                    <p class="text-xl font-bold text-accent-600" x-text="Number(selectedProduct?.selling_price ?? 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></p>
                    <template x-if="selectedProduct?.discount_percent">
                        <div class="flex items-center gap-2">
                            <p class="text-sm text-slate-400 line-through" x-text="Number(selectedProduct?.compare_at_price ?? 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></p>
                            <span class="rounded-full bg-rose-600 px-2 py-0.5 text-[11px] font-bold text-white" x-text="'-' + selectedProduct.discount_percent + '%'"></span>
                        </div>
                    </template>
                </div>

                <ul class="mt-4 space-y-1.5" x-show="featureList.length">
                    <template x-for="feature in featureList" :key="feature">
                        <li class="flex items-start gap-1.5 text-xs text-slate-600">
                            <svg class="mt-0.5 h-3.5 w-3.5 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                            <span x-text="feature"></span>
                        </li>
                    </template>
                </ul>

                <div class="mt-5 w-full rounded-lg bg-accent-500 px-4 py-3 text-center text-sm font-bold text-brand-950">
                    {{ __('Order Now') }}
                </div>
            </div>
        </div>
        <p class="mt-2 text-[11px] text-slate-400">{{ __('Approximation only — visit the real /campaign/ URL after saving to see the exact page.') }}</p>
    </div>
</div>
