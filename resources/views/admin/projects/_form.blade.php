@php $editing = isset($project); @endphp

<div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 space-y-5">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="lg:col-span-2">
            <x-input-label for="name" :value="__('Project Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                          :value="old('name', $project->name ?? '')" required autofocus />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>
        <div>
            <x-input-label :value="__('Site')" />
            <x-searchable-select name="site_id" :options="$sites" :selected="old('site_id', $project->site_id ?? auth()->user()->current_site_id)"
                                  placeholder="{{ __('Select site…') }}" />
            <x-input-error class="mt-2" :messages="$errors->get('site_id')" />
        </div>
        <div>
            <x-input-label :value="__('Project Manager')" />
            <x-searchable-select name="project_manager_id" :options="$managers" :selected="old('project_manager_id', $project->project_manager_id ?? null)"
                                  placeholder="{{ __('Select employee…') }}" />
            <x-input-error class="mt-2" :messages="$errors->get('project_manager_id')" />
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="lg:col-span-2">
            <x-input-label :value="__('Client (optional)')" />
            <x-searchable-select name="party_id" :options="$parties" :selected="old('party_id', $project->party_id ?? null)"
                                  placeholder="{{ __('Leave blank for an internal project…') }}" />
            <p class="mt-1 text-xs text-slate-400">{{ __('Leave blank for a purely internal project — pick a client for a project done for them.') }}</p>
            <x-input-error class="mt-2" :messages="$errors->get('party_id')" />
        </div>
        <div>
            <x-input-label for="due_date" :value="__('Due Date')" />
            <x-text-input id="due_date" name="due_date" type="date" class="mt-1 block w-full"
                          :value="old('due_date', $editing ? $project->due_date->format('Y-m-d') : '')" required />
            <x-input-error class="mt-2" :messages="$errors->get('due_date')" />
        </div>
        <div>
            <x-input-label for="estimated_hours" :value="__('Estimated Hours')" />
            <x-text-input id="estimated_hours" name="estimated_hours" type="number" step="0.5" min="0" class="mt-1 block w-full"
                          :value="old('estimated_hours', $project->estimated_hours ?? '0')" required />
            <x-input-error class="mt-2" :messages="$errors->get('estimated_hours')" />
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
            <x-input-label :value="__('Status')" />
            <x-searchable-select name="status" :options="$statuses" :selected="old('status', $project->status ?? 'planned')"
                                  placeholder="{{ __('Select status…') }}" />
            <x-input-error class="mt-2" :messages="$errors->get('status')" />
        </div>
    </div>

    <div>
        <x-input-label for="description" :value="__('Description (optional)')" />
        <textarea id="description" name="description" rows="4"
                  class="mt-1 block w-full rounded-lg border-slate-300 focus:border-accent-500 focus:ring-accent-500">{{ old('description', $project->description ?? '') }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('description')" />
    </div>

    <div class="flex items-center gap-3">
        <x-primary-button>{{ $editing ? __('Update Project') : __('Create Project') }}</x-primary-button>
        <a href="{{ $editing ? route('projects.show', $project) : route('projects.index') }}"
           class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50">{{ __('Cancel') }}</a>
    </div>
</div>
