<x-app-layout>
    <x-slot name="title">{{ __('Edit Employee') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('Edit Employee') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ $employee->name }}</p>
        </div>
    </x-slot>

    @if ($employee->attachments->isNotEmpty())
    {{-- Deliberately outside the update form below — each delete posts to its
         own route/method, and HTML forms can't nest. --}}
    <div class="mb-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <h3 class="font-bold text-brand-900">{{ __('Documents') }}</h3>
        <div class="mt-4 divide-y divide-slate-100 rounded-lg ring-1 ring-slate-200">
            @foreach ($employee->attachments as $attachment)
            <div class="flex items-center justify-between px-4 py-2.5 text-sm">
                <a href="{{ $attachment->url }}" target="_blank" class="flex items-center gap-2 text-brand-700 hover:underline">
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                    <span>{{ $attachment->label }}</span>
                </a>
                <form method="POST" action="{{ route('employees.attachments.destroy', [$employee, $attachment]) }}"
                      onsubmit="return confirm('{{ __('Remove this document?') }}');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-lg p-1.5 text-slate-400 hover:bg-rose-50 hover:text-rose-600">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                    </button>
                </form>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <form method="POST" action="{{ route('employees.update', $employee) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.employees._form')
    </form>

    {{-- Deliberately outside the update form above — HTML forms can't nest,
         and this posts to a different route (toggle-login) with its own method. --}}
    <div class="mt-4 max-w-sm rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <h3 class="text-sm font-bold text-brand-900">{{ __('Login Access') }}</h3>
        <p class="mt-0.5 text-xs text-slate-400">
            @if ($employee->hasActiveLogin())
                {{ __('Login is enabled') }} — {{ $employee->user->email }}
            @elseif ($employee->user_id)
                {{ __('Login is disabled') }} — {{ $employee->user->email }}
            @else
                {{ __('No login account yet') }}
            @endif
        </p>
        <form method="POST" action="{{ route('employees.toggle-login', $employee) }}" class="mt-3">
            @csrf
            @method('PATCH')
            <button type="submit"
                    class="w-full rounded-lg px-4 py-2 text-sm font-semibold ring-1
                    {{ $employee->hasActiveLogin() ? 'text-rose-600 ring-rose-200 hover:bg-rose-50' : 'text-emerald-700 ring-emerald-200 hover:bg-emerald-50' }}">
                {{ $employee->hasActiveLogin() ? __('Disable Login') : ($employee->user_id ? __('Re-enable Login') : __('Enable Login')) }}
            </button>
        </form>
    </div>

    @can('hrm.edit')
    <div class="mt-4 max-w-sm rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <h3 class="text-sm font-bold text-brand-900">{{ __('Salary Structure') }}</h3>
        <p class="mt-0.5 text-xs text-slate-400">
            @if ($employee->salaryStructure)
                {{ $employee->salaryStructure->mode === 'flat' ? __('Flat amount') : __('Component-based') }} —
                {{ number_format($employee->salaryStructure->grossAmount(), 2) }} / {{ __('month') }}
            @else
                {{ __('Not set yet — required before this employee can be added to a payroll run.') }}
            @endif
        </p>
        <a href="{{ route('employees.salary.edit', $employee) }}"
           class="mt-3 block w-full rounded-lg px-4 py-2 text-center text-sm font-semibold text-brand-700 ring-1 ring-brand-200 hover:bg-brand-50">
            {{ $employee->salaryStructure ? __('Edit Salary Structure') : __('Set Salary Structure') }}
        </a>
    </div>
    @endcan
</x-app-layout>
