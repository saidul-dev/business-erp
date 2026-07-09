<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Site;
use App\Models\StockMovement;
use Illuminate\Http\Request;
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
     * Current stock per product at a Site — SUM(in) - SUM(out) from the
     * stock_movements ledger, never a stored counter. Variable (has_variants)
     * products are excluded for now, same as Initial Stock, until there's a
     * per-variant version of this report.
     */
    public function index(Request $request)
    {
        $sites = Site::where('status', true)->orderBy('name')->get();
        $site = $request->filled('site_id') ? Site::findOrFail($request->integer('site_id')) : null;

        $products = collect();
        $stock = collect();

        if ($site) {
            $products = Product::with('stockUnit')
                ->where('status', true)
                ->where('has_variants', false)
                ->when($request->filled('q'), fn ($q) => $q->where(function ($q) use ($request) {
                    $q->where('name', 'like', "%{$request->q}%")
                        ->orWhere('sku', 'like', "%{$request->q}%");
                }))
                ->orderBy('name')
                ->paginate(20)
                ->withQueryString();

            $stock = StockMovement::where('site_id', $site->id)
                ->whereIn('product_id', $products->pluck('id'))
                ->selectRaw("product_id, SUM(CASE WHEN direction = 'in' THEN quantity ELSE -quantity END) as balance")
                ->groupBy('product_id')
                ->pluck('balance', 'product_id');
        }

        return view('admin.stock.report', compact('sites', 'site', 'products', 'stock'));
    }
}
