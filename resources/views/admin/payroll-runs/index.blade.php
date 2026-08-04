<x-app-layout>
    <x-slot name="title">{{ __('Payroll Runs') }}</x-slot>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-brand-900">{{ __('Payroll Runs') }}</h2>
                <p class="text-sm text-slate-500 mt-0.5">{{ __('Monthly salary runs, per branch') }}</p>
            </div>
            @can('hrm.create')
            <a href="{{ route('payroll-runs.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 focus:ring-2 focus:ring-accent-500 focus:ring-offset-2">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                {{ __('New Payroll Run') }}
            </a>
            @endcan
        </div>
    </x-slot>

    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full min-w-[700px] text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <th class="px-5 py-3 font-semibold">{{ __('Period') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Branch') }}</th>
                    <th class="px-5 py-3 font-semibold text-right">{{ __('Total') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Status') }}</th>
                    <th class="px-5 py-3 font-semibold text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($runs as $run)
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-3 font-semibold text-slate-800">{{ $run->monthLabel() }}</td>
                    <td class="px-5 py-3 text-slate-600">{{ $run->branch->name }}</td>
                    <td class="px-5 py-3 text-right text-slate-600">{{ number_format($run->total_amount, 2) }}</td>
                    <td class="px-5 py-3">
                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1
                            {{ $run->status === 'approved' ? 'bg-emerald-50 text-emerald-600 ring-emerald-200' : 'bg-amber-50 text-amber-600 ring-amber-200' }}">
                            {{ ucfirst($run->status) }}
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        <div class="flex items-center justify-end gap-1">
                            <a href="{{ route('payroll-runs.show', $run) }}" title="{{ __('View') }}"
                               class="rounded-lg p-2 text-slate-400 hover:bg-brand-50 hover:text-brand-700">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                            </a>
                            @can('hrm.delete')
                            @if ($run->status === 'draft')
                            <form method="POST" action="{{ route('payroll-runs.destroy', $run) }}"
                                  onsubmit="return confirm('{{ __('Delete this draft payroll run?') }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" title="{{ __('Delete') }}"
                                        class="rounded-lg p-2 text-slate-400 hover:bg-rose-50 hover:text-rose-600">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                </button>
                            </form>
                            @endif
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-5 py-10 text-center text-slate-400">{{ __('No payroll runs yet.') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>

        @if ($runs->hasPages())
        <div class="border-t border-slate-100 px-5 py-4">
            {{ $runs->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
