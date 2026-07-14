<x-app-layout>
    <x-slot name="title">{{ __('Attendance Summary') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('Attendance Summary') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ __('Monthly attendance counts per employee') }}</p>
        </div>
    </x-slot>

    <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 mb-6">
        <form method="GET" action="{{ route('attendance.summary') }}" class="flex flex-wrap items-end gap-4">
            <div>
                <x-input-label for="month" :value="__('Month')" />
                <select id="month" name="month" class="mt-1 block rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">
                    @foreach (range(1, 12) as $m)
                        <option value="{{ $m }}" @selected($month == $m)>{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-input-label for="year" :value="__('Year')" />
                <select id="year" name="year" class="mt-1 block rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">
                    @foreach (range(now()->year, now()->year - 3) as $y)
                        <option value="{{ $y }}" @selected($year == $y)>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-56">
                <x-input-label for="department_id" :value="__('Department')" />
                <select id="department_id" name="department_id" class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}" @selected(request('department_id') == $department->id)>{{ $department->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="shrink-0 rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">{{ __('Filter') }}</button>
        </form>
    </div>

    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full min-w-[700px] text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <th class="px-5 py-3 font-semibold">{{ __('Employee') }}</th>
                    <th class="px-5 py-3 font-semibold text-center">{{ __('Present') }}</th>
                    <th class="px-5 py-3 font-semibold text-center">{{ __('Absent') }}</th>
                    <th class="px-5 py-3 font-semibold text-center">{{ __('Half Day') }}</th>
                    <th class="px-5 py-3 font-semibold text-center">{{ __('Leave') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($rows as $row)
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-3 font-semibold text-slate-800">{{ $row['employee']->name }}</td>
                    <td class="px-5 py-3 text-center text-emerald-600 font-semibold">{{ $row['present'] }}</td>
                    <td class="px-5 py-3 text-center text-rose-600 font-semibold">{{ $row['absent'] }}</td>
                    <td class="px-5 py-3 text-center text-amber-600 font-semibold">{{ $row['half_day'] }}</td>
                    <td class="px-5 py-3 text-center text-slate-500 font-semibold">{{ $row['leave'] }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-5 py-10 text-center text-slate-400">{{ __('No employees to show.') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</x-app-layout>
