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
            ['code' => 'PC-001'],
            [
                'name' => 'Pathao Courier',
                'phone' => '09678460460',
                'contact_person' => 'Pathao Support',
                'status' => true,
            ]
        );
    }
}
