<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fund_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_no')->unique();

            // Both cash_bank-group ledger_accounts — money moving between
            // the company's own accounts, not to/from a party or category.
            $table->foreignId('from_account_id')->constrained('ledger_accounts')->restrictOnDelete();
            $table->foreignId('to_account_id')->constrained('ledger_accounts')->restrictOnDelete();

            $table->decimal('amount', 14, 2);
            $table->date('transfer_date');

            $table->string('reference_no')->nullable();
            $table->text('note')->nullable();

            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index('from_account_id');
            $table->index('to_account_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fund_transfers');
    }
};
