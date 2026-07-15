<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Milestone;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;

/**
 * Milestones have no index/show/create/edit page of their own — they're
 * managed inline on the parent Project's show page, same pattern as
 * PayrollRunItem on PayrollRun::show.
 */
class MilestoneController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:projects.edit', only: ['store', 'update', 'destroy']),
        ];
    }

    public function store(Request $request, Project $project)
    {
        $validated = $this->validated($request);

        $project->milestones()->create($validated);

        return back()->with('success', "Milestone \"{$validated['name']}\" added.");
    }

    public function update(Request $request, Project $project, Milestone $milestone)
    {
        abort_unless($milestone->project_id === $project->id, 404);

        $milestone->update($this->validated($request));

        return back()->with('success', "Milestone \"{$milestone->name}\" updated.");
    }

    public function destroy(Project $project, Milestone $milestone)
    {
        abort_unless($milestone->project_id === $project->id, 404);

        if ($milestone->tasks()->exists()) {
            return back()->with('error', "\"{$milestone->name}\" already has tasks — remove those first before deleting the milestone.");
        }

        $milestone->delete();

        return back()->with('success', "Milestone \"{$milestone->name}\" deleted.");
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::in(Milestone::STATUSES)],
            'due_date' => ['nullable', 'date'],
        ]);
    }
}
