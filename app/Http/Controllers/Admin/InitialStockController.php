<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Site;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InitialStockController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:inventory.create'),
        ];
    }

    /**
     * Bulk opening-stock entry: pick a Site, then enter quantity/cost for
     * every eligible product in one form submit. Variable (has_variants)
     * products are excluded here — opening stock for their variants needs
     * its own screen once the variant-level ledger is built.
     */
    public function index(Request $request)
    {
        $sites = Site::where('status', true)->orderBy('name')->get();
        $site = $request->filled('site_id') ? Site::findOrFail($request->integer('site_id')) : null;

        $products = collect();

        if ($site) {
            // Once a product has ANY movement at this site (initial stock,
            // purchase, sale, ...) its history has already started — an
            // initial-stock entry after that would double-count on top of
            // real transactions, so it drops out of this list for good.
            $hasMovement = StockMovement::where('site_id', $site->id)->pluck('product_id');

            $products = Product::with('stockUnit')
                ->where('status', true)
                ->where('has_variants', false)
                ->whereNotIn('id', $hasMovement)
                ->orderBy('name')
                ->get();
        }

        return view('admin.stock.initial', compact('sites', 'site', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'site_id' => ['required', 'integer', 'exists:sites,id'],
            'moved_at' => ['required', 'date'],
            'rows' => ['required', 'array', 'min:1'],
            'rows.*.quantity' => ['nullable', 'numeric', 'min:0'],
            'rows.*.unit_cost' => ['nullable', 'numeric', 'min:0'],
            'rows.*.batch_no' => ['nullable', 'string', 'max:100'],
            'rows.*.expiry_date' => ['nullable', 'date'],
            'rows.*.serial_no' => ['nullable', 'string', 'max:100'],
        ]);

        // Only real, eligible products (guards against a tampered product-id key).
        $validProductIds = Product::whereIn('id', array_keys($validated['rows']))
            ->where('has_variants', false)
            ->pluck('id')
            ->all();

        // Never seed a product/site that already has ANY movement — history
        // is never overwritten or double-counted, only corrected via adjustments.
        $hasMovement = StockMovement::where('site_id', $validated['site_id'])
            ->whereIn('product_id', $validProductIds)
            ->pluck('product_id')
            ->all();

        $rows = collect($validated['rows'])
            ->filter(fn ($row, $productId) => in_array($productId, $validProductIds)
                && ! in_array($productId, $hasMovement)
                && ! empty($row['quantity'])
                && $row['quantity'] > 0
            );

        if ($rows->isEmpty()) {
            return back()->with('error', 'Enter a quantity for at least one product.');
        }

        DB::transaction(function () use ($rows, $validated) {
            foreach ($rows as $productId => $row) {
                StockMovement::create([
                    'product_id' => $productId,
                    'site_id' => $validated['site_id'],
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

        return redirect()->route('stock.initial.index', ['site_id' => $validated['site_id']])
            ->with('success', $rows->count().' product(s) opening stock saved.');
    }
}
