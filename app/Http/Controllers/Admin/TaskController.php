<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attachment;
use App\Models\Employee;
use App\Models\Milestone;
use App\Models\Task;
use App\Models\TaskComment;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class TaskController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:tasks.view', only: ['index', 'show']),
            new Middleware('permission:tasks.create', only: ['store']),
            new Middleware('permission:tasks.edit', only: ['update', 'storeAttachment']),
            new Middleware('permission:tasks.delete', only: ['destroy']),
        ];
    }

    /**
     * Every task, project-linked or standalone — an employee assigned to
     * a task under a project they can't otherwise see (no projects.view)
     * still needs one place to find everything on their plate.
     */
    public function index(Request $request)
    {
        $tasks = Task::with(['assignedEmployee', 'project', 'milestone'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('priority'), fn ($q) => $q->where('priority', $request->priority))
            ->when($request->filled('assigned_employee_id'), fn ($q) => $q->where('assigned_employee_id', $request->assigned_employee_id))
            ->orderByDesc('due_date')
            ->paginate(15)
            ->withQueryString();

        return view('admin.tasks.index', [
            'tasks' => $tasks,
            'employees' => Employee::where('employment_status', 'active')->orderBy('name')->get(),
            'statuses' => $this->enumOptions(Task::STATUSES),
            'priorities' => $this->enumOptions(Task::PRIORITIES),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);

        $milestone = ! empty($validated['milestone_id']) ? Milestone::find($validated['milestone_id']) : null;
        $validated['project_id'] = $milestone?->project_id;
        $validated['created_by'] = Auth::id();

        $task = Task::create($validated);

        return $task->project_id
            ? redirect()->route('projects.show', $task->project_id)->with('success', "Task \"{$task->title}\" added.")
            : redirect()->route('tasks.index')->with('success', "Task \"{$task->title}\" added.");
    }

    public function update(Request $request, Task $task)
    {
        $validated = $this->validated($request);

        $milestone = ! empty($validated['milestone_id']) ? Milestone::find($validated['milestone_id']) : null;
        $validated['project_id'] = $milestone?->project_id;

        $task->update($validated);

        return back()->with('success', "Task \"{$task->title}\" updated.");
    }

    public function show(Task $task)
    {
        $task->load(['project', 'milestone', 'assignedEmployee', 'comments.user', 'attachments']);

        return view('admin.tasks.show', [
            'task' => $task,
            'employees' => Employee::where('employment_status', 'active')->orderBy('name')->get(),
            'statuses' => $this->enumOptions(Task::STATUSES),
            'priorities' => $this->enumOptions(Task::PRIORITIES),
        ]);
    }

    public function destroy(Task $task)
    {
        foreach ($task->attachments as $attachment) {
            Storage::disk('public')->delete($attachment->path);
        }

        $projectId = $task->project_id;
        $task->delete();

        return $projectId
            ? redirect()->route('projects.show', $projectId)->with('success', 'Task deleted.')
            : redirect()->route('tasks.index')->with('success', 'Task deleted.');
    }

    public function storeComment(Request $request, Task $task)
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $task->comments()->create([
            'user_id' => Auth::id(),
            'body' => $validated['body'],
        ]);

        return back()->with('success', 'Comment added.');
    }

    public function destroyComment(Task $task, TaskComment $comment)
    {
        abort_unless($comment->task_id === $task->id, 404);
        abort_unless($comment->user_id === Auth::id() || Auth::user()->can('tasks.edit'), 403);

        $comment->delete();

        return back()->with('success', 'Comment removed.');
    }

    public function storeAttachment(Request $request, Task $task)
    {
        $request->validate([
            'documents' => ['nullable', 'array'],
            'documents.*' => ['nullable', 'file', 'max:5120'],
            'document_labels' => ['nullable', 'array'],
            'document_labels.*' => ['nullable', 'string', 'max:255'],
        ]);

        if (! $request->hasFile('documents')) {
            return back()->with('error', 'Choose at least one file to upload.');
        }

        $labels = $request->input('document_labels', []);

        foreach ($request->file('documents') as $index => $file) {
            if (! $file) {
                continue;
            }

            $task->attachments()->create([
                'label' => $labels[$index] ?? $file->getClientOriginalName(),
                'path' => $file->store('tasks/attachments', 'public'),
                'original_name' => $file->getClientOriginalName(),
                'uploaded_by' => Auth::id(),
            ]);
        }

        return back()->with('success', 'Attachment uploaded.');
    }

    public function destroyAttachment(Task $task, Attachment $attachment)
    {
        abort_unless(
            $attachment->attachable_type === Task::class && $attachment->attachable_id === $task->id,
            404
        );

        Storage::disk('public')->delete($attachment->path);
        $attachment->delete();

        return back()->with('success', "Attachment \"{$attachment->label}\" removed.");
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'milestone_id' => ['nullable', 'integer', 'exists:milestones,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'assigned_employee_id' => ['required', 'integer', 'exists:employees,id'],
            'start_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:start_date'],
            'estimated_hours' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(Task::STATUSES)],
            'priority' => ['required', Rule::in(Task::PRIORITIES)],
        ]);
    }

    protected function enumOptions(array $values): array
    {
        return collect($values)
            ->map(fn ($value) => (object) ['id' => $value, 'name' => ucfirst(str_replace('_', ' ', $value))])
            ->all();
    }
}
