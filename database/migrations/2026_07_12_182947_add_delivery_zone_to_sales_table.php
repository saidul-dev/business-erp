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
        Schema::table('sales', function (Blueprint $table) {
            // Snapshot of the DeliveryZone picked at checkout — name+charge
            // copied at order time (not a foreign key) so it stays accurate
            // even if the zone's price changes or is deleted later.
            // Purely informational: courier COD reference for the admin,
            // never added to subtotal_amount/total_amount or the ledger —
            // this store doesn't charge for delivery.
            $table->string('delivery_zone_name')->nullable()->after('shipping_address');
            $table->decimal('delivery_charge', 10, 2)->nullable()->after('delivery_zone_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['delivery_zone_name', 'delivery_charge']);
        });
    }
};
