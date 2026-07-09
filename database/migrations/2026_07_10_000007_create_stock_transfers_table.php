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
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_no')->unique();
            $table->foreignId('from_site_id')->constrained('sites')->restrictOnDelete();
            $table->foreignId('to_site_id')->constrained('sites')->restrictOnDelete();

            // See App\Models\StockTransfer::STATUSES. in_transit (dispatched,
            // stock already left from_site) -> received (arrived at to_site)
            // or cancelled (reversed back at from_site). Never mutated back
            // to in_transit once it leaves that state.
            $table->string('status')->default('in_transit');

            $table->text('note')->nullable();
            $table->date('dispatched_at');
            $table->foreignId('dispatched_by')->constrained('users')->restrictOnDelete();
            $table->date('received_at')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['from_site_id', 'status']);
            $table->index(['to_site_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transfers');
    }
};
