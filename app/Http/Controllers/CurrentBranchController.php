<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;

class CurrentBranchController extends Controller
{
    /**
     * Forced picker shown when a user has more than one assigned branch and
     * hasn't chosen a Current Branch yet (see SetCurrentBranch middleware).
     */
    public function select()
    {
        $user = auth()->user();

        $branches = $user->seesAllBranches()
            ? Branch::where('status', true)->orderBy('name')->get()
            : $user->branches()->where('status', true)->orderBy('name')->get();

        return view('admin.select-branch', compact('branches'));
    }

    /**
     * Switch the Current Branch — used by both the forced picker and the
     * topbar Branch Selector. Only Admin/Super Admin may pick "All Branches" (null).
     */
    public function switch(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'branch_id' => ['nullable', 'exists:branches,id'],
        ]);

        $branchId = $validated['branch_id'] ?? null;

        if ($branchId === null && ! $user->seesAllBranches()) {
            abort(403, 'You must select a specific branch.');
        }

        if ($branchId !== null && ! $user->seesAllBranches() && ! $user->branches()->where('branches.id', $branchId)->exists()) {
            abort(403, 'You are not assigned to that branch.');
        }

        $user->update(['current_branch_id' => $branchId]);
        session(['current_branch_id' => $branchId]);

        $message = $branchId ? 'Switched to '.Branch::find($branchId)->name.'.' : 'Now viewing All Branches.';

        return redirect()->intended(route('dashboard'))->with('success', $message);
    }
}
