<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_quotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_quotation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->restrictOnDelete();

            $table->decimal('quantity', 14, 4);
            $table->decimal('unit_price', 14, 4);
            $table->decimal('subtotal', 14, 2);

            $table->timestamps();

            $table->index('sale_quotation_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_quotation_items');
    }
};
