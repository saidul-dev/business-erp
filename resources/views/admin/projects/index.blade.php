<x-app-layout>
    <x-slot name="title">{{ __('Projects') }}</x-slot>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-brand-900">{{ __('Projects') }}</h2>
                <p class="text-sm text-slate-500 mt-0.5">{{ __('Client and internal projects with milestones and tasks') }}</p>
            </div>
            @can('projects.create')
            <a href="{{ route('projects.create') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                {{ __('New Project') }}
            </a>
            @endcan
        </div>
    </x-slot>

    <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 mb-6">
        <form method="GET" action="{{ route('projects.index') }}" class="flex flex-wrap items-end gap-4">
            <div class="w-52">
                <x-input-label for="status" :value="__('Status')" />
                <x-searchable-select name="status" :options="$statuses" :selected="request('status')" placeholder="{{ __('All statuses') }}" auto-submit />
            </div>
            <div class="w-52">
                <x-input-label :value="__('Branch')" />
                <x-searchable-select name="branch_id" :options="$branches" :selected="request('branch_id')" placeholder="{{ __('All branches') }}" auto-submit />
            </div>
            <div class="w-52">
                <x-input-label :value="__('Client')" />
                <x-searchable-select name="party_id" :options="$parties" :selected="request('party_id')" placeholder="{{ __('All clients') }}" auto-submit />
            </div>
            <div class="w-52">
                <x-input-label :value="__('Project Manager')" />
                <x-searchable-select name="project_manager_id" :options="$managers" :selected="request('project_manager_id')" placeholder="{{ __('All managers') }}" auto-submit />
            </div>
            @if (request('status') || request('branch_id') || request('party_id') || request('project_manager_id'))
            <a href="{{ route('projects.index') }}" class="shrink-0 text-sm font-semibold text-slate-500 hover:text-slate-700">{{ __('Clear') }}</a>
            @endif
        </form>
    </div>

    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full min-w-[960px] text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <th class="px-5 py-3 font-semibold">{{ __('Project') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Client') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Manager') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Due Date') }}</th>
                    <th class="px-5 py-3 font-semibold text-right">{{ __('Est. Hours') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Status') }}</th>
                    <th class="px-5 py-3 font-semibold text-center">{{ __('Milestones') }}</th>
                    <th class="px-5 py-3 font-semibold text-center">{{ __('Tasks') }}</th>
                    <th class="px-5 py-3 font-semibold text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($projects as $project)
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-3 font-semibold text-slate-800">
                        <a href="{{ route('projects.show', $project) }}" class="hover:text-accent-700 hover:underline">{{ $project->name }}</a>
                    </td>
                    <td class="px-5 py-3 text-slate-600">{{ $project->party->name ?? __('Internal') }}</td>
                    <td class="px-5 py-3 text-slate-600">{{ $project->projectManager->name }}</td>
                    <td class="px-5 py-3 text-slate-600">{{ $project->due_date->format('d M, Y') }}</td>
                    <td class="px-5 py-3 text-right text-slate-600">{{ number_format($project->estimated_hours, 1) }}</td>
                    <td class="px-5 py-3">
                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1
                            {{ $project->status === 'completed' ? 'bg-emerald-50 text-emerald-600 ring-emerald-200' : ($project->status === 'cancelled' ? 'bg-rose-50 text-rose-600 ring-rose-200' : 'bg-amber-50 text-amber-600 ring-amber-200') }}">
                            {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-center text-slate-600">{{ $project->milestones_count }}</td>
                    <td class="px-5 py-3 text-center text-slate-600">{{ $project->tasks_count }}</td>
                    <td class="px-5 py-3">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('projects.show', $project) }}" class="rounded-lg px-2.5 py-1 text-xs font-semibold text-brand-700 ring-1 ring-brand-200 hover:bg-brand-50">{{ __('View') }}</a>
                            @can('projects.edit')
                            <a href="{{ route('projects.edit', $project) }}" class="rounded-lg px-2.5 py-1 text-xs font-semibold text-slate-500 ring-1 ring-slate-200 hover:bg-slate-50">{{ __('Edit') }}</a>
                            @endcan
                            @can('projects.delete')
                            @if ($project->milestones_count === 0)
                            <form method="POST" action="{{ route('projects.destroy', $project) }}"
                                  onsubmit="return confirm('{{ __('Delete this project?') }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-lg px-2.5 py-1 text-xs font-semibold text-rose-600 ring-1 ring-rose-200 hover:bg-rose-50">{{ __('Delete') }}</button>
                            </form>
                            @endif
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-5 py-10 text-center text-slate-400">{{ __('No projects yet — create your first project.') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>

        @if ($projects->hasPages())
        <div class="border-t border-slate-100 px-5 py-4">
            {{ $projects->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
