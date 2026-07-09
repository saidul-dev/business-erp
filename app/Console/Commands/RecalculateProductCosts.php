<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use Illuminate\Console\Command;

class RecalculateProductCosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:recalculate-product-costs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rebuild every Product/ProductVariant estimated_cost from stock_movements history (one-off backfill for movements recorded before auto-costing shipped)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $updated = 0;

        StockMovement::where('direction', 'in')
            ->whereNotNull('unit_cost')
            ->whereNull('product_variant_id')
            ->selectRaw('product_id, SUM(quantity * unit_cost) as cost_sum, SUM(quantity) as qty_sum')
            ->groupBy('product_id')
            ->get()
            ->each(function ($row) use (&$updated) {
                if ((float) $row->qty_sum <= 0) {
                    return;
                }

                $avgCost = round((float) $row->cost_sum / (float) $row->qty_sum, 4);
                $product = Product::find($row->product_id);

                if ($product && (float) $product->estimated_cost !== $avgCost) {
                    $product->estimated_cost = $avgCost;
                    $product->saveQuietly();
                    $updated++;
                }
            });

        StockMovement::where('direction', 'in')
            ->whereNotNull('unit_cost')
            ->whereNotNull('product_variant_id')
            ->selectRaw('product_variant_id, SUM(quantity * unit_cost) as cost_sum, SUM(quantity) as qty_sum')
            ->groupBy('product_variant_id')
            ->get()
            ->each(function ($row) use (&$updated) {
                if ((float) $row->qty_sum <= 0) {
                    return;
                }

                $avgCost = round((float) $row->cost_sum / (float) $row->qty_sum, 4);
                $variant = ProductVariant::find($row->product_variant_id);

                if ($variant && (float) $variant->estimated_cost !== $avgCost) {
                    $variant->estimated_cost = $avgCost;
                    $variant->saveQuietly();
                    $updated++;
                }
            });

        $this->info("Recalculated cost for {$updated} product(s)/variant(s).");

        return self::SUCCESS;
    }
}
