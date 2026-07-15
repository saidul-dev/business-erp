@php $editing = isset($task); $idPrefix = $task?->id ?? 'new'; @endphp

{{-- Deliberately no old() here: this partial is reused by several
     independent modal instances (one per milestone/task) that all share
     the same field names, submitting to the same route. old() is a
     single global session value, so it can't tell which modal instance a
     failed submission belonged to — it would leak into every other
     modal's fields too. Safer to always show the real default/current
     value and let the user retype on a validation error. --}}

<div>
    <x-input-label for="task_title_{{ $idPrefix }}" :value="__('Task Title')" />
    <x-text-input id="task_title_{{ $idPrefix }}" name="title" type="text" class="mt-1 block w-full"
                  :value="$task?->title ?? ''" required autofocus />
    <x-input-error class="mt-2" :messages="$errors->get('title')" />
</div>

<div>
    <x-input-label :value="__('Assigned Employee')" />
    <x-searchable-select name="assigned_employee_id" :options="$employees" :selected="$task?->assigned_employee_id"
                          placeholder="{{ __('Select employee…') }}" />
    <x-input-error class="mt-2" :messages="$errors->get('assigned_employee_id')" />
</div>

<div class="grid grid-cols-2 gap-4">
    <div>
        <x-input-label for="task_start_date_{{ $idPrefix }}" :value="__('Start Date')" />
        <x-text-input id="task_start_date_{{ $idPrefix }}" name="start_date" type="date" class="mt-1 block w-full"
                      :value="$editing ? $task->start_date->format('Y-m-d') : ''" required />
        <x-input-error class="mt-2" :messages="$errors->get('start_date')" />
    </div>
    <div>
        <x-input-label for="task_due_date_{{ $idPrefix }}" :value="__('Due Date')" />
        <x-text-input id="task_due_date_{{ $idPrefix }}" name="due_date" type="date" class="mt-1 block w-full"
                      :value="$editing ? $task->due_date->format('Y-m-d') : ''" required />
        <x-input-error class="mt-2" :messages="$errors->get('due_date')" />
    </div>
</div>

<div class="grid grid-cols-2 gap-4">
    <div>
        <x-input-label for="task_estimated_hours_{{ $idPrefix }}" :value="__('Estimated Hours')" />
        <x-text-input id="task_estimated_hours_{{ $idPrefix }}" name="estimated_hours" type="number" step="0.5" min="0" class="mt-1 block w-full"
                      :value="$task?->estimated_hours ?? '0'" required />
        <x-input-error class="mt-2" :messages="$errors->get('estimated_hours')" />
    </div>
    <div>
        <x-input-label :value="__('Priority')" />
        <x-searchable-select name="priority" :options="$priorities" :selected="$task?->priority ?? 'medium'"
                              placeholder="{{ __('Select priority…') }}" />
        <x-input-error class="mt-2" :messages="$errors->get('priority')" />
    </div>
</div>

<div>
    <x-input-label :value="__('Status')" />
    <x-searchable-select name="status" :options="$statuses" :selected="$task?->status ?? 'todo'"
                          placeholder="{{ __('Select status…') }}" />
    <x-input-error class="mt-2" :messages="$errors->get('status')" />
</div>

<div>
    <x-input-label for="task_description_{{ $idPrefix }}" :value="__('Description (optional)')" />
    <textarea id="task_description_{{ $idPrefix }}" name="description" rows="3"
              class="mt-1 block w-full rounded-lg border-slate-300 focus:border-accent-500 focus:ring-accent-500">{{ $task?->description ?? '' }}</textarea>
    <x-input-error class="mt-2" :messages="$errors->get('description')" />
</div>
