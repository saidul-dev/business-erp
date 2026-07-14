<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;

class LeaveRequestController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:hrm.view', only: ['index', 'create', 'store', 'destroy']),
            new Middleware('permission:hrm.approve', only: ['approve', 'reject']),
        ];
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $visibleEmployeeIds = Employee::visibleTo($user)->pluck('id');

        $leaveRequests = LeaveRequest::with(['employee', 'leaveType'])
            ->whereIn('employee_id', $visibleEmployeeIds)
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->orderByDesc('from_date')
            ->paginate(15)
            ->withQueryString();

        return view('admin.leave-requests.index', [
            'leaveRequests' => $leaveRequests,
            'canApprove' => $user->can('hrm.approve'),
            'currentEmployeeId' => $user->employee?->id,
        ]);
    }

    public function create()
    {
        $user = Auth::user();
        $canPickAnyEmployee = $user->can('hrm.create');

        return view('admin.leave-requests.create', [
            'employees' => $canPickAnyEmployee
                ? Employee::where('employment_status', 'active')->orderBy('name')->get()
                : collect(),
            'canPickAnyEmployee' => $canPickAnyEmployee,
            'leaveTypes' => LeaveType::where('status', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $canPickAnyEmployee = $user->can('hrm.create');

        $validated = $request->validate([
            'employee_id' => [$canPickAnyEmployee ? 'required' : 'nullable', 'integer', 'exists:employees,id'],
            'leave_type_id' => ['required', 'integer', 'exists:leave_types,id'],
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $employeeId = $canPickAnyEmployee && ! empty($validated['employee_id'])
            ? $validated['employee_id']
            : $user->employee?->id;

        abort_unless($employeeId, 403, 'No employee profile is linked to your login.');

        LeaveRequest::create([
            'employee_id' => $employeeId,
            'leave_type_id' => $validated['leave_type_id'],
            'from_date' => $validated['from_date'],
            'to_date' => $validated['to_date'],
            'reason' => $validated['reason'] ?? null,
            'created_by' => $user->id,
        ]);

        return redirect()->route('leave-requests.index')->with('success', 'Leave request submitted.');
    }

    public function approve(LeaveRequest $leaveRequest)
    {
        if ($leaveRequest->status !== 'pending') {
            return back()->with('error', 'This request has already been reviewed.');
        }

        $sufficientBalance = $leaveRequest->approve(Auth::user());

        return back()->with('success', $sufficientBalance
            ? 'Leave request approved.'
            : "Leave request approved — note this exceeds the employee's remaining balance for the year.");
    }

    public function reject(Request $request, LeaveRequest $leaveRequest)
    {
        if ($leaveRequest->status !== 'pending') {
            return back()->with('error', 'This request has already been reviewed.');
        }

        $validated = $request->validate([
            'rejection_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $leaveRequest->reject(Auth::user(), $validated['rejection_reason'] ?? null);

        return back()->with('success', 'Leave request rejected.');
    }

    public function destroy(LeaveRequest $leaveRequest)
    {
        $user = Auth::user();
        $isOwn = $leaveRequest->employee_id === $user->employee?->id;

        abort_unless($leaveRequest->status === 'pending' && ($isOwn || $user->can('hrm.edit')), 403);

        $leaveRequest->delete();

        return redirect()->route('leave-requests.index')->with('success', 'Leave request cancelled.');
    }
}
