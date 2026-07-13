<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Data migration only — the seeded delivery partner was originally labeled
 * "Pathao Courier" but was never actually Pathao-specific. Renamed to
 * "Default Courier" as a generic placeholder — pick whichever courier(s)
 * this business actually uses via Admin > Delivery Partners (a Steadfast
 * one can be added there once API credentials are available; see
 * SteadfastService). Existing consignments keep pointing at the same row
 * (id/code unchanged), so nothing else needs to move.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('delivery_partners')
            ->where('code', 'PC-001')
            ->where('name', 'Pathao Courier')
            ->update(['name' => 'Default Courier']);
    }

    public function down(): void
    {
        DB::table('delivery_partners')
            ->where('code', 'PC-001')
            ->where('name', 'Default Courier')
            ->update(['name' => 'Pathao Courier']);
    }
};
