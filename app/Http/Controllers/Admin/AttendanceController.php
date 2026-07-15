<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\CompanySetting;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AttendanceController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:hrm.view', only: ['summary']),
            new Middleware('permission:hrm.edit', only: ['register', 'saveRegister']),
        ];
    }

    public function register(Request $request)
    {
        $date = $request->filled('date') ? Carbon::parse($request->date('date')) : today();

        $employees = Employee::visibleTo(Auth::user())
            ->where('employment_status', 'active')
            ->with([
                'department',
                'attendanceLogs' => fn ($q) => $q->whereDate('date', $date),
                'leaveRequests' => fn ($q) => $q->where('status', 'approved')
                    ->whereDate('from_date', '<=', $date)
                    ->whereDate('to_date', '>=', $date),
            ])
            ->orderBy('name')
            ->get();

        return view('admin.attendance.register', [
            'date' => $date,
            'employees' => $employees,
        ]);
    }

    public function saveRegister(Request $request)
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'records' => ['required', 'array'],
            'records.*.employee_id' => ['required', 'integer', 'exists:employees,id'],
            'records.*.status' => ['required', Rule::in(AttendanceLog::STATUSES)],
            'records.*.notes' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['records'] as $record) {
                AttendanceLog::updateOrCreate(
                    ['employee_id' => $record['employee_id'], 'date' => $validated['date']],
                    [
                        'status' => $record['status'],
                        'notes' => $record['notes'] ?? null,
                        'source' => 'manual',
                        'marked_by' => Auth::id(),
                    ]
                );
            }
        });

        return redirect()->route('attendance.register', ['date' => $validated['date']])
            ->with('success', 'Attendance saved for '.Carbon::parse($validated['date'])->format('d M, Y').'.');
    }

    public function summary(Request $request)
    {
        $month = $request->filled('month') ? $request->integer('month') : now()->month;
        $year = $request->filled('year') ? $request->integer('year') : now()->year;

        $employees = Employee::visibleTo(Auth::user())
            ->when($request->filled('department_id'), fn ($q) => $q->where('department_id', $request->department_id))
            ->with(['department', 'attendanceLogs' => fn ($q) => $q->whereMonth('date', $month)->whereYear('date', $year)])
            ->orderBy('name')
            ->get()
            ->map(function (Employee $employee) {
                $counts = $employee->attendanceLogs->countBy('status');

                return [
                    'employee' => $employee,
                    'present' => $counts->get('present', 0),
                    'absent' => $counts->get('absent', 0),
                    'half_day' => $counts->get('half_day', 0),
                    'leave' => $counts->get('leave', 0),
                ];
            });

        return view('admin.attendance.summary', [
            'rows' => $employees,
            'month' => $month,
            'year' => $year,
            'departments' => Department::orderBy('name')->get(),
        ]);
    }

    public function my()
    {
        $employee = Auth::user()->employee;
        abort_unless($employee, 403, "No employee profile is linked to your login.");

        $today = today();
        $todayLog = AttendanceLog::where('employee_id', $employee->id)->whereDate('date', $today)->first();

        $recentLogs = AttendanceLog::where('employee_id', $employee->id)
            ->orderByDesc('date')
            ->limit(14)
            ->get();

        return view('admin.attendance.my', compact('employee', 'todayLog', 'recentLogs'));
    }

    public function checkIn()
    {
        $employee = Auth::user()->employee;
        abort_unless($employee, 403, "No employee profile is linked to your login.");

        $today = today();

        $onApprovedLeave = LeaveRequest::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->whereDate('from_date', '<=', $today)
            ->whereDate('to_date', '>=', $today)
            ->exists();

        if ($onApprovedLeave) {
            return back()->with('error', 'You have an approved leave today — check-in is disabled.');
        }

        $log = AttendanceLog::firstOrNew(['employee_id' => $employee->id, 'date' => $today]);

        if ($log->exists && $log->check_in_at) {
            return back()->with('error', 'You have already checked in today.');
        }

        $settings = CompanySetting::current();
        $shiftThreshold = Carbon::parse($today->format('Y-m-d').' '.$settings->default_shift_start_time)
            ->addMinutes($settings->late_grace_minutes);

        $log->fill([
            'status' => 'present',
            'check_in_at' => now(),
            'is_late' => now()->gt($shiftThreshold),
            'source' => 'self',
        ])->save();

        return back()->with('success', $log->is_late ? 'Checked in — marked late.' : 'Checked in.');
    }

    public function checkOut()
    {
        $employee = Auth::user()->employee;
        abort_unless($employee, 403, "No employee profile is linked to your login.");

        $log = AttendanceLog::where('employee_id', $employee->id)->whereDate('date', today())->first();

        if (! $log || ! $log->check_in_at) {
            return back()->with('error', "You haven't checked in today yet.");
        }

        if ($log->check_out_at) {
            return back()->with('error', 'You have already checked out today.');
        }

        $log->update(['check_out_at' => now()]);

        return back()->with('success', 'Checked out.');
    }
}
