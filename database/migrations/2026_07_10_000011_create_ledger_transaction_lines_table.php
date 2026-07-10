<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ledger_transaction_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ledger_transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ledger_account_id')->constrained()->restrictOnDelete();

            // Set only when ledger_account_id points at the Accounts
            // Receivable/Payable control account — lets a Party's statement
            // be a plain filter over this table instead of its own
            // duplicate ledger.
            $table->foreignId('party_id')->nullable()->constrained()->restrictOnDelete();

            // Exactly one of these is non-zero per line. Balances are never
            // a stored counter — always SUM(debit) - SUM(credit), same rule
            // as stock_movements uses for quantity.
            $table->decimal('debit', 14, 2)->default(0);
            $table->decimal('credit', 14, 2)->default(0);
            $table->timestamps();

            $table->index('ledger_account_id');
            $table->index('party_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_transaction_lines');
    }
};
