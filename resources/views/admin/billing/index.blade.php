<x-app-layout>
    <x-slot name="title">{{ __('Billing & Package') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('Billing & Package') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ __('Your subscription and branch usage') }}</p>
        </div>
    </x-slot>

    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 mb-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Current Package') }}</p>
                <p class="mt-1 text-xl font-bold text-brand-900">{{ $subscription?->plan?->name ?? __('None') }}</p>
                @if ($subscription?->onTrial())
                <p class="mt-1 text-sm text-amber-600">{{ __('Free trial — ends :date', ['date' => $subscription->trial_ends_at->format('d M, Y')]) }}</p>
                @elseif ($subscription?->isActive())
                <p class="mt-1 text-sm text-emerald-600">{{ __('Active — renews :date', ['date' => $subscription->current_period_ends_at->format('d M, Y')]) }}</p>
                @else
                <p class="mt-1 text-sm text-rose-600">{{ __('No active package — pick one below.') }}</p>
                @endif
            </div>
            <div class="text-right">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Branches Used') }}</p>
                <p class="mt-1 text-xl font-bold text-brand-900">{{ $branchCount }} / {{ $subscription?->plan?->max_branches ?? '—' }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach ($plans as $plan)
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 {{ $subscription?->plan_id === $plan->id ? 'ring-2 ring-accent-500' : 'ring-slate-200' }} flex flex-col">
            <h3 class="font-bold text-brand-900">{{ $plan->name }}</h3>
            <p class="mt-1 text-xs text-slate-400">{{ $plan->description }}</p>
            <p class="mt-4 text-2xl font-extrabold text-brand-900">
                {{ $plan->price > 0 ? number_format($plan->price).' '.__('BDT') : __('Free') }}
                <span class="text-xs font-medium text-slate-400">/ {{ __(ucfirst($plan->billing_cycle)) }}</span>
            </p>
            <p class="mt-2 text-sm text-slate-500">{{ __('Up to :count branches', ['count' => $plan->max_branches]) }}</p>

            <form method="POST" action="{{ route('billing.store') }}" class="mt-6">
                @csrf
                <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                @if ($subscription?->plan_id === $plan->id)
                <button type="button" disabled class="w-full rounded-lg bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-400">
                    {{ __('Current Package') }}
                </button>
                @else
                <button type="submit" class="w-full rounded-lg bg-accent-600 px-4 py-2 text-sm font-semibold text-white hover:bg-accent-700">
                    {{ __('Choose Package') }}
                </button>
                @endif
            </form>
        </div>
        @endforeach
    </div>
</x-app-layout>
