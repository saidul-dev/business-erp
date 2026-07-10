<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incomes', function (Blueprint $table) {
            $table->id();
            $table->string('income_no')->unique();

            // Which income_expense-group account this income is booked
            // against (Interest, Rental, Commission, ...) — see
            // IncomeController::categoryOptions() for the excluded
            // auto-posted-only account (Sales Revenue).
            $table->foreignId('category_account_id')->constrained('ledger_accounts')->restrictOnDelete();

            // Which cash_bank account the money landed in.
            $table->foreignId('received_into_account_id')->constrained('ledger_accounts')->restrictOnDelete();

            $table->decimal('amount', 14, 2);
            $table->date('income_date');

            $table->string('reference_no')->nullable();
            $table->text('note')->nullable();

            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index('category_account_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incomes');
    }
};
