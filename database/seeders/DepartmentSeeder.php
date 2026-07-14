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
            'Human Resources',
            'Finance & Accounts',
            'Sales & Marketing',
            'Information Technology',
            'Operations',
            'Customer Support',
            'Procurement',
            'Administration',
        ];

        foreach ($departments as $name) {
            Department::firstOrCreate(['name' => $name]);
        }
    }
}
