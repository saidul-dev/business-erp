<x-app-layout>
    <x-slot name="title">{{ __('Leave Requests') }}</x-slot>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-brand-900">{{ __('Leave Requests') }}</h2>
                <p class="text-sm text-slate-500 mt-0.5">{{ __('Applications and approvals') }}</p>
            </div>
            <button type="button" @click="$dispatch('open-modal', 'apply-leave')" class="inline-flex items-center gap-2 rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 focus:ring-2 focus:ring-accent-500 focus:ring-offset-2">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                {{ __('Apply for Leave') }}
            </button>
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
                            <button type="button" @click="$dispatch('open-modal', 'view-leave-{{ $leaveRequest->id }}')"
                                    class="rounded-lg px-2.5 py-1 text-xs font-semibold text-brand-700 ring-1 ring-brand-200 hover:bg-brand-50">{{ __('View') }}</button>
                            @if ($leaveRequest->status === 'pending')
                                @if ($canApprove)
                                <form method="POST" action="{{ route('leave-requests.approve', $leaveRequest) }}"
                                      onsubmit="return confirm('{{ __('Approve this leave request?') }}');">
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
                        <form id="reject-form-{{ $leaveRequest->id }}" method="POST" action="{{ route('leave-requests.reject', $leaveRequest) }}" class="hidden mt-2 flex items-center gap-2"
                              onsubmit="return confirm('{{ __('Reject this leave request?') }}');">
                            @csrf
                            @method('PATCH')
                            <input type="text" name="rejection_reason" placeholder="{{ __('Reason (optional)') }}"
                                   class="rounded-lg border-slate-300 text-xs focus:border-accent-500 focus:ring-accent-500">
                            <button type="submit" class="rounded-lg bg-rose-600 px-2.5 py-1 text-xs font-semibold text-white hover:bg-rose-700">{{ __('Confirm Reject') }}</button>
                        </form>
                        @endif

                        <x-modal name="view-leave-{{ $leaveRequest->id }}" max-width="md">
                            <div class="p-6 text-left">
                                <h2 class="text-lg font-bold text-brand-900">{{ __('Leave Request') }}</h2>
                                <p class="text-xs text-slate-400 mt-0.5">{{ $leaveRequest->employee->name }}</p>

                                <dl class="mt-4 space-y-3 text-sm">
                                    <div class="flex justify-between gap-4">
                                        <dt class="text-slate-400">{{ __('Leave Type') }}</dt>
                                        <dd class="font-semibold text-slate-800">{{ $leaveRequest->leaveType->name }}</dd>
                                    </div>
                                    <div class="flex justify-between gap-4">
                                        <dt class="text-slate-400">{{ __('Dates') }}</dt>
                                        <dd class="font-semibold text-slate-800">{{ $leaveRequest->from_date->format('d M') }} – {{ $leaveRequest->to_date->format('d M, Y') }}</dd>
                                    </div>
                                    <div class="flex justify-between gap-4">
                                        <dt class="text-slate-400">{{ __('Days') }}</dt>
                                        <dd class="font-semibold text-slate-800">{{ $leaveRequest->days }}</dd>
                                    </div>
                                    <div class="flex justify-between gap-4">
                                        <dt class="text-slate-400">{{ __('Status') }}</dt>
                                        <dd>
                                            <span @class([
                                                'inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1',
                                                'bg-amber-50 text-amber-600 ring-amber-200' => $leaveRequest->status === 'pending',
                                                'bg-emerald-50 text-emerald-600 ring-emerald-200' => $leaveRequest->status === 'approved',
                                                'bg-rose-50 text-rose-600 ring-rose-200' => $leaveRequest->status === 'rejected',
                                            ])>{{ ucfirst($leaveRequest->status) }}</span>
                                        </dd>
                                    </div>
                                    @if ($leaveRequest->reason)
                                    <div>
                                        <dt class="text-slate-400">{{ __('Reason') }}</dt>
                                        <dd class="mt-1 text-slate-700">{{ $leaveRequest->reason }}</dd>
                                    </div>
                                    @endif
                                    @if ($leaveRequest->status !== 'pending')
                                    <div class="flex justify-between gap-4">
                                        <dt class="text-slate-400">{{ __('Reviewed By') }}</dt>
                                        <dd class="font-semibold text-slate-800">{{ $leaveRequest->reviewer->name ?? '—' }} @if($leaveRequest->reviewed_at)· {{ $leaveRequest->reviewed_at->format('d M, Y') }}@endif</dd>
                                    </div>
                                    @endif
                                    @if ($leaveRequest->status === 'rejected' && $leaveRequest->rejection_reason)
                                    <div>
                                        <dt class="text-slate-400">{{ __('Rejection Reason') }}</dt>
                                        <dd class="mt-1 text-slate-700">{{ $leaveRequest->rejection_reason }}</dd>
                                    </div>
                                    @endif
                                </dl>

                                <div class="flex justify-end pt-5">
                                    <button type="button" x-on:click="$dispatch('close')" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50">{{ __('Close') }}</button>
                                </div>
                            </div>
                        </x-modal>
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

    <x-modal name="apply-leave" max-width="lg" :show="$errors->any()" focusable>
        <div class="p-6">
            <h2 class="text-lg font-bold text-brand-900">{{ __('Apply for Leave') }}</h2>

            <form method="POST" action="{{ route('leave-requests.store') }}" class="mt-4 space-y-5">
                @csrf

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

                <div class="flex items-center gap-3 pt-2">
                    <x-primary-button>{{ __('Submit Application') }}</x-primary-button>
                    <button type="button" x-on:click="$dispatch('close')" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50">{{ __('Cancel') }}</button>
                </div>
            </form>
        </div>
    </x-modal>
</x-app-layout>
