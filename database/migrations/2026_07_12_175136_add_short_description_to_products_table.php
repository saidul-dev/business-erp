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
            // A short, plain-text teaser — shown near the title on the
            // product page regardless of ecommerce_enabled, since it's
            // useful general product info, not storefront-only content.
            // `description` (the Trix rich-text field) is the long-form
            // copy that only matters once the online store is on.
            $table->string('short_description', 500)->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('short_description');
        });
    }
};
