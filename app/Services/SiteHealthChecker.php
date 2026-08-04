<?php

namespace App\Services;

use App\Models\LedgerAccount;
use App\Models\SiteHealthSnapshot;
use Illuminate\Support\Facades\DB;

/**
 * Computes an operational "site health" score from the business's own data
 * (NOT a code-quality/static-analysis metric) — negative stock, unbalanced
 * ledger entries, stale backlog items, etc. Each category is the equal-
 * weighted average of a handful of named checks; a check with no applicable
 * records at all scores 100 (nothing to be unhealthy about), so a quiet/new
 * business isn't penalized for having no data yet.
 */
class SiteHealthChecker
{
    public function run(): SiteHealthSnapshot
    {
        $categories = [
            'data_consistency' => $this->dataConsistency(),
            'inventory_accuracy' => $this->inventoryAccuracy(),
            'financial_integrity' => $this->financialIntegrity(),
            'pending_backlog' => $this->pendingBacklog(),
        ];

        $overall = (int) round(collect($categories)->avg('score'));

        return SiteHealthSnapshot::create([
            'overall_score' => $overall,
            'data_consistency_score' => (int) round($categories['data_consistency']['score']),
            'inventory_accuracy_score' => (int) round($categories['inventory_accuracy']['score']),
            'financial_integrity_score' => (int) round($categories['financial_integrity']['score']),
            'pending_backlog_score' => (int) round($categories['pending_backlog']['score']),
            'details' => $categories,
            'computed_at' => now(),
        ]);
    }

    protected function dataConsistency(): array
    {
        $balances = DB::table('stock_movements')
            ->selectRaw('product_id, product_variant_id, branch_id, SUM(CASE WHEN direction = \'in\' THEN quantity ELSE -quantity END) as balance')
            ->groupBy('product_id', 'product_variant_id', 'branch_id')
            ->get();

        $checks = [
            $this->check(
                'No negative on-hand stock',
                $balances->where('balance', '>=', -0.0001)->count(),
                $balances->count(),
            ),
            $this->check(
                'Stock movements recorded with a positive quantity',
                DB::table('stock_movements')->where('quantity', '>', 0)->count(),
                DB::table('stock_movements')->count(),
            ),
            $this->check(
                'Active products have valid unit conversions',
                DB::table('products')->where('status', true)
                    ->where('purchase_unit_conversion', '>', 0)
                    ->where('sale_unit_conversion', '>', 0)
                    ->count(),
                DB::table('products')->where('status', true)->count(),
            ),
        ];

        return $this->category($checks);
    }

    protected function inventoryAccuracy(): array
    {
        $productBalances = DB::table('stock_movements')
            ->selectRaw('product_id, SUM(CASE WHEN direction = \'in\' THEN quantity ELSE -quantity END) as balance')
            ->groupBy('product_id')
            ->pluck('balance', 'product_id');

        $reorderTracked = DB::table('products')->where('status', true)->where('reorder_level', '>', 0)->get(['id', 'reorder_level']);
        $atOrAboveReorder = $reorderTracked->filter(fn ($p) => (float) ($productBalances[$p->id] ?? 0) >= (float) $p->reorder_level)->count();

        $costedProductIds = DB::table('stock_movements')->distinct()->pluck('product_id');
        $costedActiveProducts = DB::table('products')->where('status', true)->whereIn('id', $costedProductIds)->get(['id', 'estimated_cost']);

        $checks = [
            $this->check(
                'Products priced above cost',
                DB::table('products')->where('status', true)->where('estimated_cost', '>', 0)
                    ->whereColumn('selling_price', '>=', 'estimated_cost')->count(),
                DB::table('products')->where('status', true)->where('estimated_cost', '>', 0)->count(),
            ),
            $this->check(
                'Stock at or above reorder level',
                $atOrAboveReorder,
                $reorderTracked->count(),
            ),
            $this->check(
                'Products have a starting cost recorded',
                $costedActiveProducts->where('estimated_cost', '>', 0)->count(),
                $costedActiveProducts->count(),
            ),
        ];

        return $this->category($checks);
    }

    protected function financialIntegrity(): array
    {
        $transactionBalances = DB::table('ledger_transaction_lines')
            ->selectRaw('ledger_transaction_id, ROUND(SUM(debit), 2) as total_debit, ROUND(SUM(credit), 2) as total_credit')
            ->groupBy('ledger_transaction_id')
            ->get();

        $cashBankAccounts = LedgerAccount::where('group', 'cash_bank')->where('status', true)->get();
        $notOverdrawn = $cashBankAccounts->filter(fn (LedgerAccount $a) => $a->balance() >= -0.01)->count();

        $checks = [
            $this->check(
                'Ledger transactions balance (debit = credit)',
                $transactionBalances->filter(fn ($t) => abs((float) $t->total_debit - (float) $t->total_credit) < 0.01)->count(),
                $transactionBalances->count(),
            ),
            $this->check(
                'Cash/Bank accounts are not overdrawn',
                $notOverdrawn,
                $cashBankAccounts->count(),
            ),
            $this->check(
                'Delivered COD consignments settled',
                DB::table('courier_consignments')->where('status', 'delivered')->where('cod_amount', '>', 0)
                    ->whereNotNull('cod_settled_at')->count(),
                DB::table('courier_consignments')->where('status', 'delivered')->where('cod_amount', '>', 0)->count(),
            ),
        ];

        return $this->category($checks);
    }

    protected function pendingBacklog(): array
    {
        $checks = [
            $this->check(
                'Purchase requisitions not stuck pending',
                DB::table('purchase_requisitions')->where('status', 'pending')->where('request_date', '>', now()->subDays(7))->count(),
                DB::table('purchase_requisitions')->where('status', 'pending')->count(),
            ),
            $this->check(
                'Sale quotations not stuck pending',
                DB::table('sale_quotations')->where('status', 'pending')->where('quote_date', '>', now()->subDays(7))->count(),
                DB::table('sale_quotations')->where('status', 'pending')->count(),
            ),
            $this->check(
                'Contact messages read promptly',
                DB::table('contact_messages')->where('is_read', false)->where('created_at', '>', now()->subDays(3))->count(),
                DB::table('contact_messages')->where('is_read', false)->count(),
            ),
            $this->check(
                'Product reviews moderated promptly',
                DB::table('product_reviews')->where('status', 'pending')->where('created_at', '>', now()->subDays(7))->count(),
                DB::table('product_reviews')->where('status', 'pending')->count(),
            ),
        ];

        return $this->category($checks);
    }

    /**
     * @param  array<int, array{label: string, healthy: int, total: int, score: float}>  $checks
     */
    protected function category(array $checks): array
    {
        return [
            'score' => round(collect($checks)->avg('score'), 1),
            'checks' => $checks,
        ];
    }

    protected function check(string $label, int $healthy, int $total): array
    {
        return [
            'label' => $label,
            'healthy' => $healthy,
            'total' => $total,
            // No applicable records at all = nothing to be unhealthy about.
            'score' => $total === 0 ? 100.0 : round($healthy / $total * 100, 1),
        ];
    }
}
