<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('site_id')->constrained()->restrictOnDelete();

            // See App\Models\StockMovement::TYPES for the fixed list of
            // movement types and their in/out direction mapping.
            $table->string('type');
            $table->enum('direction', ['in', 'out']);

            $table->decimal('quantity', 14, 4);
            $table->decimal('unit_cost', 14, 4)->nullable();

            // Only populated when the product tracks batch/expiry/serial.
            $table->string('batch_no')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('serial_no')->nullable();

            // Link back to the source document (Purchase, Sale, Transfer,
            // Adjustment, ...) once those modules exist.
            $table->nullableMorphs('reference');

            $table->text('note')->nullable();
            $table->date('moved_at');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['product_id', 'site_id']);
            $table->index(['site_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
