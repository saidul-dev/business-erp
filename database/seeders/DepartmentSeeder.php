<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            'Kitchen',
            'Service (Floor)',
            'Bar & Beverage',
            'Procurement & Store',
            'Finance & Accounts',
            'Human Resources',
            'Management',
        ];

        foreach ($departments as $name) {
            Department::firstOrCreate(['name' => $name]);
        }
    }
}
