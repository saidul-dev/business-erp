<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\SalaryStructure;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;

class SalaryStructureController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:hrm.edit', only: ['edit', 'update']),
        ];
    }

    public function edit(Employee $employee)
    {
        return view('admin.employees.salary', [
            'employee' => $employee,
            'structure' => $employee->salaryStructure,
        ]);
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'mode' => ['required', Rule::in(SalaryStructure::MODES)],
            'flat_amount' => ['nullable', 'numeric', 'min:0'],
            'basic' => ['nullable', 'numeric', 'min:0'],
            'house_rent' => ['nullable', 'numeric', 'min:0'],
            'medical' => ['nullable', 'numeric', 'min:0'],
            'conveyance' => ['nullable', 'numeric', 'min:0'],
            'effective_from' => ['required', 'date'],
        ]);

        $employee->salaryStructure()->updateOrCreate([], $validated);

        return redirect()->route('employees.edit', $employee)->with('success', "Salary structure saved for \"{$employee->name}\".");
    }
}
