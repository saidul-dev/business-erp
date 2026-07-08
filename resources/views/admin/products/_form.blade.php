@php $editing = isset($product); @endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4" x-data="{ preview: @js($product->image_url ?? null), removeImage: false }">
    <!-- Image -->
    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <div class="mb-4">
            <h3 class="font-bold text-brand-900">Product Image</h3>
            <p class="text-xs text-slate-400">PNG or JPG, up to 2MB</p>
        </div>

        <div class="flex flex-col items-center gap-4">
            <div class="grid h-28 w-28 place-items-center overflow-hidden rounded-2xl bg-slate-100 ring-1 ring-slate-200">
                <template x-if="preview">
                    <img :src="preview" alt="Product preview" class="h-full w-full object-cover">
                </template>
                <template x-if="!preview">
                    <svg class="h-10 w-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/></svg>
                </template>
            </div>

            <label class="cursor-pointer rounded-lg bg-brand-800/5 px-4 py-2 text-xs font-semibold text-brand-800 hover:bg-brand-800/10">
                Choose Image
                <input type="file" name="image" accept="image/*" class="hidden"
                       @change="removeImage = false; const f = $event.target.files[0]; if (f) preview = URL.createObjectURL(f)">
            </label>

            @if ($editing && $product->image_url)
            <label class="flex items-center gap-2 text-xs text-slate-500">
                <input type="checkbox" name="remove_image" value="1" x-model="removeImage"
                       @change="if (removeImage) preview = null; else preview = @js($product->image_url)"
                       class="rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                Remove current image
            </label>
            @endif
        </div>
        <x-input-error class="mt-3" :messages="$errors->get('image')" />
    </div>

    <!-- Basic details -->
    <div class="lg:col-span-2 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 space-y-5">
        <div>
            <h3 class="font-bold text-brand-900">Basic Details</h3>
            <p class="text-xs text-slate-400">Name and identifiers</p>
        </div>

        <div>
            <x-input-label for="name" value="Product Name" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                          :value="old('name', $product->name ?? '')" required autofocus />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="sku" value="SKU" />
                <x-text-input id="sku" name="sku" type="text" class="mt-1 block w-full"
                              :value="old('sku', $product->sku ?? '')" required placeholder="e.g. TSHIRT-BLK-M" />
                <x-input-error class="mt-2" :messages="$errors->get('sku')" />
            </div>
            <div>
                <x-input-label for="barcode" value="Barcode (optional)" />
                <x-text-input id="barcode" name="barcode" type="text" class="mt-1 block w-full"
                              :value="old('barcode', $product->barcode ?? '')" />
                <x-input-error class="mt-2" :messages="$errors->get('barcode')" />
            </div>
        </div>

        <div>
            <x-input-label for="description" value="Description (optional)" />
            <textarea id="description" name="description" rows="3"
                      class="mt-1 block w-full rounded-lg border-slate-300 focus:border-accent-500 focus:ring-accent-500">{{ old('description', $product->description ?? '') }}</textarea>
            <x-input-error class="mt-2" :messages="$errors->get('description')" />
        </div>
    </div>

    <!-- Classification -->
    <div class="lg:col-span-3 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <div class="mb-4">
            <h3 class="font-bold text-brand-900">Classification</h3>
            <p class="text-xs text-slate-400">Category and brand</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="category_id" value="Category" />
                <select id="category_id" name="category_id" required
                        class="mt-1 block w-full rounded-lg border-slate-300 focus:border-accent-500 focus:ring-accent-500">
                    <option value="">Select category</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id ?? '') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('category_id')" />
                @if ($categories->isEmpty())
                    <p class="mt-1 text-xs text-amber-600">No categories yet — <a href="{{ route('categories.create') }}" class="underline">add one first</a>.</p>
                @endif
            </div>

            <div>
                <x-input-label for="brand_id" value="Brand (optional)" />
                <select id="brand_id" name="brand_id"
                        class="mt-1 block w-full rounded-lg border-slate-300 focus:border-accent-500 focus:ring-accent-500">
                    <option value="">No brand</option>
                    @foreach ($brands as $brand)
                        <option value="{{ $brand->id }}" @selected(old('brand_id', $product->brand_id ?? '') == $brand->id)>{{ $brand->name }}</option>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('brand_id')" />
            </div>
        </div>
    </div>

    <!-- Units -->
    <div class="lg:col-span-3 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <div class="mb-4">
            <h3 class="font-bold text-brand-900">Units</h3>
            <p class="text-xs text-slate-400">Stock is always tracked in the Stock Unit. Purchase and Sale units are optional — leave blank to use the Stock Unit directly.</p>
        </div>

        @if ($units->isEmpty())
            <p class="text-sm text-amber-600">No units yet — <a href="{{ route('units.create') }}" class="underline font-semibold">add one first</a>.</p>
        @else
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <x-input-label for="stock_unit_id" value="Stock Unit" />
                <select id="stock_unit_id" name="stock_unit_id" required
                        class="mt-1 block w-full rounded-lg border-slate-300 focus:border-accent-500 focus:ring-accent-500">
                    <option value="">Select unit</option>
                    @foreach ($units as $unit)
                        <option value="{{ $unit->id }}" @selected(old('stock_unit_id', $product->stock_unit_id ?? '') == $unit->id)>{{ $unit->name }} ({{ $unit->short_name }})</option>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('stock_unit_id')" />
            </div>

            <div>
                <x-input-label for="purchase_unit_id" value="Purchase Unit (optional)" />
                <select id="purchase_unit_id" name="purchase_unit_id"
                        class="mt-1 block w-full rounded-lg border-slate-300 focus:border-accent-500 focus:ring-accent-500">
                    <option value="">Same as Stock Unit</option>
                    @foreach ($units as $unit)
                        <option value="{{ $unit->id }}" @selected(old('purchase_unit_id', $product->purchase_unit_id ?? '') == $unit->id)>{{ $unit->name }} ({{ $unit->short_name }})</option>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('purchase_unit_id')" />

                <div class="mt-2">
                    <x-input-label for="purchase_unit_conversion" value="1 Purchase Unit =" class="text-xs" />
                    <div class="mt-1 flex items-center gap-2">
                        <x-text-input id="purchase_unit_conversion" name="purchase_unit_conversion" type="number" step="0.0001" min="0.0001" class="block w-full"
                                      :value="old('purchase_unit_conversion', $product->purchase_unit_conversion ?? '1')" required />
                        <span class="text-xs text-slate-400 shrink-0">Stock Unit(s)</span>
                    </div>
                    <p class="mt-1 text-[11px] text-slate-400">e.g. 1 Box = 12 Pcs</p>
                    <x-input-error class="mt-1" :messages="$errors->get('purchase_unit_conversion')" />
                </div>
            </div>

            <div>
                <x-input-label for="sale_unit_id" value="Sale Unit (optional)" />
                <select id="sale_unit_id" name="sale_unit_id"
                        class="mt-1 block w-full rounded-lg border-slate-300 focus:border-accent-500 focus:ring-accent-500">
                    <option value="">Same as Stock Unit</option>
                    @foreach ($units as $unit)
                        <option value="{{ $unit->id }}" @selected(old('sale_unit_id', $product->sale_unit_id ?? '') == $unit->id)>{{ $unit->name }} ({{ $unit->short_name }})</option>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('sale_unit_id')" />

                <div class="mt-2">
                    <x-input-label for="sale_unit_conversion" value="1 Sale Unit =" class="text-xs" />
                    <div class="mt-1 flex items-center gap-2">
                        <x-text-input id="sale_unit_conversion" name="sale_unit_conversion" type="number" step="0.0001" min="0.0001" class="block w-full"
                                      :value="old('sale_unit_conversion', $product->sale_unit_conversion ?? '1')" required />
                        <span class="text-xs text-slate-400 shrink-0">Stock Unit(s)</span>
                    </div>
                    <p class="mt-1 text-[11px] text-slate-400">e.g. 1 Dozen = 12 Pcs</p>
                    <x-input-error class="mt-1" :messages="$errors->get('sale_unit_conversion')" />
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Pricing -->
    <div class="lg:col-span-3 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <div class="mb-4">
            <h3 class="font-bold text-brand-900">Pricing &amp; Stock Alert</h3>
            <p class="text-xs text-slate-400">Estimated cost is a starting reference — actual cost will come from Purchase/Production once those are tracked</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <x-input-label for="estimated_cost" value="Estimated Cost" />
                <x-text-input id="estimated_cost" name="estimated_cost" type="number" step="0.01" min="0" class="mt-1 block w-full"
                              :value="old('estimated_cost', $product->estimated_cost ?? '0')" required />
                <p class="mt-1 text-[11px] text-slate-400">Reference only, per Stock Unit — not the source of truth once purchases exist</p>
                <x-input-error class="mt-2" :messages="$errors->get('estimated_cost')" />
            </div>

            <div>
                <x-input-label for="selling_price" value="Selling Price" />
                <x-text-input id="selling_price" name="selling_price" type="number" step="0.01" min="0" class="mt-1 block w-full"
                              :value="old('selling_price', $product->selling_price ?? '0')" required />
                <p class="mt-1 text-[11px] text-slate-400">Per Sale Unit</p>
                <x-input-error class="mt-2" :messages="$errors->get('selling_price')" />
            </div>

            <div>
                <x-input-label for="reorder_level" value="Reorder Level" />
                <x-text-input id="reorder_level" name="reorder_level" type="number" min="0" class="mt-1 block w-full"
                              :value="old('reorder_level', $product->reorder_level ?? '0')" required />
                <p class="mt-1 text-[11px] text-slate-400">Low-stock alert threshold, in Stock Units (used once stock tracking is added)</p>
                <x-input-error class="mt-2" :messages="$errors->get('reorder_level')" />
            </div>
        </div>
    </div>
</div>

<div class="mt-5 flex items-center gap-3">
    <x-primary-button>{{ $editing ? 'Update Product' : 'Create Product' }}</x-primary-button>
    <a href="{{ route('products.index') }}" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50">Cancel</a>
</div>
