<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Branch;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InitialStockController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:inventory.create'),
        ];
    }

    /**
     * Bulk opening-stock entry: pick a Branch, then enter quantity/cost for
     * every eligible product (and, for variable products, every eligible
     * variant) in one form submit.
     */
    public function index(Request $request)
    {
        $branches = Branch::where('status', true)->orderBy('name')->get();
        $branch = $request->filled('branch_id') ? Branch::findOrFail($request->integer('branch_id')) : null;

        $products = collect();
        $variantGroups = collect();

        if ($branch) {
            // Once a product/variant has ANY movement at this branch (initial
            // stock, purchase, sale, ...) its history has already started —
            // an initial-stock entry after that would double-count on top of
            // real transactions, so it drops out of this list for good.
            $movements = StockMovement::where('branch_id', $branch->id)->get(['product_id', 'product_variant_id']);
            $seededProductIds = $movements->whereNull('product_variant_id')->pluck('product_id');
            $seededVariantIds = $movements->whereNotNull('product_variant_id')->pluck('product_variant_id');

            $products = Product::with('stockUnit')
                ->where('status', true)
                ->where('has_variants', false)
                ->whereNotIn('id', $seededProductIds)
                ->orderBy('name')
                ->get();

            $variantGroups = Product::with(['stockUnit', 'variants.attributeValues'])
                ->where('status', true)
                ->where('has_variants', true)
                ->orderBy('name')
                ->get()
                ->map(function (Product $product) use ($seededVariantIds) {
                    $product->setRelation('variants', $product->variants
                        ->where('status', true)
                        ->whereNotIn('id', $seededVariantIds)
                        ->values());

                    return $product;
                })
                ->filter(fn (Product $product) => $product->variants->isNotEmpty())
                ->values();
        }

        return view('admin.stock.initial', compact('branches', 'branch', 'products', 'variantGroups'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'moved_at' => ['required', 'date'],
            'products' => ['nullable', 'array'],
            'products.*.quantity' => ['nullable', 'numeric', 'min:0'],
            'products.*.unit_cost' => ['nullable', 'numeric', 'min:0'],
            'products.*.batch_no' => ['nullable', 'string', 'max:100'],
            'products.*.expiry_date' => ['nullable', 'date'],
            'products.*.serial_no' => ['nullable', 'string', 'max:100'],
            'variants' => ['nullable', 'array'],
            'variants.*.quantity' => ['nullable', 'numeric', 'min:0'],
            'variants.*.unit_cost' => ['nullable', 'numeric', 'min:0'],
            'variants.*.batch_no' => ['nullable', 'string', 'max:100'],
            'variants.*.expiry_date' => ['nullable', 'date'],
            'variants.*.serial_no' => ['nullable', 'string', 'max:100'],
        ]);

        $productRows = collect($validated['products'] ?? []);
        $variantRows = collect($validated['variants'] ?? []);

        // Only real, eligible records (guards against a tampered id key).
        $validProductIds = Product::whereIn('id', $productRows->keys())
            ->where('has_variants', false)
            ->pluck('id')
            ->all();

        $validVariants = ProductVariant::whereIn('id', $variantRows->keys())->get()->keyBy('id');

        // Never seed a product/variant/branch that already has ANY movement —
        // history is never overwritten or double-counted, only corrected via adjustments.
        $seeded = StockMovement::where('branch_id', $validated['branch_id'])
            ->where(function ($q) use ($validProductIds, $validVariants) {
                $q->whereIn('product_id', $validProductIds)
                    ->orWhereIn('product_variant_id', $validVariants->keys());
            })
            ->get(['product_id', 'product_variant_id']);

        $seededProductIds = $seeded->whereNull('product_variant_id')->pluck('product_id')->all();
        $seededVariantIds = $seeded->whereNotNull('product_variant_id')->pluck('product_variant_id')->all();

        $productsToInsert = $productRows->filter(fn ($row, $productId) => in_array($productId, $validProductIds)
            && ! in_array($productId, $seededProductIds)
            && ! empty($row['quantity'])
            && $row['quantity'] > 0
        );

        $variantsToInsert = $variantRows->filter(fn ($row, $variantId) => $validVariants->has($variantId)
            && ! in_array($variantId, $seededVariantIds)
            && ! empty($row['quantity'])
            && $row['quantity'] > 0
        );

        if ($productsToInsert->isEmpty() && $variantsToInsert->isEmpty()) {
            return back()->with('error', 'Enter a quantity for at least one product.');
        }

        // Unit cost is how this item's average cost gets seeded — every row
        // that's actually being inserted needs one, even though the column
        // stays optional at the validation level (most rows in this bulk
        // form are left blank/zero to skip them).
        $missingCost = collect();
        foreach ($productsToInsert as $productId => $row) {
            if (empty($row['unit_cost']) || $row['unit_cost'] <= 0) {
                $missingCost["products.{$productId}.unit_cost"] = 'Unit cost is required.';
            }
        }
        foreach ($variantsToInsert as $variantId => $row) {
            if (empty($row['unit_cost']) || $row['unit_cost'] <= 0) {
                $missingCost["variants.{$variantId}.unit_cost"] = 'Unit cost is required.';
            }
        }
        if ($missingCost->isNotEmpty()) {
            throw ValidationException::withMessages($missingCost->all());
        }

        DB::transaction(function () use ($productsToInsert, $variantsToInsert, $validVariants, $validated) {
            foreach ($productsToInsert as $productId => $row) {
                StockMovement::create([
                    'product_id' => $productId,
                    'branch_id' => $validated['branch_id'],
                    'type' => 'initial_stock',
                    'quantity' => $row['quantity'],
                    'unit_cost' => $row['unit_cost'] ?? null,
                    'batch_no' => $row['batch_no'] ?? null,
                    'expiry_date' => $row['expiry_date'] ?? null,
                    'serial_no' => $row['serial_no'] ?? null,
                    'moved_at' => $validated['moved_at'],
                    'created_by' => Auth::id(),
                ]);
            }

            foreach ($variantsToInsert as $variantId => $row) {
                $variant = $validVariants[$variantId];

                StockMovement::create([
                    'product_id' => $variant->product_id,
                    'product_variant_id' => $variant->id,
                    'branch_id' => $validated['branch_id'],
                    'type' => 'initial_stock',
                    'quantity' => $row['quantity'],
                    'unit_cost' => $row['unit_cost'] ?? null,
                    'batch_no' => $row['batch_no'] ?? null,
                    'expiry_date' => $row['expiry_date'] ?? null,
                    'serial_no' => $row['serial_no'] ?? null,
                    'moved_at' => $validated['moved_at'],
                    'created_by' => Auth::id(),
                ]);
            }
        });

        $count = $productsToInsert->count() + $variantsToInsert->count();

        return redirect()->route('stock.initial.index', ['branch_id' => $validated['branch_id']])
            ->with('success', "{$count} item(s) opening stock saved.");
    }
}
