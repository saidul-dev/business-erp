<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(?int $tenantId = null): void
    {
        $leaveTypes = [
            ['name' => 'Casual Leave', 'default_days_per_year' => 10],
            ['name' => 'Sick Leave', 'default_days_per_year' => 10],
            ['name' => 'Earned Leave', 'default_days_per_year' => 15],
            ['name' => 'Maternity Leave', 'default_days_per_year' => 90],
            ['name' => 'Paternity Leave', 'default_days_per_year' => 10],
            ['name' => 'Unpaid Leave', 'default_days_per_year' => 0],
        ];

        foreach ($leaveTypes as $leaveType) {
            LeaveType::firstOrCreate(['tenant_id' => $tenantId, 'name' => $leaveType['name']], $leaveType);
        }
    }
}
