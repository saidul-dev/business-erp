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
        Schema::table('company_settings', function (Blueprint $table) {
            // The Site that online-store orders are placed against — set
            // from Admin > Settings > E-commerce. Nullable: until a super
            // admin picks one, checkout stays disabled (see ShopController).
            $table->foreignId('online_site_id')->nullable()->after('ecommerce_enabled')
                ->constrained('sites')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('online_site_id');
        });
    }
};
