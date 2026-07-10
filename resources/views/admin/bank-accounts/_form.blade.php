@php $editing = isset($bankAccount); @endphp

<div class="max-w-xl rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 space-y-5">
    <div>
        <x-input-label for="name" :value="__('Account Name')" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                      :value="old('name', $bankAccount->name ?? '')" required autofocus
                      placeholder="{{ __('e.g. City Bank – CA 1234 or Petty Cash') }}" />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    <div>
        <x-input-label for="site_id" :value="__('Site (optional)')" />
        <x-searchable-select name="site_id" :options="$sites" :selected="old('site_id', $bankAccount->site_id ?? null)"
                              placeholder="{{ __('Company-wide — shared across all sites') }}" />
        <p class="mt-1 text-xs text-slate-400">{{ __('Leave blank for a shared account (e.g. the main bank account); pick a Site for a branch-specific cash drawer.') }}</p>
        <x-input-error class="mt-2" :messages="$errors->get('site_id')" />
    </div>

    @if ($editing && $bankAccount->is_system)
    <p class="text-xs text-amber-600 bg-amber-50 rounded-lg px-3 py-2 ring-1 ring-amber-200">
        {{ __('This is a system account used by the accounting ledger — it can be renamed but not deleted.') }}
    </p>
    @endif
</div>

<div class="mt-5 flex items-center gap-3">
    <x-primary-button>{{ $editing ? __('Update Account') : __('Create Account') }}</x-primary-button>
    <a href="{{ route('bank-accounts.index') }}" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50">{{ __('Cancel') }}</a>
</div>
