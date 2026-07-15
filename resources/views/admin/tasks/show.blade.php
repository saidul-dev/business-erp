<x-app-layout>
    <x-slot name="title">{{ $task->title }}</x-slot>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="text-2xl font-bold text-brand-900">{{ $task->title }}</h2>
                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1
                        {{ $task->status === 'done' ? 'bg-emerald-50 text-emerald-600 ring-emerald-200' : ($task->status === 'cancelled' ? 'bg-rose-50 text-rose-600 ring-rose-200' : 'bg-amber-50 text-amber-600 ring-amber-200') }}">
                        {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                    </span>
                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 bg-slate-100 text-slate-600 ring-slate-200">
                        {{ ucfirst($task->priority) }}
                    </span>
                </div>
                <p class="text-sm text-slate-500 mt-0.5">
                    @if ($task->project)
                        <a href="{{ route('projects.show', $task->project) }}" class="hover:underline">{{ $task->project->name }}</a>
                        @if ($task->milestone) · {{ $task->milestone->name }} @endif
                    @else
                        {{ __('Standalone task') }}
                    @endif
                </p>
            </div>
            @can('tasks.edit')
            <button type="button" @click="$dispatch('open-modal', 'task-edit')"
                    class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50">{{ __('Edit') }}</button>
            @endcan
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Assignee') }}</dt>
                        <dd class="mt-1 font-semibold text-slate-800">{{ $task->assignedEmployee->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Estimated Hours') }}</dt>
                        <dd class="mt-1 font-semibold text-slate-800">{{ number_format($task->estimated_hours, 1) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Start Date') }}</dt>
                        <dd class="mt-1 font-semibold text-slate-800">{{ $task->start_date->format('d M, Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Due Date') }}</dt>
                        <dd class="mt-1 font-semibold text-slate-800">{{ $task->due_date->format('d M, Y') }}</dd>
                    </div>
                </dl>
                @if ($task->description)
                <p class="mt-4 text-sm text-slate-600">{{ $task->description }}</p>
                @endif
            </div>

            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <h3 class="font-bold text-brand-900">{{ __('Comments') }}</h3>

                <div class="mt-4 space-y-4">
                    @forelse ($task->comments as $comment)
                    <div class="rounded-lg bg-slate-50 p-3 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="font-semibold text-slate-800">{{ $comment->user->name ?? __('Unknown') }}</span>
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-slate-400">{{ $comment->created_at->diffForHumans() }}</span>
                                @if ($comment->user_id === auth()->id() || auth()->user()->can('tasks.edit'))
                                <form method="POST" action="{{ route('tasks.comments.destroy', [$task, $comment]) }}"
                                      onsubmit="return confirm('{{ __('Delete this comment?') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-semibold text-rose-500 hover:text-rose-700">{{ __('Delete') }}</button>
                                </form>
                                @endif
                            </div>
                        </div>
                        <p class="mt-1 text-slate-600">{{ $comment->body }}</p>
                    </div>
                    @empty
                    <p class="text-sm text-slate-400">{{ __('No comments yet.') }}</p>
                    @endforelse
                </div>

                <form method="POST" action="{{ route('tasks.comments.store', $task) }}" class="mt-4 space-y-2">
                    @csrf
                    <textarea name="body" rows="3" required placeholder="{{ __('Write a comment…') }}"
                              class="block w-full rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500"></textarea>
                    <x-input-error :messages="$errors->get('body')" />
                    <x-primary-button>{{ __('Post Comment') }}</x-primary-button>
                </form>
            </div>
        </div>

        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 h-fit">
            <h3 class="font-bold text-brand-900">{{ __('Attachments') }}</h3>

            <div class="mt-3 divide-y divide-slate-100 rounded-lg ring-1 ring-slate-200">
                @forelse ($task->attachments as $attachment)
                <div class="flex items-center justify-between px-4 py-2.5 text-sm">
                    <a href="{{ $attachment->url }}" target="_blank" class="flex items-center gap-2 text-brand-700 hover:underline">
                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                        <span>{{ $attachment->label }}</span>
                    </a>
                    @can('tasks.edit')
                    <form method="POST" action="{{ route('tasks.attachments.destroy', [$task, $attachment]) }}"
                          onsubmit="return confirm('{{ __('Remove this attachment?') }}');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="rounded-lg p-1.5 text-slate-400 hover:bg-rose-50 hover:text-rose-600">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                        </button>
                    </form>
                    @endcan
                </div>
                @empty
                <p class="px-4 py-3 text-sm text-slate-400">{{ __('No attachments yet.') }}</p>
                @endforelse
            </div>

            @can('tasks.edit')
            <form method="POST" action="{{ route('tasks.attachments.store', $task) }}" enctype="multipart/form-data" class="mt-4 space-y-2">
                @csrf
                <input type="text" name="document_labels[0]" placeholder="{{ __('Label, e.g. Design Spec') }}"
                       class="block w-full rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">
                <input type="file" name="documents[]" class="block w-full text-sm">
                <x-input-error :messages="$errors->get('documents.0')" />
                <button type="submit" class="w-full rounded-lg px-4 py-2 text-sm font-semibold text-brand-700 ring-1 ring-brand-200 hover:bg-brand-50">{{ __('Upload') }}</button>
            </form>
            @endcan
        </div>
    </div>

    @can('tasks.edit')
    <x-modal name="task-edit" max-width="lg" :show="$errors->any()">
        <div class="p-6">
            <h2 class="text-lg font-bold text-brand-900">{{ __('Edit Task') }}</h2>
            <form method="POST" action="{{ route('tasks.update', $task) }}" class="mt-4 space-y-4">
                @csrf
                @method('PUT')
                @include('admin.tasks._fields', ['milestones' => $milestones])
                <div class="flex items-center gap-3 pt-2">
                    <x-primary-button>{{ __('Save Changes') }}</x-primary-button>
                    <button type="button" x-on:click="$dispatch('close')" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50">{{ __('Cancel') }}</button>
                </div>
            </form>
        </div>
    </x-modal>
    @endcan
</x-app-layout>
