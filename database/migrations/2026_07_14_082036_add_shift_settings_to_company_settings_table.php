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
            $table->time('default_shift_start_time')->default('09:00:00')->after('values_text');
            $table->unsignedInteger('late_grace_minutes')->default(15)->after('default_shift_start_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropColumn(['default_shift_start_time', 'late_grace_minutes']);
        });
    }
};
