<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            // Public-website copy — distinct from `name`/`legal_name`
            // (used on invoices/reports), these are only ever shown on the
            // public homepage (see WebsiteController).
            $table->string('tagline')->nullable()->after('legal_name');
            $table->text('about_text')->nullable()->after('tagline');
        });
    }

    public function down(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropColumn(['tagline', 'about_text']);
        });
    }
};
