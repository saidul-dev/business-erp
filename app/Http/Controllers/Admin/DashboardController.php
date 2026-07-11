<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Collection as CollectionModel;
use App\Models\LedgerAccount;
use App\Models\Party;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Auth;

/**
 * Owner's Daily Digest (README M9) — every stat here is scoped to the
 * user's Current Site (see SetCurrentSite middleware), except Collections:
 * collections/payments carry no site_id (they're pure accounting entries,
 * not tied to a warehouse), so that side of the picture is always
 * company-wide regardless of the site selector.
 */
class DashboardController extends Controller
{
    public function index()
    {
        $siteId = Auth::user()->current_site_id;

        $todaySales = (float) Sale::whereDate('order_date', today())
            ->where('status', '!=', 'cancelled')
            ->when($siteId, fn ($q) => $q->where('site_id', $siteId))
            ->sum('total_amount');

        $todayCollection = (float) CollectionModel::whereDate('collection_date', today())->sum('amount');

        $totalReceivable = $this->accountBalance('accounts_receivable', $siteId);

        [$lowStockItems, $lowStockCount, $criticalCount] = $this->lowStock($siteId);

        [$chartLabels, $salesSeries, $collectionSeries] = $this->weeklyChart($siteId);

        $topOverdueCustomers = Party::where('is_customer', true)->where('status', true)->get()
            ->map(fn (Party $party) => ['id' => $party->id, 'name' => $party->name, 'due' => $party->receivableBalance()])
            ->filter(fn (array $row) => $row['due'] > 0)
            ->sortByDesc('due')
            ->take(6)
            ->values();

        $recentSales = Sale::with('party')
            ->when($siteId, fn ($q) => $q->where('site_id', $siteId))
            ->orderByDesc('order_date')
            ->orderByDesc('id')
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'todaySales', 'todayCollection', 'totalReceivable',
            'lowStockItems', 'lowStockCount', 'criticalCount',
            'chartLabels', 'salesSeries', 'collectionSeries',
            'topOverdueCustomers', 'recentSales',
        ));
    }

    /**
     * Signed balance of a system ledger account, optionally restricted to
     * one Site via the parent ledger_transactions row — mirrors
     * LedgerAccount::balance() but adds the site filter that method doesn't
     * have. Opening-balance postings carry no site_id, so they naturally
     * drop out once a specific Site is selected — acceptable since an
     * opening balance predates any site-level activity anyway.
     */
    protected function accountBalance(string $code, ?int $siteId): float
    {
        $account = LedgerAccount::where('code', $code)->first();

        if (! $account) {
            return 0.0;
        }

        $raw = (float) $account->lines()
            ->when($siteId, fn ($q) => $q->whereHas('transaction', fn ($qt) => $qt->where('site_id', $siteId)))
            ->selectRaw('COALESCE(SUM(debit) - SUM(credit), 0) as bal')
            ->value('bal');

        return $account->nature === 'credit' ? -$raw : $raw;
    }

    /**
     * Every simple product / variant tracked for reorder (reorder_level > 0)
     * whose current Site balance has dropped to or below that level — same
     * "balance <= reorder_level" convention as StockReportController. A
     * variant has no reorder_level of its own; it inherits its parent
     * product's.
     *
     * @return array{0: \Illuminate\Support\Collection, 1: int, 2: int} [rows to display, total low-stock count, out-of-stock count within it]
     */
    protected function lowStock(?int $siteId): array
    {
        $products = Product::with('stockUnit')
            ->where('status', true)->where('has_variants', false)->where('reorder_level', '>', 0)->get();

        $variantProducts = Product::with(['stockUnit', 'variants.attributeValues'])
            ->where('status', true)->where('has_variants', true)->where('reorder_level', '>', 0)
            ->get()
            ->each(fn (Product $p) => $p->setRelation('variants', $p->variants->where('status', true)->values()));

        $productIds = $products->pluck('id');
        $variantIds = $variantProducts->flatMap(fn (Product $p) => $p->variants->pluck('id'));

        $productBalances = StockMovement::whereIn('product_id', $productIds)->whereNull('product_variant_id')
            ->when($siteId, fn ($q) => $q->where('site_id', $siteId))
            ->selectRaw("product_id, SUM(CASE WHEN direction = 'in' THEN quantity ELSE -quantity END) as balance")
            ->groupBy('product_id')->pluck('balance', 'product_id');

        $variantBalances = StockMovement::whereIn('product_variant_id', $variantIds)
            ->when($siteId, fn ($q) => $q->where('site_id', $siteId))
            ->selectRaw("product_variant_id, SUM(CASE WHEN direction = 'in' THEN quantity ELSE -quantity END) as balance")
            ->groupBy('product_variant_id')->pluck('balance', 'product_variant_id');

        $rows = collect();

        foreach ($products as $product) {
            $balance = (float) ($productBalances[$product->id] ?? 0);

            if ($balance <= $product->reorder_level) {
                $rows->push((object) [
                    'name' => $product->name,
                    'unit' => $product->stockUnit?->short_name,
                    'balance' => $balance,
                    'reorder_level' => $product->reorder_level,
                ]);
            }
        }

        foreach ($variantProducts as $product) {
            foreach ($product->variants as $variant) {
                $balance = (float) ($variantBalances[$variant->id] ?? 0);

                if ($balance <= $product->reorder_level) {
                    $rows->push((object) [
                        'name' => "{$product->name} — {$variant->label}",
                        'unit' => $product->stockUnit?->short_name,
                        'balance' => $balance,
                        'reorder_level' => $product->reorder_level,
                    ]);
                }
            }
        }

        $rows = $rows->sortBy('balance')->values();

        return [$rows->take(4), $rows->count(), $rows->where('balance', '<=', 0)->count()];
    }

    /**
     * Last 7 days (today inclusive) of Sales value vs Collections received,
     * for the "Sales vs Collection" chart.
     *
     * @return array{0: array<string>, 1: array<float>, 2: array<float>}
     */
    protected function weeklyChart(?int $siteId): array
    {
        $labels = [];
        $salesSeries = [];
        $collectionSeries = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = today()->subDays($i);
            $labels[] = $date->format('d M');

            $salesSeries[] = (float) Sale::whereDate('order_date', $date)
                ->where('status', '!=', 'cancelled')
                ->when($siteId, fn ($q) => $q->where('site_id', $siteId))
                ->sum('total_amount');

            $collectionSeries[] = (float) CollectionModel::whereDate('collection_date', $date)->sum('amount');
        }

        return [$labels, $salesSeries, $collectionSeries];
    }
}
