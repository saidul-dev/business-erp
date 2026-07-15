<x-app-layout>
    <x-slot name="title">{{ $project->name }}</x-slot>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="text-2xl font-bold text-brand-900">{{ $project->name }}</h2>
                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1
                        {{ $project->status === 'completed' ? 'bg-emerald-50 text-emerald-600 ring-emerald-200' : ($project->status === 'cancelled' ? 'bg-rose-50 text-rose-600 ring-rose-200' : 'bg-amber-50 text-amber-600 ring-amber-200') }}">
                        {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                    </span>
                </div>
                <p class="text-sm text-slate-500 mt-0.5">{{ $project->party->name ?? __('Internal Project') }} · {{ $project->site->name }}</p>
            </div>
            <div class="flex items-center gap-2">
                @can('projects.edit')
                <a href="{{ route('projects.edit', $project) }}" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50">{{ __('Edit') }}</a>
                @endcan
                @can('projects.delete')
                @if ($project->milestones->isEmpty())
                <form method="POST" action="{{ route('projects.destroy', $project) }}"
                      onsubmit="return confirm('{{ __('Delete this project?') }}');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-lg px-4 py-2 text-sm font-semibold text-rose-600 ring-1 ring-rose-200 hover:bg-rose-50">{{ __('Delete') }}</button>
                </form>
                @endif
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 mb-6">
        <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Project Manager') }}</dt>
                <dd class="mt-1 font-semibold text-slate-800">{{ $project->projectManager->name }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Due Date') }}</dt>
                <dd class="mt-1 font-semibold text-slate-800">{{ $project->due_date->format('d M, Y') }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Estimated Hours') }}</dt>
                <dd class="mt-1 font-semibold text-slate-800">{{ number_format($project->estimated_hours, 1) }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Client') }}</dt>
                <dd class="mt-1 font-semibold text-slate-800">{{ $project->party->name ?? __('None — internal project') }}</dd>
            </div>
        </dl>
        @if ($project->description)
        <p class="mt-4 text-sm text-slate-600">{{ $project->description }}</p>
        @endif
    </div>

    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-bold text-brand-900">{{ __('Milestones') }}</h3>
        @can('projects.edit')
        <button type="button" @click="$dispatch('open-modal', 'milestone-create')"
                class="inline-flex items-center gap-2 rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            {{ __('Add Milestone') }}
        </button>
        @endcan
    </div>

    @forelse ($project->milestones as $milestone)
    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 overflow-hidden mb-4">
        <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <h4 class="font-bold text-slate-800">{{ $milestone->name }}</h4>
                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1
                    {{ $milestone->status === 'completed' ? 'bg-emerald-50 text-emerald-600 ring-emerald-200' : 'bg-amber-50 text-amber-600 ring-amber-200' }}">
                    {{ ucfirst(str_replace('_', ' ', $milestone->status)) }}
                </span>
                @if ($milestone->due_date)
                <span class="text-xs text-slate-400">{{ __('Due') }} {{ $milestone->due_date->format('d M, Y') }}</span>
                @endif
            </div>
            @can('projects.edit')
            <div class="flex items-center gap-2">
                <button type="button" @click="$dispatch('open-modal', 'task-create-{{ $milestone->id }}')"
                        class="rounded-lg px-2.5 py-1 text-xs font-semibold text-brand-700 ring-1 ring-brand-200 hover:bg-brand-50">{{ __('Add Task') }}</button>
                <button type="button" @click="$dispatch('open-modal', 'milestone-edit-{{ $milestone->id }}')"
                        class="rounded-lg px-2.5 py-1 text-xs font-semibold text-slate-500 ring-1 ring-slate-200 hover:bg-slate-50">{{ __('Edit') }}</button>
                @if ($milestone->tasks->isEmpty())
                <form method="POST" action="{{ route('projects.milestones.destroy', [$project, $milestone]) }}"
                      onsubmit="return confirm('{{ __('Delete this milestone?') }}');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-lg px-2.5 py-1 text-xs font-semibold text-rose-600 ring-1 ring-rose-200 hover:bg-rose-50">{{ __('Delete') }}</button>
                </form>
                @endif
            </div>
            @endcan
        </div>

        <div class="overflow-x-auto">
        <table class="w-full min-w-[760px] text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <th class="px-5 py-2.5 font-semibold">{{ __('Task') }}</th>
                    <th class="px-5 py-2.5 font-semibold">{{ __('Assignee') }}</th>
                    <th class="px-5 py-2.5 font-semibold">{{ __('Due Date') }}</th>
                    <th class="px-5 py-2.5 font-semibold">{{ __('Priority') }}</th>
                    <th class="px-5 py-2.5 font-semibold">{{ __('Status') }}</th>
                    <th class="px-5 py-2.5 font-semibold text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($milestone->tasks as $task)
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-2.5 font-semibold text-slate-800">
                        <a href="{{ route('tasks.show', $task) }}" class="hover:text-accent-700 hover:underline">{{ $task->title }}</a>
                    </td>
                    <td class="px-5 py-2.5 text-slate-600">{{ $task->assignedEmployee->name }}</td>
                    <td class="px-5 py-2.5 text-slate-600">{{ $task->due_date->format('d M, Y') }}</td>
                    <td class="px-5 py-2.5 text-slate-600">{{ ucfirst($task->priority) }}</td>
                    <td class="px-5 py-2.5">
                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1
                            {{ $task->status === 'done' ? 'bg-emerald-50 text-emerald-600 ring-emerald-200' : ($task->status === 'cancelled' ? 'bg-rose-50 text-rose-600 ring-rose-200' : 'bg-amber-50 text-amber-600 ring-amber-200') }}">
                            {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                        </span>
                    </td>
                    <td class="px-5 py-2.5 text-right">
                        <div class="flex items-center justify-end gap-2">
                            @can('tasks.edit')
                            <button type="button" @click="$dispatch('open-modal', 'task-edit-{{ $task->id }}')"
                                    class="rounded-lg px-2.5 py-1 text-xs font-semibold text-slate-500 ring-1 ring-slate-200 hover:bg-slate-50">{{ __('Edit') }}</button>
                            @endcan
                            @can('tasks.delete')
                            <form method="POST" action="{{ route('tasks.destroy', $task) }}"
                                  onsubmit="return confirm('{{ __('Delete this task?') }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-lg px-2.5 py-1 text-xs font-semibold text-rose-600 ring-1 ring-rose-200 hover:bg-rose-50">{{ __('Delete') }}</button>
                            </form>
                            @endcan
                        </div>
                    </td>
                </tr>

                <x-modal name="task-edit-{{ $task->id }}" max-width="lg">
                    <div class="p-6">
                        <h2 class="text-lg font-bold text-brand-900">{{ __('Edit Task') }}</h2>
                        <form method="POST" action="{{ route('tasks.update', $task) }}" class="mt-4 space-y-4">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="milestone_id" value="{{ $milestone->id }}">
                            @include('admin.tasks._fields', ['task' => $task, 'employees' => $employees, 'statuses' => $taskStatuses, 'priorities' => $taskPriorities])
                            <div class="flex items-center gap-3 pt-2">
                                <x-primary-button>{{ __('Save Changes') }}</x-primary-button>
                                <button type="button" x-on:click="$dispatch('close')" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50">{{ __('Cancel') }}</button>
                            </div>
                        </form>
                    </div>
                </x-modal>
                @empty
                <tr>
                    <td colspan="6" class="px-5 py-6 text-center text-slate-400">{{ __('No tasks in this milestone yet.') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    <x-modal name="milestone-edit-{{ $milestone->id }}" max-width="md">
        <div class="p-6">
            <h2 class="text-lg font-bold text-brand-900">{{ __('Edit Milestone') }}</h2>
            <form method="POST" action="{{ route('projects.milestones.update', [$project, $milestone]) }}" class="mt-4 space-y-4">
                @csrf
                @method('PUT')
                @include('admin.projects._milestone-fields', ['milestone' => $milestone])
                <div class="flex items-center gap-3 pt-2">
                    <x-primary-button>{{ __('Save Changes') }}</x-primary-button>
                    <button type="button" x-on:click="$dispatch('close')" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50">{{ __('Cancel') }}</button>
                </div>
            </form>
        </div>
    </x-modal>

    <x-modal name="task-create-{{ $milestone->id }}" max-width="lg">
        <div class="p-6">
            <h2 class="text-lg font-bold text-brand-900">{{ __('Add Task') }}</h2>
            <p class="text-xs text-slate-400 mt-0.5">{{ $milestone->name }}</p>
            <form method="POST" action="{{ route('tasks.store') }}" class="mt-4 space-y-4">
                @csrf
                <input type="hidden" name="milestone_id" value="{{ $milestone->id }}">
                @include('admin.tasks._fields', ['task' => null, 'employees' => $employees, 'statuses' => $taskStatuses, 'priorities' => $taskPriorities])
                <div class="flex items-center gap-3 pt-2">
                    <x-primary-button>{{ __('Add Task') }}</x-primary-button>
                    <button type="button" x-on:click="$dispatch('close')" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50">{{ __('Cancel') }}</button>
                </div>
            </form>
        </div>
    </x-modal>
    @empty
    <div class="rounded-2xl border border-dashed border-slate-200 py-12 text-center text-slate-400">
        {{ __('No milestones yet — add one to start planning tasks.') }}
    </div>
    @endforelse

    <x-modal name="milestone-create" max-width="md">
        <div class="p-6">
            <h2 class="text-lg font-bold text-brand-900">{{ __('Add Milestone') }}</h2>
            <form method="POST" action="{{ route('projects.milestones.store', $project) }}" class="mt-4 space-y-4">
                @csrf
                @include('admin.projects._milestone-fields', ['milestone' => null])
                <div class="flex items-center gap-3 pt-2">
                    <x-primary-button>{{ __('Add Milestone') }}</x-primary-button>
                    <button type="button" x-on:click="$dispatch('close')" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50">{{ __('Cancel') }}</button>
                </div>
            </form>
        </div>
    </x-modal>
</x-app-layout>
