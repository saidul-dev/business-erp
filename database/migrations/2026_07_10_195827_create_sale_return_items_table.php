<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_return_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sale_item_id')->constrained()->restrictOnDelete();

            $table->decimal('quantity', 14, 4);

            // Only populated when the product tracks batch/expiry/serial —
            // same convention as sale_delivery_items.
            $table->string('batch_no')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('serial_no')->nullable();

            $table->timestamps();

            $table->index('sale_return_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_return_items');
    }
};
