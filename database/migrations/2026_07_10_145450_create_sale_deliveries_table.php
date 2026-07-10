<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_deliveries', function (Blueprint $table) {
            $table->id();

            // restrictOnDelete, not cascade — a Sale with any delivery
            // history shouldn't be deletable at all (see SaleController).
            $table->foreignId('sale_id')->constrained()->restrictOnDelete();

            $table->string('delivery_no')->unique();
            $table->date('delivered_date');
            $table->text('note')->nullable();
            $table->foreignId('delivered_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index('sale_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_deliveries');
    }
};
