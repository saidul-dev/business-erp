<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ledger_transactions', function (Blueprint $table) {
            $table->id();

            // Human-readable, auto-numbered per type — see
            // App\Models\LedgerTransaction::PREFIXES.
            $table->string('voucher_no')->unique();
            $table->date('date');

            // See App\Models\LedgerTransaction::TYPES for the fixed list.
            $table->string('type');

            // Link back to the source document (Purchase, Payment, Party
            // for an opening balance, ...) once those modules exist.
            $table->nullableMorphs('reference');

            $table->text('narration')->nullable();

            // Nullable — most transaction types (Purchase, Sale, Payment,
            // Expense) happen at a specific Site, but company-wide entries
            // (a Party's opening balance, a manual Journal correction) have
            // no single Site to attach to.
            $table->foreignId('site_id')->nullable()->constrained()->restrictOnDelete();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['site_id', 'type']);
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_transactions');
    }
};
