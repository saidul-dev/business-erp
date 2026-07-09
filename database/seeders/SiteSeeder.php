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

        $manager = User::where('email', 'manager@businesserp.test')->first();

        if ($manager) {
            $manager->sites()->syncWithoutDetaching([
                $site->id => ['is_default' => true],
            ]);

            $manager->update(['current_site_id' => $site->id]);
        }
    }
}
