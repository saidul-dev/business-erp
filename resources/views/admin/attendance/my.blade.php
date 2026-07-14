<x-app-layout>
    <x-slot name="title">{{ __('My Attendance') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('My Attendance') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ $employee->name }}</p>
        </div>
    </x-slot>

    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 mb-6 max-w-md">
        <h3 class="font-bold text-brand-900">{{ __('Today') }}</h3>
        <p class="mt-1 text-sm text-slate-500">
            @if ($todayLog?->check_in_at)
                {{ __('Checked in at') }} {{ $todayLog->check_in_at->format('h:i A') }}
                @if ($todayLog->is_late)
                    <span class="ml-1 inline-flex rounded-full bg-rose-50 px-2 py-0.5 text-xs font-semibold text-rose-600 ring-1 ring-rose-200">{{ __('Late') }}</span>
                @endif
            @else
                {{ __('Not checked in yet') }}
            @endif
            @if ($todayLog?->check_out_at)
                <br>{{ __('Checked out at') }} {{ $todayLog->check_out_at->format('h:i A') }}
            @endif
        </p>

        <div class="mt-4 flex gap-3">
            <form method="POST" action="{{ route('attendance.check-in') }}">
                @csrf
                <button type="submit" @disabled($todayLog?->check_in_at)
                        class="rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 disabled:opacity-40 disabled:cursor-not-allowed">
                    {{ __('Check In') }}
                </button>
            </form>
            <form method="POST" action="{{ route('attendance.check-out') }}">
                @csrf
                <button type="submit" @disabled(! $todayLog?->check_in_at || $todayLog?->check_out_at)
                        class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed">
                    {{ __('Check Out') }}
                </button>
            </form>
        </div>
    </div>

    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 overflow-hidden max-w-2xl">
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <th class="px-5 py-3 font-semibold">{{ __('Date') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Status') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Check In') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Check Out') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($recentLogs as $log)
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-3 text-slate-800">{{ $log->date->format('d M, Y') }}</td>
                    <td class="px-5 py-3 capitalize text-slate-600">{{ str_replace('_', ' ', $log->status) }}</td>
                    <td class="px-5 py-3 text-slate-600">{{ $log->check_in_at?->format('h:i A') ?? '—' }}</td>
                    <td class="px-5 py-3 text-slate-600">{{ $log->check_out_at?->format('h:i A') ?? '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-5 py-10 text-center text-slate-400">{{ __('No attendance history yet.') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</x-app-layout>
