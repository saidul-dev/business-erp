<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            // Optional homepage hero background photo — distinct from
            // logo_path (used in the nav/footer/print templates). Falls
            // back to the gradient hero (see website/home.blade.php) when
            // not set.
            $table->string('hero_image_path')->nullable()->after('logo_path');
        });
    }

    public function down(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropColumn('hero_image_path');
        });
    }
};
