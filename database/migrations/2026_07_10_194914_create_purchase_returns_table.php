<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_returns', function (Blueprint $table) {
            $table->id();

            // restrictOnDelete, not cascade — same convention as
            // purchase_receipts: a Purchase with any return history
            // shouldn't be deletable at all.
            $table->foreignId('purchase_id')->constrained()->restrictOnDelete();

            $table->string('return_no')->unique();
            $table->date('return_date');
            $table->text('note')->nullable();
            $table->foreignId('returned_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index('purchase_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_returns');
    }
};
