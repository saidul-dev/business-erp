<x-app-layout>
    <x-slot name="title">{{ __('Attendance Register') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('Attendance Register') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ __('Mark daily attendance for each employee') }}</p>
        </div>
    </x-slot>

    <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 mb-6">
        <form method="GET" action="{{ route('attendance.register') }}" class="flex flex-wrap items-end gap-4">
            <div>
                <x-input-label for="date" :value="__('Date')" />
                <input type="date" id="date" name="date" value="{{ $date->format('Y-m-d') }}"
                       class="mt-1 block rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">
            </div>
            <button type="submit" class="shrink-0 rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">{{ __('Go') }}</button>
        </form>
    </div>

    <form method="POST" action="{{ route('attendance.save-register') }}">
        @csrf
        <input type="hidden" name="date" value="{{ $date->format('Y-m-d') }}">

        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
            <table class="w-full min-w-[800px] text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                        <th class="px-5 py-3 font-semibold">{{ __('Employee') }}</th>
                        <th class="px-5 py-3 font-semibold">{{ __('Department') }}</th>
                        <th class="px-5 py-3 font-semibold">{{ __('Status') }}</th>
                        <th class="px-5 py-3 font-semibold">{{ __('Notes') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($employees as $i => $employee)
                    @php $existing = $employee->attendanceLogs->first(); @endphp
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3 font-semibold text-slate-800">
                            {{ $employee->name }}
                            <input type="hidden" name="records[{{ $i }}][employee_id]" value="{{ $employee->id }}">
                        </td>
                        <td class="px-5 py-3 text-slate-600">{{ $employee->department?->name ?? '—' }}</td>
                        <td class="px-5 py-3">
                            <select name="records[{{ $i }}][status]"
                                    class="rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">
                                @foreach (\App\Models\AttendanceLog::STATUSES as $status)
                                    <option value="{{ $status }}" @selected(($existing->status ?? 'present') === $status)>
                                        {{ ucfirst(str_replace('_', ' ', $status)) }}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                        <td class="px-5 py-3">
                            <input type="text" name="records[{{ $i }}][notes]" value="{{ $existing->notes ?? '' }}"
                                   placeholder="{{ __('Optional') }}"
                                   class="block w-full rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-5 py-10 text-center text-slate-400">{{ __('No active employees to mark attendance for.') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>

        @if ($employees->isNotEmpty())
        <div class="mt-5">
            <x-primary-button>{{ __('Save Attendance') }}</x-primary-button>
        </div>
        @endif
    </form>
</x-app-layout>
