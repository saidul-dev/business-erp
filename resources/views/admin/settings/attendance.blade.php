<x-app-layout>
    <x-slot name="title">{{ __('Attendance Settings') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('Attendance Settings') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ __('Set the default shift start time and how many minutes of grace are allowed before a self check-in counts as late.') }}</p>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('settings.attendance.update') }}" class="max-w-xl">
        @csrf
        @method('PUT')

        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 space-y-5">
            <div>
                <x-input-label for="default_shift_start_time" :value="__('Default Shift Start Time')" />
                <x-text-input id="default_shift_start_time" name="default_shift_start_time" type="time" class="mt-1 block w-full"
                              :value="old('default_shift_start_time', substr($company->default_shift_start_time, 0, 5))" required />
                <x-input-error class="mt-2" :messages="$errors->get('default_shift_start_time')" />
            </div>

            <div>
                <x-input-label for="late_grace_minutes" :value="__('Late Grace Period (minutes)')" />
                <x-text-input id="late_grace_minutes" name="late_grace_minutes" type="number" min="0" max="180" class="mt-1 block w-full"
                              :value="old('late_grace_minutes', $company->late_grace_minutes)" required />
                <p class="mt-1 text-xs text-slate-400">{{ __('A check-in after start time + grace period is marked late.') }}</p>
                <x-input-error class="mt-2" :messages="$errors->get('late_grace_minutes')" />
            </div>

            <div>
                <x-primary-button>{{ __('Save Changes') }}</x-primary-button>
            </div>
        </div>
    </form>
</x-app-layout>
