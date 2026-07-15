@php $editing = isset($milestone); $idPrefix = $milestone?->id ?? 'new'; @endphp

{{-- Deliberately no old() here: this partial is reused by several
     independent modal instances (one per milestone) that all share the
     same field names, submitting to the same route. old() is a single
     global session value, so it can't tell which modal instance a failed
     submission belonged to — it would leak into every other milestone's
     "Add"/"Edit" fields too. Safer to always show the real default/
     current value and let the user retype on a validation error. --}}

<div>
    <x-input-label for="milestone_name_{{ $idPrefix }}" :value="__('Milestone Name')" />
    <x-text-input id="milestone_name_{{ $idPrefix }}" name="name" type="text" class="mt-1 block w-full"
                  :value="$milestone?->name ?? ''" required autofocus />
    <x-input-error class="mt-2" :messages="$errors->get('name')" />
</div>

<div class="grid grid-cols-2 gap-4">
    <div>
        <x-input-label :value="__('Status')" />
        <x-searchable-select name="status" :options="$milestoneStatuses" :selected="$milestone?->status ?? 'pending'"
                              placeholder="{{ __('Select status…') }}" />
        <x-input-error class="mt-2" :messages="$errors->get('status')" />
    </div>
    <div>
        <x-input-label for="milestone_due_date_{{ $idPrefix }}" :value="__('Due Date (optional)')" />
        <x-text-input id="milestone_due_date_{{ $idPrefix }}" name="due_date" type="date" class="mt-1 block w-full"
                      :value="$editing && $milestone->due_date ? $milestone->due_date->format('Y-m-d') : ''" />
        <x-input-error class="mt-2" :messages="$errors->get('due_date')" />
    </div>
</div>

<div>
    <x-input-label for="milestone_description_{{ $idPrefix }}" :value="__('Description (optional)')" />
    <textarea id="milestone_description_{{ $idPrefix }}" name="description" rows="3"
              class="mt-1 block w-full rounded-lg border-slate-300 focus:border-accent-500 focus:ring-accent-500">{{ $milestone?->description ?? '' }}</textarea>
    <x-input-error class="mt-2" :messages="$errors->get('description')" />
</div>
