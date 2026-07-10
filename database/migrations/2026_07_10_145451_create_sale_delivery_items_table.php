<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_delivery_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_delivery_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sale_item_id')->constrained()->restrictOnDelete();

            $table->decimal('quantity', 14, 4);

            // Only populated when the product tracks batch/expiry/serial —
            // same convention as purchase_receipt_items / stock_movements.
            $table->string('batch_no')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('serial_no')->nullable();

            $table->timestamps();

            $table->index('sale_delivery_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_delivery_items');
    }
};
