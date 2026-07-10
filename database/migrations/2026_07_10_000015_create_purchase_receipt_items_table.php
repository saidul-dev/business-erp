<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_receipt_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_receipt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('purchase_item_id')->constrained()->restrictOnDelete();

            $table->decimal('quantity', 14, 4);

            // Only populated when the product tracks batch/expiry/serial —
            // same convention as stock_movements / stock_transfer_items.
            $table->string('batch_no')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('serial_no')->nullable();

            $table->timestamps();

            $table->index('purchase_receipt_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_receipt_items');
    }
};
