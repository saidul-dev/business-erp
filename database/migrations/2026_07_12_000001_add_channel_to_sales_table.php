<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            // Every sale created through the admin panel is 'pos'. Once the
            // e-commerce storefront can place orders, those get 'online' —
            // same Sale/SaleItem/delivery/ledger pipeline either way, just
            // tagged by where the order came from (see Sale::CHANNELS).
            $table->string('channel')->default('pos')->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('channel');
        });
    }
};
