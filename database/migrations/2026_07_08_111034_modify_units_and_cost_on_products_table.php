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
        Schema::table('products', function (Blueprint $table) {
            // "unit_id" was a single ambiguous unit. A product now has a
            // Stock Unit (what quantity is tracked in) plus optional,
            // separately-convertible Purchase and Sale units.
            $table->renameColumn('unit_id', 'stock_unit_id');

            // cost_price implied a single authoritative cost. Real cost comes
            // from Purchase/Production transactions (future work) — this is
            // now just a starting estimate, e.g. for initial stock entry.
            $table->renameColumn('cost_price', 'estimated_cost');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('purchase_unit_id')->nullable()->after('stock_unit_id')->constrained('units')->nullOnDelete();
            $table->decimal('purchase_unit_conversion', 12, 4)->default(1)->after('purchase_unit_id');
            $table->foreignId('sale_unit_id')->nullable()->after('purchase_unit_conversion')->constrained('units')->nullOnDelete();
            $table->decimal('sale_unit_conversion', 12, 4)->default(1)->after('sale_unit_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('purchase_unit_id');
            $table->dropColumn('purchase_unit_conversion');
            $table->dropConstrainedForeignId('sale_unit_id');
            $table->dropColumn('sale_unit_conversion');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('stock_unit_id', 'unit_id');
            $table->renameColumn('estimated_cost', 'cost_price');
        });
    }
};
