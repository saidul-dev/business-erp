<x-app-layout>
    <x-slot name="title">{{ __('New Payroll Run') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('New Payroll Run') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ __('Generates one line per active employee with a salary structure set') }}</p>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('payroll-runs.store') }}" class="max-w-lg">
        @csrf
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 space-y-5">
            <div>
                <x-input-label for="site_id" :value="__('Site / Branch')" />
                <select id="site_id" name="site_id" required
                        class="mt-1 block w-full rounded-lg border-slate-300 focus:border-accent-500 focus:ring-accent-500">
                    <option value="">{{ __('Select…') }}</option>
                    @foreach ($sites as $site)
                        <option value="{{ $site->id }}" @selected(old('site_id') == $site->id)>{{ $site->name }}</option>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('site_id')" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <x-input-label for="month" :value="__('Month')" />
                    <select id="month" name="month" required class="mt-1 block w-full rounded-lg border-slate-300 focus:border-accent-500 focus:ring-accent-500">
                        @foreach (range(1, 12) as $m)
                            <option value="{{ $m }}" @selected(old('month', now()->month) == $m)>{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('month')" />
                </div>
                <div>
                    <x-input-label for="year" :value="__('Year')" />
                    <x-text-input id="year" name="year" type="number" class="mt-1 block w-full" :value="old('year', now()->year)" required />
                    <x-input-error class="mt-2" :messages="$errors->get('year')" />
                </div>
            </div>

            <div class="flex items-center gap-3">
                <x-primary-button>{{ __('Generate') }}</x-primary-button>
                <a href="{{ route('payroll-runs.index') }}" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50">{{ __('Cancel') }}</a>
            </div>
        </div>
    </form>
</x-app-layout>
