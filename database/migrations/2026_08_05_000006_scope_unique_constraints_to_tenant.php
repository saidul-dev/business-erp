<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * These were global-unique before multi-tenancy (one restaurant assumed).
 * Two tenants must each be free to have their own "APP-001" SKU or "PAY"
 * voucher prefix, so uniqueness has to be scoped to tenant_id, not global.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique('products_sku_unique');
            $table->dropUnique('products_barcode_unique');
            $table->unique(['tenant_id', 'sku']);
            $table->unique(['tenant_id', 'barcode']);
        });

        Schema::table('attributes', function (Blueprint $table) {
            $table->dropUnique('attributes_name_unique');
            $table->unique(['tenant_id', 'name']);
        });

        Schema::table('ledger_accounts', function (Blueprint $table) {
            $table->dropUnique('ledger_accounts_code_unique');
            $table->unique(['tenant_id', 'code']);
        });

        Schema::table('ledger_transactions', function (Blueprint $table) {
            $table->dropUnique('ledger_transactions_voucher_no_unique');
            $table->unique(['tenant_id', 'voucher_no']);
        });

        Schema::table('branches', function (Blueprint $table) {
            $table->dropUnique('branches_code_unique');
            $table->unique(['tenant_id', 'code']);
        });

        // parties predates the contacts->parties rename, so the auto-named
        // index is still "contacts_phone_unique" even though the table isn't.
        Schema::table('parties', function (Blueprint $table) {
            $table->dropUnique('contacts_phone_unique');
            $table->unique(['tenant_id', 'phone']);
        });

        // employees has no tenant_id column of its own (isolation comes
        // transitively through branch_id -> Branch.tenant_id), so these
        // scope to branch_id instead — still cross-tenant safe, since two
        // different tenants never share a branch_id.
        Schema::table('employees', function (Blueprint $table) {
            $table->dropUnique('employees_phone_unique');
            $table->dropUnique('employees_email_unique');
            $table->unique(['branch_id', 'phone']);
            $table->unique(['branch_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'sku']);
            $table->dropUnique(['tenant_id', 'barcode']);
            $table->unique('sku');
            $table->unique('barcode');
        });

        Schema::table('attributes', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'name']);
            $table->unique('name');
        });

        Schema::table('ledger_accounts', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'code']);
            $table->unique('code');
        });

        Schema::table('ledger_transactions', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'voucher_no']);
            $table->unique('voucher_no');
        });

        Schema::table('branches', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'code']);
            $table->unique('code');
        });

        Schema::table('parties', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'phone']);
            $table->unique('phone');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropUnique(['branch_id', 'phone']);
            $table->dropUnique(['branch_id', 'email']);
            $table->unique('phone');
            $table->unique('email');
        });
    }
};
