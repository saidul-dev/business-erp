<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('collections', function (Blueprint $table) {
            // Set when this collection was recorded as part of a POS checkout —
            // ordinary back-office Collections (settling aggregate AR, not a
            // specific invoice) leave this null.
            $table->foreignId('sale_id')->nullable()->after('party_id')
                ->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('collections', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sale_id');
        });
    }
};
