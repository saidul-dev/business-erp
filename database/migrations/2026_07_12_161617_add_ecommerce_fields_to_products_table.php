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
            // Original price before a discount — shown struck-through with a
            // "% off" badge on the storefront wherever it's set and higher
            // than selling_price. Same precision as selling_price.
            $table->decimal('compare_at_price', 14, 2)->nullable()->after('selling_price');

            // Manual curation flags for the storefront homepage sections
            // (Featured Products / Flash Sale rows) — see website/home-shop.
            $table->boolean('is_featured')->default(false)->after('compare_at_price');
            $table->boolean('is_flash_sale')->default(false)->after('is_featured');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['compare_at_price', 'is_featured', 'is_flash_sale']);
        });
    }
};
