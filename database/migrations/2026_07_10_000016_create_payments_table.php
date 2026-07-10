<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_no')->unique();

            // The supplier being paid. Payment Out only for now — a
            // symmetric Payment In / Collection screen for customer
            // receivables is a natural follow-up, not built yet.
            $table->foreignId('party_id')->constrained()->restrictOnDelete();

            // Which cash_bank ledger_accounts row the money left from.
            $table->foreignId('ledger_account_id')->constrained()->restrictOnDelete();

            $table->decimal('amount', 14, 2);
            $table->date('payment_date');

            // Cheque no. / mobile banking transaction ID / etc. — free text,
            // not a fixed "method" field, since the paying account already
            // conveys cash vs bank.
            $table->string('reference_no')->nullable();
            $table->text('note')->nullable();

            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index('party_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
