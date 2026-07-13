<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ledger_accounts', function (Blueprint $table) {
            // Distinguishes a physical cash drawer (POS shows Cash
            // Tendered/Change Due) from a bank/mobile-banking/card account
            // (POS shows a Reference/Transaction ID field instead) — a
            // cash_bank account is one or the other, decided explicitly by
            // whoever creates it rather than inferred from its name/code.
            $table->boolean('is_cash')->default(false)->after('group');
        });

        DB::table('ledger_accounts')->where('code', 'cash_in_hand')->update(['is_cash' => true]);
    }

    public function down(): void
    {
        Schema::table('ledger_accounts', function (Blueprint $table) {
            $table->dropColumn('is_cash');
        });
    }
};
