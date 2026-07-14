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
        Schema::create('salary_structures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->unique()->constrained()->restrictOnDelete();
            $table->string('mode');
            $table->decimal('flat_amount', 12, 2)->nullable();
            $table->decimal('basic', 12, 2)->nullable();
            $table->decimal('house_rent', 12, 2)->nullable();
            $table->decimal('medical', 12, 2)->nullable();
            $table->decimal('conveyance', 12, 2)->nullable();
            $table->date('effective_from');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_structures');
    }
};
