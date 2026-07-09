<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Site;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class StockReportController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:inventory.view'),
        ];
    }

    /**
     * Current stock per SKU at a Site — SUM(in) - SUM(out) from the
     * stock_movements ledger, never a stored counter. Variable products
     * contribute one row per variant (their own SKU/stock), not one row
     * for the parent product.
     */
    public function index(Request $request)
    {
        $sites = Site::where('status', true)->orderBy('name')->get();
        $categories = Category::orderBy('name')->get();
        $site = $request->filled('site_id') ? Site::findOrFail($request->integer('site_id')) : null;

        $products = null;
        $productBalances = collect();
        $variantBalances = collect();

        if ($site) {
            $q = $request->q;
            $categoryId = $request->filled('category_id') ? $request->integer('category_id') : null;

            $simpleRows = Product::with(['stockUnit', 'category'])
                ->where('status', true)
                ->where('has_variants', false)
                ->when($q, fn ($query) => $query->where(function ($query) use ($q) {
                    $query->where('name', 'like', "%{$q}%")->orWhere('sku', 'like', "%{$q}%");
                }))
                ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
                ->get()
                ->map(fn (Product $product) => (object) [
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'category' => $product->category?->name,
                    'unit' => $product->stockUnit?->short_name,
                    'reorder_level' => $product->reorder_level,
                    'product_id' => $product->id,
                    'variant_id' => null,
                ]);

            $variantRows = Product::with(['stockUnit', 'category', 'variants.attributeValues'])
                ->where('status', true)
                ->where('has_variants', true)
                ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
                ->get()
                ->flatMap(function (Product $product) use ($q) {
                    return $product->variants
                        ->where('status', true)
                        ->when($q, fn ($variants) => $variants->filter(
                            fn (ProductVariant $v) => str_contains(strtolower($product->name), strtolower($q))
                                || str_contains(strtolower($v->sku), strtolower($q))
                        ))
                        ->map(fn (ProductVariant $variant) => (object) [
                            'name' => $product->name.' — '.$variant->label,
                            'sku' => $variant->sku,
                            'category' => $product->category?->name,
                            'unit' => $product->stockUnit?->short_name,
                            'reorder_level' => $product->reorder_level,
                            'product_id' => $product->id,
                            'variant_id' => $variant->id,
                        ]);
                });

            $all = $simpleRows->concat($variantRows)->sortBy('name')->values();

            $perPage = 20;
            $page = max(1, $request->integer('page', 1));
            $products = new LengthAwarePaginator(
                $all->forPage($page, $perPage)->values(),
                $all->count(),
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );

            $productIds = $products->getCollection()->whereNull('variant_id')->pluck('product_id');
            $variantIds = $products->getCollection()->whereNotNull('variant_id')->pluck('variant_id');

            $productBalances = StockMovement::where('site_id', $site->id)
                ->whereIn('product_id', $productIds)
                ->whereNull('product_variant_id')
                ->selectRaw("product_id, SUM(CASE WHEN direction = 'in' THEN quantity ELSE -quantity END) as balance")
                ->groupBy('product_id')
                ->pluck('balance', 'product_id');

            $variantBalances = StockMovement::where('site_id', $site->id)
                ->whereIn('product_variant_id', $variantIds)
                ->selectRaw("product_variant_id, SUM(CASE WHEN direction = 'in' THEN quantity ELSE -quantity END) as balance")
                ->groupBy('product_variant_id')
                ->pluck('balance', 'product_variant_id');
        }

        return view('admin.stock.report', compact('sites', 'categories', 'site', 'products', 'productBalances', 'variantBalances'));
    }
}
