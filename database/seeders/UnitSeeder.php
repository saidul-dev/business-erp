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
    public function run(): void
    {
        $units = [
            ['name' => 'Piece', 'short_name' => 'Pcs'],
            ['name' => 'Kilogram', 'short_name' => 'Kg'],
            ['name' => 'Gram', 'short_name' => 'g'],
            ['name' => 'Litre', 'short_name' => 'L'],
            ['name' => 'Millilitre', 'short_name' => 'mL'],
            ['name' => 'Box', 'short_name' => 'Box'],
            ['name' => 'Dozen', 'short_name' => 'Dz'],
            ['name' => 'Meter', 'short_name' => 'm'],
            ['name' => 'Pack', 'short_name' => 'Pack'],
            ['name' => 'Set', 'short_name' => 'Set'],
        ];

        foreach ($units as $unit) {
            Unit::firstOrCreate(['short_name' => $unit['short_name']], $unit);
        }
    }
}
