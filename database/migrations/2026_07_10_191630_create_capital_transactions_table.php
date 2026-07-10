<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('capital_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_no')->unique();

            // See App\Models\CapitalTransaction::TYPES — 'investment'
            // (owner puts money in) or 'drawing' (owner takes money out).
            $table->string('type');

            // The cash_bank account the money landed in (investment) or
            // left from (drawing) — the "other side" is always one of the
            // two fixed owner-equity accounts, resolved in the controller.
            $table->foreignId('account_id')->constrained('ledger_accounts')->restrictOnDelete();

            $table->decimal('amount', 14, 2);
            $table->date('transaction_date');

            $table->string('reference_no')->nullable();
            $table->text('note')->nullable();

            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('capital_transactions');
    }
};
