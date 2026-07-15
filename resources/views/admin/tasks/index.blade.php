<x-app-layout>
    <x-slot name="title">{{ __('Tasks') }}</x-slot>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-brand-900">{{ __('Tasks') }}</h2>
                <p class="text-sm text-slate-500 mt-0.5">{{ __('Every task assigned to you or your team, project-linked or standalone') }}</p>
            </div>
            @can('tasks.create')
            <button type="button" @click="$dispatch('open-modal', 'task-create')"
                    class="inline-flex items-center gap-2 rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                {{ __('Add Task') }}
            </button>
            @endcan
        </div>
    </x-slot>

    <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 mb-6">
        <form method="GET" action="{{ route('tasks.index') }}" class="flex flex-wrap items-end gap-4">
            <div class="w-52">
                <x-input-label :value="__('Status')" />
                <x-searchable-select name="status" :options="$statuses" :selected="request('status')" placeholder="{{ __('All statuses') }}" auto-submit />
            </div>
            <div class="w-52">
                <x-input-label :value="__('Priority')" />
                <x-searchable-select name="priority" :options="$priorities" :selected="request('priority')" placeholder="{{ __('All priorities') }}" auto-submit />
            </div>
            <div class="w-52">
                <x-input-label :value="__('Assignee')" />
                <x-searchable-select name="assigned_employee_id" :options="$employees" :selected="request('assigned_employee_id')" placeholder="{{ __('All employees') }}" auto-submit />
            </div>
            @if (request('status') || request('priority') || request('assigned_employee_id'))
            <a href="{{ route('tasks.index') }}" class="shrink-0 text-sm font-semibold text-slate-500 hover:text-slate-700">{{ __('Clear') }}</a>
            @endif
        </form>
    </div>

    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full min-w-[800px] text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <th class="px-5 py-3 font-semibold">{{ __('Task') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Project') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Assignee') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Due Date') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Priority') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Status') }}</th>
                    <th class="px-5 py-3 font-semibold text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($tasks as $task)
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-3 font-semibold text-slate-800">
                        <a href="{{ route('tasks.show', $task) }}" class="hover:text-accent-700 hover:underline">{{ $task->title }}</a>
                    </td>
                    <td class="px-5 py-3 text-slate-600">
                        @if ($task->project)
                            <a href="{{ route('projects.show', $task->project) }}" class="hover:underline">{{ $task->project->name }}</a>
                            @if ($task->milestone) <span class="text-xs text-slate-400">· {{ $task->milestone->name }}</span> @endif
                        @else
                            <span class="text-slate-400">{{ __('Standalone') }}</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-slate-600">{{ $task->assignedEmployee->name }}</td>
                    <td class="px-5 py-3 text-slate-600">{{ $task->due_date->format('d M, Y') }}</td>
                    <td class="px-5 py-3 text-slate-600">{{ ucfirst($task->priority) }}</td>
                    <td class="px-5 py-3">
                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1
                            {{ $task->status === 'done' ? 'bg-emerald-50 text-emerald-600 ring-emerald-200' : ($task->status === 'cancelled' ? 'bg-rose-50 text-rose-600 ring-rose-200' : 'bg-amber-50 text-amber-600 ring-amber-200') }}">
                            {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('tasks.show', $task) }}" class="rounded-lg px-2.5 py-1 text-xs font-semibold text-brand-700 ring-1 ring-brand-200 hover:bg-brand-50">{{ __('View') }}</a>
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
                @empty
                <tr>
                    <td colspan="7" class="px-5 py-10 text-center text-slate-400">{{ __('No tasks yet.') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>

        @if ($tasks->hasPages())
        <div class="border-t border-slate-100 px-5 py-4">
            {{ $tasks->links() }}
        </div>
        @endif
    </div>

    @can('tasks.create')
    <x-modal name="task-create" max-width="lg">
        <div class="p-6">
            <h2 class="text-lg font-bold text-brand-900">{{ __('Add Task') }}</h2>
            <form method="POST" action="{{ route('tasks.store') }}" class="mt-4 space-y-4">
                @csrf
                @include('admin.tasks._fields', ['task' => null])
                <div class="flex items-center gap-3 pt-2">
                    <x-primary-button>{{ __('Add Task') }}</x-primary-button>
                    <button type="button" x-on:click="$dispatch('close')" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50">{{ __('Cancel') }}</button>
                </div>
            </form>
        </div>
    </x-modal>
    @endcan
</x-app-layout>
