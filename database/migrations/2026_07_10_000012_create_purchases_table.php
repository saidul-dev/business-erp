<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_no')->unique();
            $table->foreignId('party_id')->constrained()->restrictOnDelete();
            $table->foreignId('site_id')->constrained()->restrictOnDelete();

            // See App\Models\Purchase::STATUSES. Only ever moves forward —
            // pending -> partial -> received, with cancelled as the only
            // other exit, same lifecycle shape as StockTransfer.
            $table->string('status')->default('pending');

            $table->date('order_date');
            $table->text('note')->nullable();

            // Full ordered value — informational. The actual Accounts
            // Payable liability accrues per-receipt (see purchase_receipts),
            // not all at once here, so this can outrun the real payable
            // while items are still pending/partially received.
            $table->decimal('total_amount', 14, 2)->default(0);

            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['site_id', 'status']);
            $table->index('party_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
