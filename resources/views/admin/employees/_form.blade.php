@php $editing = isset($employee); @endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4" x-data="{ preview: @js($employee->photo_url ?? null), removePhoto: false, docCount: 1 }">
    <!-- Photo & Login -->
    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <div class="mb-4">
            <h3 class="font-bold text-brand-900">{{ __('Photo') }}</h3>
            <p class="text-xs text-slate-400">{{ __('PNG or JPG, up to 2MB') }}</p>
        </div>

        <div class="flex flex-col items-center gap-4">
            <div class="grid h-28 w-28 place-items-center overflow-hidden rounded-full bg-slate-100 ring-1 ring-slate-200">
                <template x-if="preview">
                    <img :src="preview" alt="Photo preview" class="h-full w-full object-cover">
                </template>
                <template x-if="!preview">
                    <svg class="h-10 w-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                </template>
            </div>

            <label class="cursor-pointer rounded-lg bg-brand-800/5 px-4 py-2 text-xs font-semibold text-brand-800 hover:bg-brand-800/10">
                {{ __('Choose Image') }}
                <input type="file" name="photo" accept="image/*" class="hidden"
                       @change="removePhoto = false; const f = $event.target.files[0]; if (f) preview = URL.createObjectURL(f)">
            </label>

            @if ($editing && $employee->photo_url)
            <label class="flex items-center gap-2 text-xs text-slate-500">
                <input type="checkbox" name="remove_photo" value="1" x-model="removePhoto"
                       @change="if (removePhoto) preview = null; else preview = @js($employee->photo_url)"
                       class="rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                {{ __('Remove current photo') }}
            </label>
            @endif
        </div>
        <x-input-error class="mt-3" :messages="$errors->get('photo')" />
    </div>

    <!-- Details -->
    <div class="lg:col-span-2 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 space-y-5">
        <div>
            <h3 class="font-bold text-brand-900">{{ __('Employee Details') }}</h3>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="name" :value="__('Full Name')" />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                              :value="old('name', $employee->name ?? '')" required autofocus />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>
            <div>
                <x-input-label for="phone" :value="__('Phone')" />
                <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full"
                              :value="old('phone', $employee->phone ?? '')" required />
                <x-input-error class="mt-2" :messages="$errors->get('phone')" />
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                              :value="old('email', $employee->email ?? '')" required />
                <p class="mt-1 text-xs text-slate-400">{{ __('Used to create this employee\'s login when access is enabled below.') }}</p>
                <x-input-error class="mt-2" :messages="$errors->get('email')" />
            </div>
            <div>
                <x-input-label for="nid_no" :value="__('NID No. (optional)')" />
                <x-text-input id="nid_no" name="nid_no" type="text" class="mt-1 block w-full"
                              :value="old('nid_no', $employee->nid_no ?? '')" />
                <x-input-error class="mt-2" :messages="$errors->get('nid_no')" />
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="joining_date" :value="__('Joining Date')" />
                <x-text-input id="joining_date" name="joining_date" type="date" class="mt-1 block w-full"
                              :value="old('joining_date', $editing ? $employee->joining_date->format('Y-m-d') : '')" required />
                <x-input-error class="mt-2" :messages="$errors->get('joining_date')" />
            </div>
            <div>
                <x-input-label for="branch_id" :value="__('Branch / Branch')" />
                <select id="branch_id" name="branch_id" required
                        class="mt-1 block w-full rounded-lg border-slate-300 focus:border-accent-500 focus:ring-accent-500">
                    <option value="">{{ __('Select…') }}</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected(old('branch_id', $employee->branch_id ?? '') == $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('branch_id')" />
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <x-input-label :value="__('Department (optional)')" />
                <x-searchable-select name="department_id" :options="$departments"
                                      :selected="old('department_id', $employee->department_id ?? null)"
                                      placeholder="{{ __('Select department…') }}" />
                <x-input-error class="mt-2" :messages="$errors->get('department_id')" />
            </div>
            <div>
                <x-input-label :value="__('Designation (optional)')" />
                <x-searchable-select name="designation_id" :options="$designations"
                                      :selected="old('designation_id', $employee->designation_id ?? null)"
                                      placeholder="{{ __('Select designation…') }}" />
                <x-input-error class="mt-2" :messages="$errors->get('designation_id')" />
            </div>
        </div>

        <div>
            <x-input-label :value="__('Reporting Manager (optional)')" />
            <x-searchable-select name="reporting_manager_id" :options="$managers"
                                  :selected="old('reporting_manager_id', $employee->reporting_manager_id ?? null)"
                                  placeholder="{{ __('Select manager…') }}" />
            <x-input-error class="mt-2" :messages="$errors->get('reporting_manager_id')" />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="employment_type" :value="__('Employment Type')" />
                <select id="employment_type" name="employment_type" required
                        class="mt-1 block w-full rounded-lg border-slate-300 focus:border-accent-500 focus:ring-accent-500">
                    @foreach ($employmentTypes as $type)
                        <option value="{{ $type }}" @selected(old('employment_type', $employee->employment_type ?? 'permanent') === $type)>
                            {{ ucfirst(str_replace('_', ' ', $type)) }}
                        </option>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('employment_type')" />
            </div>
            <div>
                <x-input-label for="employment_status" :value="__('Employment Status')" />
                <select id="employment_status" name="employment_status" required
                        class="mt-1 block w-full rounded-lg border-slate-300 focus:border-accent-500 focus:ring-accent-500">
                    @foreach ($employmentStatuses as $status)
                        <option value="{{ $status }}" @selected(old('employment_status', $employee->employment_status ?? 'active') === $status)>
                            {{ ucfirst(str_replace('_', ' ', $status)) }}
                        </option>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('employment_status')" />
            </div>
        </div>

        <div>
            <x-input-label for="notes" :value="__('Notes (optional)')" />
            <textarea id="notes" name="notes" rows="3"
                      class="mt-1 block w-full rounded-lg border-slate-300 focus:border-accent-500 focus:ring-accent-500">{{ old('notes', $employee->notes ?? '') }}</textarea>
            <x-input-error class="mt-2" :messages="$errors->get('notes')" />
        </div>
    </div>

    <!-- Documents -->
    <div class="lg:col-span-3 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <div class="mb-4">
            <h3 class="font-bold text-brand-900">{{ __('Documents') }}</h3>
            <p class="text-xs text-slate-400">{{ __('NID copy, appointment letter, certificates — any file type, up to 5MB each') }}</p>
        </div>

        @if ($editing && $employee->attachments->isNotEmpty())
        <p class="mb-3 text-xs text-slate-400">{{ __('Already-uploaded documents are listed above the form — remove them there before adding replacements.') }}</p>
        @endif

        <div class="space-y-3">
            <template x-for="i in docCount" :key="i">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <input type="text" :name="'document_labels[' + (i - 1) + ']'" placeholder="{{ __('Label, e.g. NID Copy') }}"
                           class="block w-full rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">
                    <input type="file" :name="'documents[' + (i - 1) + ']'"
                           class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-800/5 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-brand-800">
                </div>
            </template>
            <button type="button" @click="docCount++" class="text-xs font-semibold text-brand-700 hover:underline">{{ __('+ Add another document') }}</button>
        </div>
        <x-input-error class="mt-2" :messages="$errors->get('documents.0')" />
    </div>
</div>

<div class="mt-5 flex items-center gap-3">
    <x-primary-button>{{ $editing ? __('Update Employee') : __('Create Employee') }}</x-primary-button>
    <a href="{{ route('employees.index') }}" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50">{{ __('Cancel') }}</a>
</div>
