<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('collections', function (Blueprint $table) {
            // Set when this collection was recorded against a specific
            // Project's budget from the Project page — ordinary back-office
            // Collections (settling aggregate AR, not tied to a project)
            // leave this null.
            $table->foreignId('project_id')->nullable()->after('party_id')
                ->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('collections', function (Blueprint $table) {
            $table->dropConstrainedForeignId('project_id');
        });
    }
};
