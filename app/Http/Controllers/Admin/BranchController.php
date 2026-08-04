<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;

class BranchController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:branches.view', only: ['index']),
            new Middleware('permission:branches.create', only: ['create', 'store']),
            new Middleware('permission:branches.edit', only: ['edit', 'update', 'toggleStatus']),
            new Middleware('permission:branches.delete', only: ['destroy']),
        ];
    }

    public function index()
    {
        $branches = Branch::withCount('users')
            ->with(['users:id,name,email', 'users.roles:id,name'])
            ->orderBy('name')
            ->paginate(15);

        return view('admin.branches.index', compact('branches'));
    }

    public function create()
    {
        return view('admin.branches.create', ['types' => Branch::TYPES]);
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);

        $branch = Branch::create($validated);

        return redirect()->route('branches.index')->with('success', "Branch \"{$branch->name}\" created.");
    }

    public function edit(Branch $branch)
    {
        return view('admin.branches.edit', ['branch' => $branch, 'types' => Branch::TYPES]);
    }

    public function update(Request $request, Branch $branch)
    {
        $validated = $this->validated($request, $branch);

        $branch->update($validated);

        return redirect()->route('branches.index')->with('success', "Branch \"{$branch->name}\" updated.");
    }

    public function toggleStatus(Branch $branch)
    {
        $branch->update(['status' => ! $branch->status]);

        return back()->with('success', "Branch \"{$branch->name}\" is now ".($branch->status ? 'active' : 'inactive').'.');
    }

    public function destroy(Branch $branch)
    {
        if ($branch->users()->exists()) {
            return back()->with('error', "Branch \"{$branch->name}\" still has users assigned — reassign them first.");
        }

        $branch->delete();

        return redirect()->route('branches.index')->with('success', "Branch \"{$branch->name}\" deleted.");
    }

    protected function validated(Request $request, ?Branch $branch = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('branches', 'code')->ignore($branch?->id)],
            'type' => ['required', 'string', Rule::in(Branch::TYPES)],
            'address' => ['nullable', 'string', 'max:1000'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
        ]);
    }
}
