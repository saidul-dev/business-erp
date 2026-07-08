<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('has_variants')->default(false)->after('status');
            $table->boolean('track_batch')->default(false)->after('has_variants');
            $table->boolean('track_expiry')->default(false)->after('track_batch');
            $table->boolean('track_serial')->default(false)->after('track_expiry');
            $table->unsignedInteger('warranty_period')->nullable()->after('track_serial');
            $table->string('warranty_unit')->nullable()->after('warranty_period');
            $table->unsignedInteger('guarantee_period')->nullable()->after('warranty_unit');
            $table->string('guarantee_unit')->nullable()->after('guarantee_period');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'has_variants', 'track_batch', 'track_expiry', 'track_serial',
                'warranty_period', 'warranty_unit', 'guarantee_period', 'guarantee_unit',
            ]);
        });
    }
};
