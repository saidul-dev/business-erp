<x-app-layout>
    <x-slot name="title">{{ __('Salary Structure') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('Salary Structure') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ $employee->name }}</p>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('employees.salary.update', $employee) }}" class="max-w-xl"
          x-data="{ mode: @js(old('mode', $structure->mode ?? 'flat')) }">
        @csrf
        @method('PUT')

        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 space-y-5">
            <div>
                <x-input-label :value="__('Mode')" />
                <div class="mt-1 flex gap-4">
                    <label class="flex items-center gap-2 text-sm">
                        <input type="radio" name="mode" value="flat" x-model="mode" class="border-slate-300 text-accent-600 focus:ring-accent-500">
                        {{ __('Flat monthly amount') }}
                    </label>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="radio" name="mode" value="components" x-model="mode" class="border-slate-300 text-accent-600 focus:ring-accent-500">
                        {{ __('Basic + House Rent + Medical + Conveyance') }}
                    </label>
                </div>
                <x-input-error class="mt-2" :messages="$errors->get('mode')" />
            </div>

            <div x-show="mode === 'flat'">
                <x-input-label for="flat_amount" :value="__('Flat Amount')" />
                <x-text-input id="flat_amount" name="flat_amount" type="number" step="0.01" min="0" class="mt-1 block w-full"
                              :value="old('flat_amount', $structure->flat_amount ?? '0')" />
                <x-input-error class="mt-2" :messages="$errors->get('flat_amount')" />
            </div>

            <div x-show="mode === 'components'" x-cloak class="grid grid-cols-2 gap-4">
                <div>
                    <x-input-label for="basic" :value="__('Basic')" />
                    <x-text-input id="basic" name="basic" type="number" step="0.01" min="0" class="mt-1 block w-full"
                                  :value="old('basic', $structure->basic ?? '0')" />
                    <x-input-error class="mt-2" :messages="$errors->get('basic')" />
                </div>
                <div>
                    <x-input-label for="house_rent" :value="__('House Rent')" />
                    <x-text-input id="house_rent" name="house_rent" type="number" step="0.01" min="0" class="mt-1 block w-full"
                                  :value="old('house_rent', $structure->house_rent ?? '0')" />
                    <x-input-error class="mt-2" :messages="$errors->get('house_rent')" />
                </div>
                <div>
                    <x-input-label for="medical" :value="__('Medical')" />
                    <x-text-input id="medical" name="medical" type="number" step="0.01" min="0" class="mt-1 block w-full"
                                  :value="old('medical', $structure->medical ?? '0')" />
                    <x-input-error class="mt-2" :messages="$errors->get('medical')" />
                </div>
                <div>
                    <x-input-label for="conveyance" :value="__('Conveyance')" />
                    <x-text-input id="conveyance" name="conveyance" type="number" step="0.01" min="0" class="mt-1 block w-full"
                                  :value="old('conveyance', $structure->conveyance ?? '0')" />
                    <x-input-error class="mt-2" :messages="$errors->get('conveyance')" />
                </div>
            </div>

            <div>
                <x-input-label for="effective_from" :value="__('Effective From')" />
                <x-text-input id="effective_from" name="effective_from" type="date" class="mt-1 block w-full"
                              :value="old('effective_from', $structure?->effective_from?->format('Y-m-d') ?? now()->format('Y-m-d'))" required />
                <x-input-error class="mt-2" :messages="$errors->get('effective_from')" />
            </div>

            <div class="flex items-center gap-3">
                <x-primary-button>{{ __('Save Salary Structure') }}</x-primary-button>
                <a href="{{ route('employees.edit', $employee) }}" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50">{{ __('Cancel') }}</a>
            </div>
        </div>
    </form>
</x-app-layout>
