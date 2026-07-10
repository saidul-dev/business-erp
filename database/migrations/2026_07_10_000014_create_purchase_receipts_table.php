<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_receipts', function (Blueprint $table) {
            $table->id();

            // restrictOnDelete, not cascade — a Purchase with any receipt
            // history shouldn't be deletable at all (see PurchaseController).
            $table->foreignId('purchase_id')->constrained()->restrictOnDelete();

            $table->string('receipt_no')->unique();
            $table->date('received_date');
            $table->text('note')->nullable();
            $table->foreignId('received_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index('purchase_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_receipts');
    }
};
