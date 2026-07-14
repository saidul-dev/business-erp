<?php

namespace Database\Seeders;

use App\Models\Designation;
use Illuminate\Database\Seeder;

class DesignationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $designations = [
            'Director',
            'Manager',
            'Assistant Manager',
            'Team Lead',
            'Senior Executive',
            'Executive',
            'Officer',
            'Intern',
        ];

        foreach ($designations as $name) {
            Designation::firstOrCreate(['name' => $name]);
        }
    }
}
