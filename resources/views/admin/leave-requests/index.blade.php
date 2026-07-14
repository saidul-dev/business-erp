<x-app-layout>
    <x-slot name="title">{{ __('Leave Requests') }}</x-slot>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-brand-900">{{ __('Leave Requests') }}</h2>
                <p class="text-sm text-slate-500 mt-0.5">{{ __('Applications and approvals') }}</p>
            </div>
            <a href="{{ route('leave-requests.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 focus:ring-2 focus:ring-accent-500 focus:ring-offset-2">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                {{ __('Apply for Leave') }}
            </a>
        </div>
    </x-slot>

    <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 mb-6">
        <form method="GET" action="{{ route('leave-requests.index') }}" class="flex flex-wrap items-end gap-4">
            <div class="w-48">
                <x-input-label for="status" :value="__('Status')" />
                <select id="status" name="status" class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">
                    <option value="">{{ __('All') }}</option>
                    @foreach (\App\Models\LeaveRequest::STATUSES as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="shrink-0 rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">{{ __('Filter') }}</button>
        </form>
    </div>

    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full min-w-[900px] text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <th class="px-5 py-3 font-semibold">{{ __('Employee') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Leave Type') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Dates') }}</th>
                    <th class="px-5 py-3 font-semibold text-center">{{ __('Days') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Status') }}</th>
                    <th class="px-5 py-3 font-semibold text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($leaveRequests as $leaveRequest)
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-3 font-semibold text-slate-800">{{ $leaveRequest->employee->name }}</td>
                    <td class="px-5 py-3 text-slate-600">{{ $leaveRequest->leaveType->name }}</td>
                    <td class="px-5 py-3 text-slate-600">{{ $leaveRequest->from_date->format('d M') }} – {{ $leaveRequest->to_date->format('d M, Y') }}</td>
                    <td class="px-5 py-3 text-center text-slate-600">{{ $leaveRequest->days }}</td>
                    <td class="px-5 py-3">
                        <span @class([
                            'inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1',
                            'bg-amber-50 text-amber-600 ring-amber-200' => $leaveRequest->status === 'pending',
                            'bg-emerald-50 text-emerald-600 ring-emerald-200' => $leaveRequest->status === 'approved',
                            'bg-rose-50 text-rose-600 ring-rose-200' => $leaveRequest->status === 'rejected',
                        ])>
                            {{ ucfirst($leaveRequest->status) }}
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        <div class="flex items-center justify-end gap-2">
                            @if ($leaveRequest->status === 'pending')
                                @if ($canApprove)
                                <form method="POST" action="{{ route('leave-requests.approve', $leaveRequest) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="rounded-lg px-2.5 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200 hover:bg-emerald-50">{{ __('Approve') }}</button>
                                </form>
                                <button type="button" onclick="document.getElementById('reject-form-{{ $leaveRequest->id }}').classList.toggle('hidden')"
                                        class="rounded-lg px-2.5 py-1 text-xs font-semibold text-rose-600 ring-1 ring-rose-200 hover:bg-rose-50">{{ __('Reject') }}</button>
                                @endif
                                @if ($leaveRequest->employee_id === $currentEmployeeId || $canApprove)
                                <form method="POST" action="{{ route('leave-requests.destroy', $leaveRequest) }}"
                                      onsubmit="return confirm('{{ __('Cancel this leave request?') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg px-2.5 py-1 text-xs font-semibold text-slate-500 ring-1 ring-slate-200 hover:bg-slate-50">{{ __('Cancel') }}</button>
                                </form>
                                @endif
                            @endif
                        </div>
                        @if ($canApprove && $leaveRequest->status === 'pending')
                        <form id="reject-form-{{ $leaveRequest->id }}" method="POST" action="{{ route('leave-requests.reject', $leaveRequest) }}" class="hidden mt-2 flex items-center gap-2">
                            @csrf
                            @method('PATCH')
                            <input type="text" name="rejection_reason" placeholder="{{ __('Reason (optional)') }}"
                                   class="rounded-lg border-slate-300 text-xs focus:border-accent-500 focus:ring-accent-500">
                            <button type="submit" class="rounded-lg bg-rose-600 px-2.5 py-1 text-xs font-semibold text-white hover:bg-rose-700">{{ __('Confirm Reject') }}</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-5 py-10 text-center text-slate-400">{{ __('No leave requests yet.') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>

        @if ($leaveRequests->hasPages())
        <div class="border-t border-slate-100 px-5 py-4">
            {{ $leaveRequests->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
