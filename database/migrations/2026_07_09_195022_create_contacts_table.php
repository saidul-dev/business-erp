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
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();

            // A Contact can be a Customer, a Supplier, or both — same
            // trading party wearing two hats (e.g. buys back stock from a
            // wholesale customer). Booleans instead of a single enum so a
            // future module-scoped view can filter with a plain where()
            // and a contact can gain the other role later without
            // migrating a "type" value.
            $table->boolean('is_customer')->default(false);
            $table->boolean('is_supplier')->default(false);

            // Individual vs Company — Company name goes in `name` either
            // way; contact_person/designation are who you actually deal
            // with at that company and only make sense when is_company.
            $table->boolean('is_company')->default(false);
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('designation')->nullable();

            $table->string('phone')->unique();
            $table->string('email')->nullable();
            $table->text('address')->nullable();

            // NID suits an individual, BIN a company (VAT registration) —
            // TIN applies to either. All optional: not every contact needs
            // formal paperwork on file (e.g. a casual retail customer).
            $table->string('nid_no')->nullable();
            $table->string('bin_no')->nullable();
            $table->string('tin_no')->nullable();

            $table->decimal('credit_limit', 12, 2)->default(0);
            $table->unsignedInteger('credit_days')->default(0);

            // One-time starting figure for balances that predate this
            // system — real running balances come from Purchase/Sale
            // ledger entries once those modules post transactions.
            $table->decimal('opening_balance', 12, 2)->default(0);
            $table->enum('opening_balance_type', ['due', 'advance'])->default('due');

            $table->boolean('status')->default(true);
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['is_customer', 'status']);
            $table->index(['is_supplier', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
