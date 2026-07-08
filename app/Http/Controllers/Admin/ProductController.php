<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\Brand;
use App\Models\Category;
use App\Models\CompanySetting;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProductController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:inventory.view', only: ['index', 'barcode']),
            new Middleware('permission:inventory.create', only: ['create', 'store']),
            new Middleware('permission:inventory.edit', only: ['edit', 'update', 'toggleStatus']),
            new Middleware('permission:inventory.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $products = Product::with(['category', 'brand', 'stockUnit'])
            ->when($request->filled('q'), fn ($q) => $q->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->q}%")
                    ->orWhere('sku', 'like', "%{$request->q}%")
                    ->orWhere('barcode', 'like', "%{$request->q}%");
            }))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        return view('admin.products.create', $this->formOptions());
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);
        $variants = $validated['has_variants'] ? $this->validateVariants($request, null) : [];

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('products', 'public');
        }

        $product = DB::transaction(function () use ($validated, $variants) {
            $product = Product::create(collect($validated)->except('image')->all());
            if ($product->has_variants) {
                $this->syncVariants($product, $variants);
            }

            return $product;
        });

        return redirect()->route('products.index')->with('success', "Product \"{$product->name}\" created.");
    }

    public function edit(Product $product)
    {
        return view('admin.products.edit', array_merge(['product' => $product], $this->formOptions()));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $this->validated($request, $product);
        $variants = $validated['has_variants'] ? $this->validateVariants($request, $product) : [];

        if ($request->boolean('remove_image') && $product->image_path) {
            Storage::disk('public')->delete($product->image_path);
            $validated['image_path'] = null;
        }

        if ($request->hasFile('image')) {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('products', 'public');
        }

        DB::transaction(function () use ($request, $product, $validated, $variants) {
            $product->update(collect($validated)->except(['image', 'remove_image'])->all());

            if ($product->has_variants) {
                $this->syncVariants($product, $variants);
            } else {
                // Feature turned off — drop any existing variants and their links.
                $product->variants()->each(function (ProductVariant $variant) {
                    $variant->attributeValues()->detach();
                    $variant->delete();
                });
            }
        });

        return redirect()->route('products.index')->with('success', "Product \"{$product->name}\" updated.");
    }

    public function toggleStatus(Product $product)
    {
        $product->update(['status' => ! $product->status]);

        return back()->with('success', "Product \"{$product->name}\" is now ".($product->status ? 'active' : 'inactive').'.');
    }

    public function barcode()
    {
        $products = Product::orderBy('name')->get()->map(fn (Product $product) => [
            'id' => $product->id,
            'name' => $product->name,
            'value' => $product->barcode ?: $product->sku,
            'price' => number_format($product->selling_price, 2),
        ]);

        return view('admin.products.barcode', [
            'products' => $products,
            'companyName' => CompanySetting::current()->name,
        ]);
    }

    public function destroy(Product $product)
    {
        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }

        $product->delete();

        return redirect()->route('products.index')->with('success', "Product \"{$product->name}\" deleted.");
    }

    protected function formOptions(): array
    {
        return [
            'categories' => Category::orderBy('name')->get(),
            'brands' => Brand::orderBy('name')->get(),
            'units' => Unit::orderBy('name')->get(),
            'attributes' => Attribute::with('values')->orderBy('name')->get(),
        ];
    }

    protected function validated(Request $request, ?Product $product = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($product?->id)],
            'barcode' => ['nullable', 'string', 'max:100', Rule::unique('products', 'barcode')->ignore($product?->id)],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'brand_id' => ['nullable', 'integer', 'exists:brands,id'],
            'stock_unit_id' => ['required', 'integer', 'exists:units,id'],
            'purchase_unit_id' => ['nullable', 'integer', 'exists:units,id'],
            'purchase_unit_conversion' => ['required', 'numeric', 'min:0.0001'],
            'sale_unit_id' => ['nullable', 'integer', 'exists:units,id'],
            'sale_unit_conversion' => ['required', 'numeric', 'min:0.0001'],
            'description' => ['nullable', 'string', 'max:2000'],
            'estimated_cost' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'reorder_level' => ['required', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'max:2048'],
            'remove_image' => ['nullable', 'boolean'],
            'has_variants' => ['nullable', 'boolean'],
            'track_batch' => ['nullable', 'boolean'],
            'track_expiry' => ['nullable', 'boolean'],
            'track_serial' => ['nullable', 'boolean'],
            'warranty_period' => ['nullable', 'integer', 'min:1'],
            'warranty_unit' => ['nullable', 'required_with:warranty_period', 'in:days,months,years'],
            'guarantee_period' => ['nullable', 'integer', 'min:1'],
            'guarantee_unit' => ['nullable', 'required_with:guarantee_period', 'in:days,months,years'],
        ]);

        // Normalize booleans (unchecked checkboxes are absent from the request).
        foreach (['has_variants', 'track_batch', 'track_expiry', 'track_serial'] as $flag) {
            $validated[$flag] = $request->boolean($flag);
        }

        // Expiry lives on a batch — enabling it implies batch tracking.
        if ($validated['track_expiry']) {
            $validated['track_batch'] = true;
        }

        // A duration with no period is meaningless — null the unit.
        if (empty($validated['warranty_period'])) {
            $validated['warranty_period'] = null;
            $validated['warranty_unit'] = null;
        }
        if (empty($validated['guarantee_period'])) {
            $validated['guarantee_period'] = null;
            $validated['guarantee_unit'] = null;
        }

        return $validated;
    }

    /**
     * Validate the nested variants payload. Only called when has_variants is on.
     * Uniqueness for sku/barcode is enforced against product_variants, ignoring
     * rows belonging to this product (so its own variants don't self-collide).
     */
    protected function validateVariants(Request $request, ?Product $product): array
    {
        $ignoreIds = $product ? $product->variants()->pluck('id')->all() : [];

        $validated = $request->validate([
            'variants' => ['required', 'array', 'min:1'],
            'variants.*.id' => ['nullable', 'integer'],
            'variants.*.sku' => ['required', 'string', 'max:100', 'distinct',
                function ($attr, $value, $fail) use ($ignoreIds) {
                    $exists = ProductVariant::where('sku', $value)->whereNotIn('id', $ignoreIds)->exists();
                    if ($exists) {
                        $fail('This SKU is already in use.');
                    }
                }],
            'variants.*.barcode' => ['nullable', 'string', 'max:100', 'distinct',
                function ($attr, $value, $fail) use ($ignoreIds) {
                    if ($value === null || $value === '') {
                        return;
                    }
                    $exists = ProductVariant::where('barcode', $value)->whereNotIn('id', $ignoreIds)->exists();
                    if ($exists) {
                        $fail('This barcode is already in use.');
                    }
                }],
            'variants.*.selling_price' => ['required', 'numeric', 'min:0'],
            'variants.*.estimated_cost' => ['nullable', 'numeric', 'min:0'],
            'variants.*.status' => ['nullable', 'boolean'],
            'variants.*.values' => ['required', 'array', 'min:1'],
            'variants.*.values.*' => ['required', 'integer', 'exists:attribute_values,id'],
        ]);

        return $validated['variants'];
    }

    /**
     * Create/update/delete variants for a product and re-sync their
     * attribute-value pivot rows. $variants is the validated variant array.
     */
    protected function syncVariants(Product $product, array $variants): void
    {
        $keepIds = [];

        foreach ($variants as $row) {
            $attributes = [
                'sku' => $row['sku'],
                'barcode' => $row['barcode'] ?? null,
                'selling_price' => $row['selling_price'],
                'estimated_cost' => $row['estimated_cost'] ?? null,
                'status' => (bool) ($row['status'] ?? true),
            ];

            $variant = ! empty($row['id'])
                ? tap($product->variants()->findOrFail($row['id']))->update($attributes)
                : $product->variants()->create($attributes);

            $keepIds[] = $variant->id;

            // Rebuild the attribute-value links: values is [attribute_id => attribute_value_id].
            $variant->attributeValues()->detach();
            foreach ($row['values'] as $attributeId => $valueId) {
                $variant->attributeValues()->attach($valueId, ['attribute_id' => (int) $attributeId]);
            }
        }

        // Remove variants no longer present.
        $product->variants()->whereNotIn('id', $keepIds)->each(function (ProductVariant $variant) {
            $variant->attributeValues()->detach();
            $variant->delete();
        });
    }
}
