<?php

namespace Database\Seeders;

use App\Models\Designation;
use Illuminate\Database\Seeder;

class DesignationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(?int $tenantId = null): void
    {
        $designations = [
            'General Manager',
            'Restaurant Manager',
            'Assistant Manager',
            'Head Chef',
            'Sous Chef',
            'Line Cook',
            'Kitchen Helper',
            'Floor Captain',
            'Waiter',
            'Bartender',
            'Cashier',
            'Steward',
            'Delivery Rider',
        ];

        foreach ($designations as $name) {
            Designation::firstOrCreate(['tenant_id' => $tenantId, 'name' => $name]);
        }
    }
}
