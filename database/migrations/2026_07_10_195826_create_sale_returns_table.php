<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_returns', function (Blueprint $table) {
            $table->id();

            // restrictOnDelete, not cascade — same convention as
            // sale_deliveries: a Sale with any return history shouldn't
            // be deletable at all.
            $table->foreignId('sale_id')->constrained()->restrictOnDelete();

            $table->string('return_no')->unique();
            $table->date('return_date');
            $table->text('note')->nullable();
            $table->foreignId('returned_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index('sale_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_returns');
    }
};
