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
        Schema::create('site_health_snapshots', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('overall_score');
            $table->unsignedTinyInteger('data_consistency_score');
            $table->unsignedTinyInteger('inventory_accuracy_score');
            $table->unsignedTinyInteger('financial_integrity_score');
            $table->unsignedTinyInteger('pending_backlog_score');
            // Per-category check breakdown (label/healthy/total/score for
            // each check) — lets the dropdown show *why* a score is what it
            // is, not just the number.
            $table->json('details');
            $table->timestamp('computed_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_health_snapshots');
    }
};
