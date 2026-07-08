<x-app-layout>
    <x-slot name="title">{{ __('Barcode') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('Barcode') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ __('Generate and print a barcode label for a product') }}</p>
        </div>
    </x-slot>

    <div x-data="barcodeForm(@js($products), @js($companyName))" class="space-y-6">
        <div class="no-print rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 p-6 max-w-2xl">
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Product') }}</label>
                    <select x-model="productId"
                            class="w-full rounded-lg border-slate-200 text-sm focus:border-accent-500 focus:ring-accent-500">
                        <option value="">{{ __('Select a product…') }}</option>
                        <template x-for="p in products" :key="p.id">
                            <option :value="p.id" x-text="p.name"></option>
                        </template>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Format') }}</label>
                    <select x-model="format"
                            class="w-full rounded-lg border-slate-200 text-sm focus:border-accent-500 focus:ring-accent-500">
                        <option value="roll">{{ __('Sticker Roll') }}</option>
                        <option value="a4">{{ __('A4 Sheet') }}</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Quantity') }}</label>
                    <input type="number" min="1" x-model.number="quantity"
                           class="w-full rounded-lg border-slate-200 text-sm focus:border-accent-500 focus:ring-accent-500">
                </div>
            </div>

            <div class="mt-5 flex items-center gap-3">
                <button type="button" @click="submit()" :disabled="!productId"
                        class="inline-flex items-center gap-2 rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 disabled:opacity-40 disabled:cursor-not-allowed">
                    {{ __('Generate') }}
                </button>
                <button type="button" x-show="submitted" @click="window.print()"
                        class="inline-flex items-center gap-2 rounded-lg bg-accent-500 px-4 py-2 text-sm font-semibold text-white hover:bg-accent-600"
                        style="display: none;">
                    {{ __('Print') }}
                </button>
            </div>
        </div>

        <div x-show="submitted" class="print-area" style="display: none;">
            <div class="labels" :class="format === 'a4' ? 'labels-a4' : 'labels-roll'">
                <template x-for="n in quantity" :key="n">
                    <div class="label">
                        <p class="label-company" x-text="companyName"></p>
                        <svg></svg>
                        <p class="label-name" x-text="product?.name"></p>
                        <p class="label-price" x-text="'৳' + product?.price"></p>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <style>
        .label {
            background: white;
            border: 1px dashed #cbd5e1;
            padding: 3mm;
            text-align: center;
            width: 50mm;
            overflow: hidden;
        }
        .label svg {
            display: block;
            width: 100%;
            height: auto;
        }
        .labels-roll { display: flex; flex-direction: column; align-items: flex-start; gap: 4mm; }
        .labels-a4 {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(48mm, 1fr));
            gap: 4mm;
            width: 100%;
        }
        .labels-a4 .label { width: 100%; }
        .label-company {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: #475569;
        }
        .label-name {
            margin-top: 2px;
            font-size: 10px;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .label-price { font-size: 11px; font-weight: 700; }

        @media print {
            body * { visibility: hidden; }
            .print-area, .print-area * { visibility: visible; }
            .print-area {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
            }
            .label { border: none; page-break-inside: avoid; }
            .labels-roll .label { page-break-after: always; }
            .labels-a4 { grid-template-columns: repeat(3, 1fr); }
            @page { margin: 10mm; }
        }
    </style>
</x-app-layout>
