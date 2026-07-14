<?php

namespace Database\Seeders;

use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SiteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $site = Site::firstOrCreate(
            ['code' => 'HO-01'],
            [
                'name' => 'Head Office',
                'type' => 'Head Office',
                'address' => 'Dhaka, Bangladesh',
                'status' => true,
            ]
        );

        // Second Site — needed to test Stock Transfer (a movement always has
        // a from-Site and a to-Site, so at least two are required).
        $warehouse = Site::firstOrCreate(
            ['code' => 'WH-01'],
            [
                'name' => 'Central Warehouse',
                'type' => 'Warehouse',
                'address' => 'Gazipur, Bangladesh',
                'status' => true,
            ]
        );

        // Third Site — a retail outlet, so multi-site stock/sales demo data
        // has a storefront-style location alongside the office/warehouse.
        $shop = Site::firstOrCreate(
            ['code' => 'DS-01'],
            [
                'name' => 'Dhaka Shop',
                'type' => 'Outlet',
                'address' => 'Dhaka, Bangladesh',
                'status' => true,
            ]
        );

        $manager = User::where('email', 'manager@businesserp.test')->first();

        if ($manager) {
            $manager->sites()->syncWithoutDetaching([
                $site->id => ['is_default' => true],
                $warehouse->id => ['is_default' => false],
                $shop->id => ['is_default' => false],
            ]);

            $manager->update(['current_site_id' => $site->id]);
        }
    }
}
