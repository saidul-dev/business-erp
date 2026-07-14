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
        Schema::create('payroll_run_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_run_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->restrictOnDelete();
            $table->string('mode');
            $table->decimal('flat_amount', 12, 2)->nullable();
            $table->decimal('basic', 12, 2)->nullable();
            $table->decimal('house_rent', 12, 2)->nullable();
            $table->decimal('medical', 12, 2)->nullable();
            $table->decimal('conveyance', 12, 2)->nullable();
            $table->decimal('gross_amount', 12, 2);
            $table->decimal('absence_days', 4, 1)->default(0);
            $table->decimal('absence_deduction', 12, 2)->default(0);
            $table->decimal('advance_recovery', 12, 2)->default(0);
            $table->decimal('net_amount', 12, 2);
            $table->foreignId('paid_from_account_id')->nullable()->constrained('ledger_accounts')->restrictOnDelete();
            $table->foreignId('ledger_transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->unique(['payroll_run_id', 'employee_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_run_items');
    }
};
