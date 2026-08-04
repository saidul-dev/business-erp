<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * These tables have no branch_id (they're company-wide "master data," per
 * the original single-company design) or a nullable branch_id (company-wide
 * ledger rows) — so tenant isolation can't be inherited transitively through
 * a Branch relation the way stock_movements/employees/projects can. Each
 * needs its own direct tenant_id.
 */
return new class extends Migration
{
    protected array $tables = [
        'categories',
        'brands',
        'units',
        'attributes',
        'products',
        'parties',
        'departments',
        'designations',
        'leave_types',
        'ledger_accounts',
        'ledger_transactions',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('tenant_id')->after('id')->constrained()->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropConstrainedForeignId('tenant_id');
            });
        }
    }
};
