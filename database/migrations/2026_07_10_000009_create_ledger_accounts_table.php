<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ledger_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');

            // Stable machine key for system accounts (e.g. "accounts_payable")
            // so LedgerService can resolve them without matching on the
            // human-editable name. Nullable — user-added Bank accounts don't
            // need one.
            $table->string('code')->nullable()->unique();

            // See App\Models\LedgerAccount::GROUPS for the fixed functional
            // groups (Cash & Bank / Party / Inventory / Income & Expense /
            // Equity & Adjustment).
            $table->string('group');

            // Normal balance side — Asset/Expense accounts are debit-normal,
            // Liability/Equity/Income are credit-normal. Stored explicitly
            // per account (not derived from group) since e.g. the "party"
            // group holds both Accounts Receivable (debit) and Accounts
            // Payable (credit).
            $table->enum('nature', ['debit', 'credit']);

            // Protects the auto-seeded control accounts (Accounts
            // Payable/Receivable, Inventory, Opening Balance Equity, ...)
            // from deletion or group/nature changes.
            $table->boolean('is_system')->default(false);

            // Only meaningful for the cash_bank group — a branch's own cash
            // drawer or bank account. Null = company-wide. Every other group
            // is always company-wide (enforced in LedgerAccount::booted()).
            $table->foreignId('branch_id')->nullable()->constrained()->restrictOnDelete();

            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->index(['group', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_accounts');
    }
};
