<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            // Runs up from 0 to `delivered_quantity` across one or more
            // sale_returns — see SaleItem::returnable(). Never exceeds
            // delivered_quantity (enforced in SaleController::storeReturn()).
            $table->decimal('returned_quantity', 14, 4)->default(0)->after('delivered_quantity');
        });
    }

    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn('returned_quantity');
        });
    }
};
