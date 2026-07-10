<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            // Runs up from 0 to `received_quantity` across one or more
            // purchase_returns — see PurchaseItem::returnable(). Never
            // exceeds received_quantity (enforced in
            // PurchaseController::storeReturn()).
            $table->decimal('returned_quantity', 14, 4)->default(0)->after('received_quantity');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropColumn('returned_quantity');
        });
    }
};
