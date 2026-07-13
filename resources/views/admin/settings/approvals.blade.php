<x-app-layout>
    <x-slot name="title">{{ __('Approval Settings') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('Approval Settings') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ __('Control whether Purchase Requisitions and Sale Quotations need Manager approval before they can be converted into an order.') }}</p>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('settings.approvals.update') }}">
        @csrf
        @method('PUT')

        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <label class="flex items-start gap-4 rounded-xl bg-slate-50 px-5 py-4 cursor-pointer">
                <input type="checkbox" name="purchase_requisition_approval_required" value="1" class="peer sr-only"
                       @checked(old('purchase_requisition_approval_required', $company->purchase_requisition_approval_required))>
                <span class="relative mt-0.5 h-6 w-11 shrink-0 rounded-full bg-slate-300 transition-colors peer-checked:bg-accent-500 after:absolute after:left-0.5 after:top-0.5 after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow after:transition-transform peer-checked:after:translate-x-5"></span>
                <span>
                    <span class="block text-sm font-semibold text-slate-800">{{ __('Require approval for Purchase Requisitions') }}</span>
                    <span class="block text-xs text-slate-500 mt-0.5">
                        {{ __('When on, a new requisition stays Pending until a Manager approves it, and only an approved requisition can be converted into a Purchase Order. When off, requisitions are approved automatically on creation.') }}
                    </span>
                </span>
            </label>
            <x-input-error class="mt-2" :messages="$errors->get('purchase_requisition_approval_required')" />
        </div>

        <div class="mt-5 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <label class="flex items-start gap-4 rounded-xl bg-slate-50 px-5 py-4 cursor-pointer">
                <input type="checkbox" name="sale_quotation_approval_required" value="1" class="peer sr-only"
                       @checked(old('sale_quotation_approval_required', $company->sale_quotation_approval_required))>
                <span class="relative mt-0.5 h-6 w-11 shrink-0 rounded-full bg-slate-300 transition-colors peer-checked:bg-accent-500 after:absolute after:left-0.5 after:top-0.5 after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow after:transition-transform peer-checked:after:translate-x-5"></span>
                <span>
                    <span class="block text-sm font-semibold text-slate-800">{{ __('Require approval for Sale Quotations') }}</span>
                    <span class="block text-xs text-slate-500 mt-0.5">
                        {{ __('When on, a new quotation stays Pending until a Manager approves it, and only an approved quotation can be converted into a Sale. When off, quotations are approved automatically on creation.') }}
                    </span>
                </span>
            </label>
            <x-input-error class="mt-2" :messages="$errors->get('sale_quotation_approval_required')" />
        </div>

        <div class="mt-5">
            <x-primary-button>{{ __('Save Changes') }}</x-primary-button>
        </div>
    </form>
</x-app-layout>
