<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // estimated_cost is now a perpetual weighted-average cost, recalculated
        // and stored on every costed "in" movement (see
        // StockMovement::recalculateAverageCost()). decimal(12,2) rounded that
        // average before storing it, so qty × stored-cost could drift a few
        // cents from the ledger's true total cost. Widening to 4 decimals
        // (matching stock_movements.unit_cost) makes that drift negligible.
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('estimated_cost', 12, 4)->default(0)->change();
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->decimal('estimated_cost', 12, 4)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('estimated_cost', 12, 2)->default(0)->change();
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->decimal('estimated_cost', 12, 2)->nullable()->change();
        });
    }
};
