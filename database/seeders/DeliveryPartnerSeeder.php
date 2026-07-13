<?php

namespace Database\Seeders;

use App\Models\DeliveryPartner;
use Illuminate\Database\Seeder;

class DeliveryPartnerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DeliveryPartner::firstOrCreate(
            ['code' => 'DC-001'],
            [
                'name' => 'Default Courier',
                'provider' => 'manual',
                'status' => true,
            ]
        );
    }
}
