<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collections', function (Blueprint $table) {
            $table->id();
            $table->string('collection_no')->unique();

            // The customer paying us. Symmetric counterpart to `payments`
            // (Payment Out) — this is Payment In, settling Accounts
            // Receivable instead of Accounts Payable.
            $table->foreignId('party_id')->constrained()->restrictOnDelete();

            // Which cash_bank ledger_accounts row the money landed in.
            $table->foreignId('ledger_account_id')->constrained()->restrictOnDelete();

            $table->decimal('amount', 14, 2);
            $table->date('collection_date');

            // Cheque no. / mobile banking transaction ID / etc. — free text,
            // not a fixed "method" field, since the receiving account
            // already conveys cash vs bank.
            $table->string('reference_no')->nullable();
            $table->text('note')->nullable();

            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index('party_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collections');
    }
};
