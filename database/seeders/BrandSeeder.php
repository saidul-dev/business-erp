<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(?int $tenantId = null): void
    {
        $brands = ['Coca-Cola', 'Pran', 'Fresh', 'Generic'];

        foreach ($brands as $name) {
            Brand::firstOrCreate(['tenant_id' => $tenantId, 'name' => $name]);
        }
    }
}
