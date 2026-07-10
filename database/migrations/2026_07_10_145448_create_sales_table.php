<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('sale_no')->unique();
            $table->foreignId('party_id')->constrained()->restrictOnDelete();
            $table->foreignId('site_id')->constrained()->restrictOnDelete();

            // See App\Models\Sale::STATUSES. Only ever moves forward —
            // pending -> partial -> delivered, with cancelled as the only
            // other exit, same lifecycle shape as Purchase.
            $table->string('status')->default('pending');

            $table->date('order_date');
            $table->text('note')->nullable();

            // Gross value before discount, the flat discount entered on the
            // order, and the net total — informational, same as Purchase's
            // total_amount. The actual Accounts Receivable/Sales Revenue
            // accrues per-delivery (see sale_deliveries), not all at once
            // here, with the discount prorated across deliveries by
            // (delivered gross / subtotal_amount) — see SaleController::deliver().
            $table->decimal('subtotal_amount', 14, 2)->default(0);
            $table->decimal('discount_amount', 14, 2)->default(0);
            $table->decimal('total_amount', 14, 2)->default(0);

            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['site_id', 'status']);
            $table->index('party_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
