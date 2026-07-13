<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('name');

            // Identifies the reviewer the same way guest checkout does — no
            // customer login exists, so phone is the only identity we have.
            $table->string('phone');

            $table->unsignedTinyInteger('rating');
            $table->text('comment')->nullable();

            // Snapshotted at submission time (does a 'delivered' online Sale
            // with this phone contain this product?) — not re-derived later,
            // same convention as Sale.shipping_* snapshotting the Party.
            $table->boolean('is_verified_purchase')->default(false);

            // pending -> approved/rejected, stamped via reviewed_by/reviewed_at
            // (+ rejection_reason) — identical shape to PurchaseRequisition/
            // SaleQuotation's review lifecycle.
            $table->string('status')->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->timestamps();

            $table->index(['product_id', 'status']);
            $table->unique(['product_id', 'phone']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_reviews');
    }
};
