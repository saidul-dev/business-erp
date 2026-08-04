<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(?int $tenantId = null): void
    {
        $units = [
            ['name' => 'Piece', 'short_name' => 'Pcs'],
            ['name' => 'Kilogram', 'short_name' => 'Kg'],
            ['name' => 'Gram', 'short_name' => 'g'],
            ['name' => 'Litre', 'short_name' => 'L'],
            ['name' => 'Millilitre', 'short_name' => 'mL'],
            ['name' => 'Plate', 'short_name' => 'Plate'],
            ['name' => 'Bottle', 'short_name' => 'Btl'],
            ['name' => 'Cup', 'short_name' => 'Cup'],
            ['name' => 'Box', 'short_name' => 'Box'],
            ['name' => 'Dozen', 'short_name' => 'Dz'],
            ['name' => 'Pack', 'short_name' => 'Pack'],
        ];

        foreach ($units as $unit) {
            Unit::firstOrCreate(['tenant_id' => $tenantId, 'short_name' => $unit['short_name']], $unit);
        }
    }
}
