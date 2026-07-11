<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('courier_consignments', function (Blueprint $table) {
            $table->id();

            // One consignment per delivery — a SaleDelivery either ships
            // itself or goes to exactly one courier, never both.
            $table->foreignId('sale_delivery_id')->unique()->constrained()->restrictOnDelete();
            $table->foreignId('delivery_partner_id')->constrained()->restrictOnDelete();

            $table->string('tracking_no')->nullable();
            $table->decimal('cod_amount', 12, 2)->default(0);
            // Filled in at COD settlement time (see CourierConsignmentController::settleCod),
            // not at booking — the courier only confirms their fee once the
            // parcel is actually delivered and remitted.
            $table->decimal('courier_fee', 12, 2)->nullable();
            $table->string('status')->default('booked');
            $table->date('cod_settled_at')->nullable();
            $table->foreignId('booked_by')->constrained('users')->restrictOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['delivery_partner_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courier_consignments');
    }
};
