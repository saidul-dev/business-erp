<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_receipts', function (Blueprint $table) {
            // Freight/loading/etc. for this specific shipment — unlike the
            // order's discount, these are only known once goods actually
            // arrive, and can differ receipt to receipt on the same order.
            $table->decimal('delivery_charge', 14, 2)->default(0)->after('note');
            $table->decimal('other_charge', 14, 2)->default(0)->after('delivery_charge');

            // Where the combined delivery+other charge is credited: the
            // same supplier's payable, or a specific cash/bank account.
            // charge_account_id is only set (and only meaningful) for
            // 'cash_bank' — see PurchaseController::receive().
            $table->string('charge_paid_via')->default('supplier')->after('other_charge');
            $table->foreignId('charge_account_id')->nullable()->after('charge_paid_via')
                ->constrained('ledger_accounts')->nullOnDelete();
        });

        Schema::table('purchase_receipt_items', function (Blueprint $table) {
            // The actual cost posted to stock for this line on this
            // receipt (discount- and landed-cost-adjusted) — mirrors
            // stock_movements.unit_cost. Needed because landed cost is
            // per-receipt, so a return spanning multiple receipts can't
            // recompute it from the order alone (see
            // PurchaseController::storeReturn()).
            $table->decimal('unit_cost', 14, 4)->default(0)->after('quantity');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_receipt_items', function (Blueprint $table) {
            $table->dropColumn('unit_cost');
        });

        Schema::table('purchase_receipts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('charge_account_id');
            $table->dropColumn(['delivery_charge', 'other_charge', 'charge_paid_via']);
        });
    }
};
