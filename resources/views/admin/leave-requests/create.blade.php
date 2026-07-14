<x-app-layout>
    <x-slot name="title">{{ __('Apply for Leave') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('Apply for Leave') }}</h2>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('leave-requests.store') }}" class="max-w-xl">
        @csrf
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 space-y-5">
            @if ($canPickAnyEmployee)
            <div>
                <x-input-label :value="__('Employee')" />
                <x-searchable-select name="employee_id" :options="$employees" :selected="old('employee_id')"
                                      placeholder="{{ __('Leave blank to apply for yourself…') }}" />
                <x-input-error class="mt-2" :messages="$errors->get('employee_id')" />
            </div>
            @endif

            <div>
                <x-input-label for="leave_type_id" :value="__('Leave Type')" />
                <select id="leave_type_id" name="leave_type_id" required
                        class="mt-1 block w-full rounded-lg border-slate-300 focus:border-accent-500 focus:ring-accent-500">
                    <option value="">{{ __('Select…') }}</option>
                    @foreach ($leaveTypes as $leaveType)
                        <option value="{{ $leaveType->id }}" @selected(old('leave_type_id') == $leaveType->id)>{{ $leaveType->name }}</option>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('leave_type_id')" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="from_date" :value="__('From Date')" />
                    <x-text-input id="from_date" name="from_date" type="date" class="mt-1 block w-full" :value="old('from_date')" required />
                    <x-input-error class="mt-2" :messages="$errors->get('from_date')" />
                </div>
                <div>
                    <x-input-label for="to_date" :value="__('To Date')" />
                    <x-text-input id="to_date" name="to_date" type="date" class="mt-1 block w-full" :value="old('to_date')" required />
                    <x-input-error class="mt-2" :messages="$errors->get('to_date')" />
                </div>
            </div>

            <div>
                <x-input-label for="reason" :value="__('Reason (optional)')" />
                <textarea id="reason" name="reason" rows="3"
                          class="mt-1 block w-full rounded-lg border-slate-300 focus:border-accent-500 focus:ring-accent-500">{{ old('reason') }}</textarea>
                <x-input-error class="mt-2" :messages="$errors->get('reason')" />
            </div>

            <div class="flex items-center gap-3">
                <x-primary-button>{{ __('Submit Application') }}</x-primary-button>
                <a href="{{ route('leave-requests.index') }}" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50">{{ __('Cancel') }}</a>
            </div>
        </div>
    </form>
</x-app-layout>
