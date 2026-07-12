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
            // Two static promo boxes shown beside the hero slider on the
            // e-commerce homepage — plain image uploads, no link/ordering
            // (that richer behavior lives on hero_slides instead).
            $table->string('side_banner_1_path')->nullable()->after('online_site_id');
            $table->string('side_banner_2_path')->nullable()->after('side_banner_1_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropColumn(['side_banner_1_path', 'side_banner_2_path']);
        });
    }
};
