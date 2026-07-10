<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('expense_no')->unique();

            // Which income_expense-group account this expense is booked
            // against (Rent, Salary, Utility, ...) — see
            // ExpenseController::categoryOptions() for the excluded
            // auto-posted-only accounts (Purchase Expense, COGS, Discount
            // Allowed).
            $table->foreignId('category_account_id')->constrained('ledger_accounts')->restrictOnDelete();

            // Which cash_bank account the money left from.
            $table->foreignId('paid_from_account_id')->constrained('ledger_accounts')->restrictOnDelete();

            $table->decimal('amount', 14, 2);
            $table->date('expense_date');

            // Cheque no. / mobile banking transaction ID / bill no. — free
            // text, same convention as payments.reference_no.
            $table->string('reference_no')->nullable();
            $table->text('note')->nullable();

            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index('category_account_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
