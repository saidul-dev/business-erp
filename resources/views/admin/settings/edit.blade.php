<x-app-layout>
    <x-slot name="title">General Settings</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">General Settings</h2>
            <p class="text-sm text-slate-500 mt-0.5">Business profile shown on invoices, reports and print templates</p>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data"
          x-data="{ preview: @js($company->logo_url), removeLogo: false }">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <!-- Logo -->
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <div class="mb-4">
                    <h3 class="font-bold text-brand-900">Business Logo</h3>
                    <p class="text-xs text-slate-400">PNG or JPG, up to 2MB</p>
                </div>

                <div class="flex flex-col items-center gap-4">
                    <div class="grid h-28 w-28 place-items-center overflow-hidden rounded-2xl bg-gradient-to-br from-brand-800 to-brand-950 ring-1 ring-slate-200">
                        <template x-if="preview">
                            <img :src="preview" alt="Logo preview" class="h-full w-full object-cover">
                        </template>
                        <template x-if="!preview">
                            <x-application-logo class="h-14 w-14" />
                        </template>
                    </div>

                    <label class="cursor-pointer rounded-lg bg-brand-800/5 px-4 py-2 text-xs font-semibold text-brand-800 hover:bg-brand-800/10">
                        Choose Image
                        <input type="file" name="logo" accept="image/*" class="hidden"
                               @change="removeLogo = false; const f = $event.target.files[0]; if (f) preview = URL.createObjectURL(f)">
                    </label>

                    @if ($company->logo_url)
                    <label class="flex items-center gap-2 text-xs text-slate-500">
                        <input type="checkbox" name="remove_logo" value="1" x-model="removeLogo"
                               @change="if (removeLogo) preview = null; else preview = @js($company->logo_url)"
                               class="rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                        Remove current logo
                    </label>
                    @endif
                </div>
                <x-input-error class="mt-3" :messages="$errors->get('logo')" />
            </div>

            <!-- Business details -->
            <div class="lg:col-span-2 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 space-y-5">
                <div>
                    <h3 class="font-bold text-brand-900">Business Details</h3>
                    <p class="text-xs text-slate-400">Name and contact information</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="name" value="Business Name" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                                      :value="old('name', $company->name)" required autofocus />
                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                    </div>
                    <div>
                        <x-input-label for="legal_name" value="Legal / Registered Name" />
                        <x-text-input id="legal_name" name="legal_name" type="text" class="mt-1 block w-full"
                                      :value="old('legal_name', $company->legal_name)" />
                        <x-input-error class="mt-2" :messages="$errors->get('legal_name')" />
                    </div>
                    <div>
                        <x-input-label for="email" value="Business Email" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                                      :value="old('email', $company->email)" />
                        <x-input-error class="mt-2" :messages="$errors->get('email')" />
                    </div>
                    <div>
                        <x-input-label for="phone" value="Business Phone" />
                        <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full"
                                      :value="old('phone', $company->phone)" />
                        <x-input-error class="mt-2" :messages="$errors->get('phone')" />
                    </div>
                </div>

                <div>
                    <x-input-label for="address" value="Address" />
                    <textarea id="address" name="address" rows="3"
                              class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-accent-500 focus:ring-accent-500">{{ old('address', $company->address) }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('address')" />
                </div>
            </div>
        </div>

        <!-- Finance & compliance -->
        <div class="mt-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <div class="mb-4">
                <h3 class="font-bold text-brand-900">Finance &amp; Compliance</h3>
                <p class="text-xs text-slate-400">Used across accounts, reports and Mushak invoice formatting</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <x-input-label for="currency" value="Currency" />
                    <select id="currency" name="currency" required
                            class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-accent-500 focus:ring-accent-500">
                        @foreach (['BDT' => 'BDT — Taka', 'USD' => 'USD — US Dollar', 'EUR' => 'EUR — Euro'] as $code => $label)
                        <option value="{{ $code }}" @selected(old('currency', $company->currency) === $code)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('currency')" />
                </div>
                <div>
                    <x-input-label for="financial_year_start_month" value="Financial Year Starts" />
                    <select id="financial_year_start_month" name="financial_year_start_month" required
                            class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-accent-500 focus:ring-accent-500">
                        @foreach (['January','February','March','April','May','June','July','August','September','October','November','December'] as $i => $monthName)
                        <option value="{{ $i + 1 }}" @selected((int) old('financial_year_start_month', $company->financial_year_start_month) === $i + 1)>{{ $monthName }}</option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('financial_year_start_month')" />
                </div>
                <div>
                    <x-input-label for="vat_registration_no" value="VAT / Mushak Reg. No." />
                    <x-text-input id="vat_registration_no" name="vat_registration_no" type="text" class="mt-1 block w-full"
                                  :value="old('vat_registration_no', $company->vat_registration_no)" />
                    <x-input-error class="mt-2" :messages="$errors->get('vat_registration_no')" />
                </div>
                <div>
                    <x-input-label for="bin_no" value="BIN No." />
                    <x-text-input id="bin_no" name="bin_no" type="text" class="mt-1 block w-full"
                                  :value="old('bin_no', $company->bin_no)" />
                    <x-input-error class="mt-2" :messages="$errors->get('bin_no')" />
                </div>
            </div>
        </div>

        <!-- Website & e-commerce -->
        <div class="mt-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <div class="mb-4">
                <h3 class="font-bold text-brand-900">Website &amp; E-commerce</h3>
                <p class="text-xs text-slate-400">Controls the public company website that will run alongside this admin panel</p>
            </div>

            <label class="flex items-start gap-4 rounded-xl bg-slate-50 px-5 py-4 cursor-pointer">
                <input type="checkbox" name="ecommerce_enabled" value="1" class="peer sr-only"
                       @checked(old('ecommerce_enabled', $company->ecommerce_enabled))>
                <span class="relative mt-0.5 h-6 w-11 shrink-0 rounded-full bg-slate-300 transition-colors peer-checked:bg-accent-500 after:absolute after:left-0.5 after:top-0.5 after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow after:transition-transform peer-checked:after:translate-x-5"></span>
                <span>
                    <span class="block text-sm font-semibold text-slate-800">Enable E-commerce</span>
                    <span class="block text-xs text-slate-500 mt-0.5">
                        Turn this on to publish the online storefront on the company website — product catalog, cart,
                        checkout and order tracking will appear automatically. Leave it off to keep the website as an
                        information-only company page.
                    </span>
                </span>
            </label>
            <x-input-error class="mt-2" :messages="$errors->get('ecommerce_enabled')" />
        </div>

        <div class="mt-5 flex items-center gap-3">
            <x-primary-button>Save Changes</x-primary-button>
        </div>
    </form>
</x-app-layout>
