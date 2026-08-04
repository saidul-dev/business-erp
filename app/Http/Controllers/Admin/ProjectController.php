<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\LedgerAccount;
use App\Models\Milestone;
use App\Models\Party;
use App\Models\Project;
use App\Models\Branch;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProjectController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:projects.view', only: ['index', 'show']),
            new Middleware('permission:projects.create', only: ['create', 'store']),
            new Middleware('permission:projects.edit', only: ['edit', 'update']),
            new Middleware('permission:projects.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $projects = Project::with(['branch', 'party', 'projectManager'])
            ->withCount(['milestones', 'tasks'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('branch_id'), fn ($q) => $q->where('branch_id', $request->branch_id))
            ->when($request->filled('party_id'), fn ($q) => $q->where('party_id', $request->party_id))
            ->when($request->filled('project_manager_id'), fn ($q) => $q->where('project_manager_id', $request->project_manager_id))
            ->orderByDesc('due_date')
            ->paginate(15)
            ->withQueryString();

        return view('admin.projects.index', [
            'projects' => $projects,
            'branches' => Branch::orderBy('name')->get(),
            'parties' => Party::orderBy('name')->get(),
            'managers' => Employee::where('employment_status', 'active')->orderBy('name')->get(),
            'statuses' => $this->statusOptions(),
        ]);
    }

    public function create()
    {
        return view('admin.projects.create', $this->formData());
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);
        $validated['created_by'] = Auth::id();

        $project = Project::create($validated);

        return redirect()->route('projects.show', $project)->with('success', "Project \"{$project->name}\" created.");
    }

    public function edit(Project $project)
    {
        return view('admin.projects.edit', $this->formData($project) + ['project' => $project]);
    }

    public function update(Request $request, Project $project)
    {
        $project->update($this->validated($request));

        return redirect()->route('projects.show', $project)->with('success', "Project \"{$project->name}\" updated.");
    }

    public function show(Project $project)
    {
        $project->load(['branch', 'party', 'projectManager', 'milestones.tasks.assignedEmployee', 'collections.account']);

        return view('admin.projects.show', [
            'project' => $project,
            'employees' => Employee::where('employment_status', 'active')->orderBy('name')->get(),
            'cashAccounts' => LedgerAccount::where('group', 'cash_bank')->where('status', true)->orderBy('name')->get(),
            'milestoneStatuses' => $this->enumOptions(Milestone::STATUSES),
            'taskStatuses' => $this->enumOptions(Task::STATUSES),
            'taskPriorities' => $this->enumOptions(Task::PRIORITIES),
        ]);
    }

    public function destroy(Project $project)
    {
        if ($project->milestones()->exists()) {
            return back()->with('error', "\"{$project->name}\" already has milestones — delete those first before removing the project.");
        }

        if ($project->collections()->exists()) {
            return back()->with('error', "\"{$project->name}\" has collections recorded against it — these financial records must stay linked to the project.");
        }

        foreach ($project->tasks as $task) {
            foreach ($task->attachments as $attachment) {
                Storage::disk('public')->delete($attachment->path);
            }
        }

        $project->delete();

        return redirect()->route('projects.index')->with('success', "Project \"{$project->name}\" deleted.");
    }

    protected function formData(?Project $project = null): array
    {
        return [
            'branches' => Branch::orderBy('name')->get(),
            'parties' => Party::orderBy('name')->get(),
            'managers' => Employee::where('employment_status', 'active')->orderBy('name')->get(),
            'statuses' => $this->statusOptions(),
        ];
    }

    protected function validated(Request $request): array
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'party_id' => ['nullable', 'integer', 'exists:parties,id'],
            'project_manager_id' => ['required', 'integer', 'exists:employees,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::in(Project::STATUSES)],
            'due_date' => ['required', 'date'],
            'estimated_hours' => ['required', 'numeric', 'min:0'],
            'budget_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        // A budget only makes sense against a client — an internal project
        // has nobody to collect payment from.
        if (empty($validated['party_id'])) {
            $validated['budget_amount'] = null;
        }

        return $validated;
    }

    protected function statusOptions(): array
    {
        return $this->enumOptions(Project::STATUSES);
    }

    /**
     * Turns a plain STATUSES/PRIORITIES const array into id+name objects so
     * it can feed <x-searchable-select> the same way an Eloquent collection
     * does — this app's standing rule is every dropdown gets search, not
     * just the long ones.
     */
    protected function enumOptions(array $values): array
    {
        return collect($values)
            ->map(fn ($value) => (object) ['id' => $value, 'name' => ucfirst(str_replace('_', ' ', $value))])
            ->all();
    }
}
