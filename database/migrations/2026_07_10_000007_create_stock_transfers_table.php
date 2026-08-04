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
            $table->foreignId('from_branch_id')->constrained('branches')->restrictOnDelete();
            $table->foreignId('to_branch_id')->constrained('branches')->restrictOnDelete();

            // See App\Models\StockTransfer::STATUSES. in_transit (dispatched,
            // stock already left from_branch) -> received (arrived at to_branch)
            // or cancelled (reversed back at from_branch). Never mutated back
            // to in_transit once it leaves that state.
            $table->string('status')->default('in_transit');

            $table->text('note')->nullable();
            $table->date('dispatched_at');
            $table->foreignId('dispatched_by')->constrained('users')->restrictOnDelete();
            $table->date('received_at')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['from_branch_id', 'status']);
            $table->index(['to_branch_id', 'status']);
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
