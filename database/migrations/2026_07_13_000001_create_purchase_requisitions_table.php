<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_requisitions', function (Blueprint $table) {
            $table->id();
            $table->string('requisition_no')->unique();
            $table->foreignId('site_id')->constrained()->restrictOnDelete();

            // Suggested supplier only — the real supplier is picked once this
            // is converted into a Purchase Order, so this stays nullable.
            $table->foreignId('party_id')->nullable()->constrained('parties')->restrictOnDelete();

            // See App\Models\PurchaseRequisition::STATUSES. pending -> approved
            // -> converted, with rejected/cancelled as the other exits.
            $table->string('status')->default('pending');

            $table->date('request_date');
            $table->date('required_by_date')->nullable();
            $table->text('note')->nullable();

            // Sum of line estimated_unit_cost * quantity — informational
            // budget figure only, not a priced order (see purchase_requisition_items).
            $table->decimal('estimated_total_amount', 14, 2)->default(0);

            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->timestamps();

            $table->index(['site_id', 'status']);
            $table->index('party_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_requisitions');
    }
};
