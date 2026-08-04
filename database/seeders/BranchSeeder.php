<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $headOffice = Branch::firstOrCreate(
            ['code' => 'HO-01'],
            [
                'name' => 'Head Office',
                'type' => 'Head Office',
                'address' => 'Dhaka, Bangladesh',
                'status' => true,
            ]
        );

        // Second Branch — needed to test Stock Transfer (a movement always has
        // a from-Branch and a to-Branch, so at least two are required).
        $centralKitchen = Branch::firstOrCreate(
            ['code' => 'CK-01'],
            [
                'name' => 'Central Kitchen',
                'type' => 'Central Kitchen',
                'address' => 'Gazipur, Bangladesh',
                'status' => true,
            ]
        );

        // Third Branch — a dine-in outlet, so multi-branch stock/sales demo
        // data has a storefront-style location alongside the office/kitchen.
        $outlet = Branch::firstOrCreate(
            ['code' => 'DS-01'],
            [
                'name' => 'Dhaka Outlet',
                'type' => 'Outlet',
                'address' => 'Dhaka, Bangladesh',
                'status' => true,
            ]
        );

        $manager = User::where('email', 'manager@businesserp.test')->first();

        if ($manager) {
            $manager->branches()->syncWithoutDetaching([
                $headOffice->id => ['is_default' => true],
                $centralKitchen->id => ['is_default' => false],
                $outlet->id => ['is_default' => false],
            ]);

            $manager->update(['current_branch_id' => $headOffice->id]);
        }
    }
}
