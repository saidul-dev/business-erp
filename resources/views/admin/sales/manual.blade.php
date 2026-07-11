<x-app-layout>
    <x-slot name="title">{{ __('User Manual') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('User Manual') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ __('Sale, Delivery and Sale Return — a step-by-step guide') }}</p>
        </div>
    </x-slot>

    <div class="space-y-4">

        {{-- Sale workflow --}}
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <div class="mb-4">
                <h3 class="font-bold text-brand-900">{{ __('1. How to make a Sale') }}</h3>
                <p class="text-xs text-slate-400">{{ __('The full process, from placing an order to delivering goods') }}</p>
            </div>

            <ol class="space-y-4">
                <li class="flex gap-3">
                    <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-brand-800 text-xs font-bold text-white">1</span>
                    <div>
                        <p class="text-sm font-semibold text-slate-800">{{ __('Sales menu → New Sale') }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">{{ __('Click "New Sale" from the Sales dropdown in the sidebar.') }}</p>
                    </div>
                </li>
                <li class="flex gap-3">
                    <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-brand-800 text-xs font-bold text-white">2</span>
                    <div>
                        <p class="text-sm font-semibold text-slate-800">{{ __('Select the Customer and Site') }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">{{ __("Choose who you're selling to (Customer) and which Site the goods will be shipped from.") }}</p>
                    </div>
                </li>
                <li class="flex gap-3">
                    <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-brand-800 text-xs font-bold text-white">3</span>
                    <div>
                        <p class="text-sm font-semibold text-slate-800">{{ __('Add items, quantity and unit price') }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">{{ __('Search and pick a Product/Variant, then enter the quantity and selling price per unit. Add a discount if there is one.') }}</p>
                    </div>
                </li>
                <li class="flex gap-3">
                    <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-brand-800 text-xs font-bold text-white">4</span>
                    <div>
                        <p class="text-sm font-semibold text-slate-800">{{ __('Save — the status becomes "Pending"') }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">{{ __("This step only creates the Sales Order — it has no effect on stock or accounts yet. Those update only once the goods are actually delivered.") }}</p>
                    </div>
                </li>
                <li class="flex gap-3">
                    <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-brand-800 text-xs font-bold text-white">5</span>
                    <div>
                        <p class="text-sm font-semibold text-slate-800">{{ __('When it\'s time to ship → Deliver') }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">{{ __('Open the sale from the Sales List and click "Deliver", then enter how much is actually being shipped. If everything doesn\'t go out at once, deliver what you have (Partial Delivery) and deliver the rest later.') }}</p>
                    </div>
                </li>
                <li class="flex gap-3">
                    <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-brand-800 text-xs font-bold text-white">6</span>
                    <div>
                        <p class="text-sm font-semibold text-slate-800">{{ __('Return goods if needed (Sale Return)') }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">{{ __('If a customer returns delivered goods, open that sale, go to "Return Items", enter how much is being returned, and submit.') }}</p>
                    </div>
                </li>
                <li class="flex gap-3">
                    <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-brand-800 text-xs font-bold text-white">7</span>
                    <div>
                        <p class="text-sm font-semibold text-slate-800">{{ __('Cancel (if needed)') }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">{{ __('A sale can be cancelled while it is still Pending or Partial. Anything already delivered is unaffected — only the remaining portion is cancelled.') }}</p>
                    </div>
                </li>
            </ol>
        </div>

        {{-- Sale Return --}}
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <div class="mb-4">
                <h3 class="font-bold text-brand-900">{{ __('2. How to process a Sale Return') }}</h3>
                <p class="text-xs text-slate-400">{{ __('Where to record it when a customer returns goods that were sold') }}</p>
            </div>

            <div class="rounded-xl bg-amber-50 ring-1 ring-amber-200 px-4 py-3 mb-4">
                <p class="text-xs text-amber-800">{{ __('Note: there is no separate "Sale Return" menu item — this is done from inside each Sale\'s own page.') }}</p>
            </div>

            <ol class="space-y-4">
                <li class="flex gap-3">
                    <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-brand-800 text-xs font-bold text-white">1</span>
                    <div>
                        <p class="text-sm font-semibold text-slate-800">{{ __('Sales menu → Sales List') }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">{{ __('Find the sale you want to process a return for and open it.') }}</p>
                    </div>
                </li>
                <li class="flex gap-3">
                    <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-brand-800 text-xs font-bold text-white">2</span>
                    <div>
                        <p class="text-sm font-semibold text-slate-800">{{ __('The "Return Items" button inside the Sale') }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">{{ __('Once a sale has been delivered, you will see a "Return Items" button on its page — click it.') }}</p>
                    </div>
                </li>
                <li class="flex gap-3">
                    <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-brand-800 text-xs font-bold text-white">3</span>
                    <div>
                        <p class="text-sm font-semibold text-slate-800">{{ __('Enter the return quantity and submit') }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">{{ __('Enter the return quantity for each item — you cannot return more than what was delivered. Submitting updates stock and accounts (reduces Accounts Receivable) automatically.') }}</p>
                    </div>
                </li>
            </ol>
        </div>

        {{-- Customer Payment --}}
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <div class="mb-4">
                <h3 class="font-bold text-brand-900">{{ __('3. Customer Payment (collecting money from a customer)') }}</h3>
                <p class="text-xs text-slate-400">{{ __("Steps to record a payment against a customer's outstanding balance") }}</p>
            </div>

            <div class="rounded-xl bg-amber-50 ring-1 ring-amber-200 px-4 py-3 mb-4">
                <p class="text-xs text-amber-800">
                    {{ __('Note: to collect money from a customer, go to "Collections" under the Accounts menu — "Payments" in the same menu is for paying suppliers, not for collecting from customers.') }}
                </p>
            </div>

            <ol class="space-y-4">
                <li class="flex gap-3">
                    <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-brand-800 text-xs font-bold text-white">1</span>
                    <div>
                        <p class="text-sm font-semibold text-slate-800">{{ __('Accounts menu → Collections') }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">{{ __('Click "Collections" in the Accounts dropdown in the sidebar, then go to "New Collection".') }}</p>
                    </div>
                </li>
                <li class="flex gap-3">
                    <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-brand-800 text-xs font-bold text-white">2</span>
                    <div>
                        <p class="text-sm font-semibold text-slate-800">{{ __('Select the Customer') }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">{{ __('Choose the customer who is making the payment — their outstanding balance (Due) will be shown.') }}</p>
                    </div>
                </li>
                <li class="flex gap-3">
                    <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-brand-800 text-xs font-bold text-white">3</span>
                    <div>
                        <p class="text-sm font-semibold text-slate-800">{{ __('Enter the amount and payment method') }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">{{ __('Enter how much is being collected and select which Bank Account/Cash it is being deposited into.') }}</p>
                    </div>
                </li>
                <li class="flex gap-3">
                    <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-brand-800 text-xs font-bold text-white">4</span>
                    <div>
                        <p class="text-sm font-semibold text-slate-800">{{ __('Save the collection') }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">{{ __('As soon as it is saved, the customer\'s Accounts Receivable balance is automatically reduced and a Collection Receipt is created, which can be printed.') }}</p>
                    </div>
                </li>
            </ol>
        </div>

    </div>
</x-app-layout>
