<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->boolean('purchase_requisition_approval_required')->default(false)->after('bin_no');
            $table->boolean('sale_quotation_approval_required')->default(false)->after('purchase_requisition_approval_required');
        });
    }

    public function down(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropColumn(['purchase_requisition_approval_required', 'sale_quotation_approval_required']);
        });
    }
};
