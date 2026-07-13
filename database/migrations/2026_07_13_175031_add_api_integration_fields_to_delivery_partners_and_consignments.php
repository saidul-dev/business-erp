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
        Schema::table('delivery_partners', function (Blueprint $table) {
            $table->string('provider')->default('manual')->after('code');
            $table->text('api_key')->nullable()->after('provider');
            $table->text('secret_key')->nullable()->after('api_key');
        });

        Schema::table('courier_consignments', function (Blueprint $table) {
            $table->string('external_id')->nullable()->after('tracking_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_partners', function (Blueprint $table) {
            $table->dropColumn(['provider', 'api_key', 'secret_key']);
        });

        Schema::table('courier_consignments', function (Blueprint $table) {
            $table->dropColumn('external_id');
        });
    }
};
