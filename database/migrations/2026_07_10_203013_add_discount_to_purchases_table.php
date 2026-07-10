<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            // Gross value before discount, and the flat discount entered
            // on the order — `total_amount` (already existing) stays the
            // net figure (subtotal - discount), same convention as
            // sales.subtotal_amount/discount_amount/total_amount.
            $table->decimal('subtotal_amount', 14, 2)->default(0)->after('note');
            $table->decimal('discount_amount', 14, 2)->default(0)->after('subtotal_amount');
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn(['subtotal_amount', 'discount_amount']);
        });
    }
};
