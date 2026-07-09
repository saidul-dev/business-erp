@php $editing = isset($party); @endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4"
     x-data="{
        isCompany: {{ old('is_company', $party->is_company ?? false) ? 'true' : 'false' }},
        isCustomer: {{ old('is_customer', $party->is_customer ?? true) ? 'true' : 'false' }},
        isSupplier: {{ old('is_supplier', $party->is_supplier ?? false) ? 'true' : 'false' }},
     }">
    <!-- Role -->
    <div class="lg:col-span-3 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <div class="mb-4">
            <h3 class="font-bold text-brand-900">{{ __('Role') }}</h3>
            <p class="text-xs text-slate-400">{{ __('The same party can be both — e.g. a wholesale customer you also buy stock back from') }}</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <label class="flex items-start gap-3 rounded-lg border border-slate-200 p-3">
                <input type="checkbox" name="is_customer" value="1" x-model="isCustomer"
                       class="mt-0.5 rounded border-slate-300 text-accent-600 focus:ring-accent-500">
                <span class="block text-sm font-semibold text-brand-900">{{ __('Customer') }}</span>
            </label>
            <label class="flex items-start gap-3 rounded-lg border border-slate-200 p-3">
                <input type="checkbox" name="is_supplier" value="1" x-model="isSupplier"
                       class="mt-0.5 rounded border-slate-300 text-accent-600 focus:ring-accent-500">
                <span class="block text-sm font-semibold text-brand-900">{{ __('Supplier') }}</span>
            </label>
            <label class="flex items-start gap-3 rounded-lg border border-slate-200 p-3">
                <input type="checkbox" name="is_company" value="1" x-model="isCompany"
                       class="mt-0.5 rounded border-slate-300 text-accent-600 focus:ring-accent-500">
                <span>
                    <span class="block text-sm font-semibold text-brand-900">{{ __('Is a Company') }}</span>
                    <span class="block text-xs text-slate-400">{{ __('Off = Individual') }}</span>
                </span>
            </label>
        </div>
        <x-input-error class="mt-2" :messages="$errors->get('is_customer')" />
    </div>

    <!-- Basic details -->
    <div class="lg:col-span-2 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 space-y-5">
        <div>
            <h3 class="font-bold text-brand-900">{{ __('Basic Details') }}</h3>
            <p class="text-xs text-slate-400" x-text="isCompany ? @js(__('Company name and who you deal with there')) : @js(__('Name and contact information'))"></p>
        </div>

        <div>
            <x-input-label for="name">
                <span x-text="isCompany ? @js(__('Company Name')) : @js(__('Full Name'))">{{ old('is_company', $party->is_company ?? false) ? __('Company Name') : __('Full Name') }}</span>
            </x-input-label>
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                          :value="old('name', $party->name ?? '')" required autofocus />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4" x-show="isCompany" x-cloak>
            <div>
                <x-input-label for="contact_person" :value="__('Contact Person (optional)')" />
                <x-text-input id="contact_person" name="contact_person" type="text" class="mt-1 block w-full"
                              :value="old('contact_person', $party->contact_person ?? '')" />
                <x-input-error class="mt-2" :messages="$errors->get('contact_person')" />
            </div>
            <div>
                <x-input-label for="designation" :value="__('Designation (optional)')" />
                <x-text-input id="designation" name="designation" type="text" class="mt-1 block w-full"
                              :value="old('designation', $party->designation ?? '')" placeholder="{{ __('e.g. Purchase Manager') }}" />
                <x-input-error class="mt-2" :messages="$errors->get('designation')" />
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="phone" :value="__('Phone')" />
                <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full"
                              :value="old('phone', $party->phone ?? '')" required />
                <x-input-error class="mt-2" :messages="$errors->get('phone')" />
            </div>
            <div>
                <x-input-label for="email" :value="__('Email (optional)')" />
                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                              :value="old('email', $party->email ?? '')" />
                <x-input-error class="mt-2" :messages="$errors->get('email')" />
            </div>
        </div>

        <div>
            <x-input-label for="address" :value="__('Address (optional)')" />
            <textarea id="address" name="address" rows="2"
                      class="mt-1 block w-full rounded-lg border-slate-300 focus:border-accent-500 focus:ring-accent-500">{{ old('address', $party->address ?? '') }}</textarea>
            <x-input-error class="mt-2" :messages="$errors->get('address')" />
        </div>
    </div>

    <!-- Identification -->
    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 space-y-5">
        <div>
            <h3 class="font-bold text-brand-900">{{ __('Identification') }}</h3>
            <p class="text-xs text-slate-400">{{ __('All optional — fill in whatever paperwork exists') }}</p>
        </div>

        <div x-show="!isCompany">
            <x-input-label for="nid_no" :value="__('NID No.')" />
            <x-text-input id="nid_no" name="nid_no" type="text" class="mt-1 block w-full"
                          :value="old('nid_no', $party->nid_no ?? '')" />
            <x-input-error class="mt-2" :messages="$errors->get('nid_no')" />
        </div>
        <div x-show="isCompany" x-cloak>
            <x-input-label for="bin_no" :value="__('BIN No.')" />
            <x-text-input id="bin_no" name="bin_no" type="text" class="mt-1 block w-full"
                          :value="old('bin_no', $party->bin_no ?? '')" />
            <x-input-error class="mt-2" :messages="$errors->get('bin_no')" />
        </div>
        <div>
            <x-input-label for="tin_no" :value="__('TIN No.')" />
            <x-text-input id="tin_no" name="tin_no" type="text" class="mt-1 block w-full"
                          :value="old('tin_no', $party->tin_no ?? '')" />
            <x-input-error class="mt-2" :messages="$errors->get('tin_no')" />
        </div>
    </div>

    <!-- Credit & opening balance -->
    <div class="lg:col-span-3 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <div class="mb-4">
            <h3 class="font-bold text-brand-900">{{ __('Credit & Opening Balance') }}</h3>
            <p class="text-xs text-slate-400">{{ __('Opening balance is a one-time starting figure — real balances will come from Purchase/Sale once those are tracked') }}</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            <div>
                <x-input-label for="credit_limit" :value="__('Credit Limit')" />
                <x-text-input id="credit_limit" name="credit_limit" type="number" step="0.01" min="0" class="mt-1 block w-full"
                              :value="old('credit_limit', $party->credit_limit ?? '0')" />
                <x-input-error class="mt-2" :messages="$errors->get('credit_limit')" />
            </div>
            <div>
                <x-input-label for="credit_days" :value="__('Credit Days')" />
                <x-text-input id="credit_days" name="credit_days" type="number" min="0" class="mt-1 block w-full"
                              :value="old('credit_days', $party->credit_days ?? '0')" />
                <x-input-error class="mt-2" :messages="$errors->get('credit_days')" />
            </div>
            <div>
                <x-input-label for="opening_balance" :value="__('Opening Balance')" />
                <x-text-input id="opening_balance" name="opening_balance" type="number" step="0.01" min="0" class="mt-1 block w-full"
                              :value="old('opening_balance', $party->opening_balance ?? '0')" />
                <x-input-error class="mt-2" :messages="$errors->get('opening_balance')" />
            </div>
            <div>
                <x-input-label for="opening_balance_type" :value="__('Balance Type')" />
                <select id="opening_balance_type" name="opening_balance_type"
                        class="mt-1 block w-full rounded-lg border-slate-300 focus:border-accent-500 focus:ring-accent-500">
                    <option value="due" @selected(old('opening_balance_type', $party->opening_balance_type ?? 'due') === 'due')>{{ __('Due') }}</option>
                    <option value="advance" @selected(old('opening_balance_type', $party->opening_balance_type ?? 'due') === 'advance')>{{ __('Advance') }}</option>
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('opening_balance_type')" />
            </div>
        </div>
    </div>

    <!-- Notes -->
    <div class="lg:col-span-3 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <x-input-label for="notes" :value="__('Notes (optional)')" />
        <textarea id="notes" name="notes" rows="3"
                  class="mt-1 block w-full rounded-lg border-slate-300 focus:border-accent-500 focus:ring-accent-500">{{ old('notes', $party->notes ?? '') }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('notes')" />
    </div>

    <div class="lg:col-span-3">
        <x-primary-button>{{ $editing ? __('Update Party') : __('Create Party') }}</x-primary-button>
    </div>
</div>
