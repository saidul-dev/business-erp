import './bootstrap';

import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import Chart from 'chart.js/auto';
import JsBarcode from 'jsbarcode';

Alpine.plugin(collapse);

Alpine.store('sidebar', {
    collapsed: localStorage.getItem('sidebar-collapsed') === '1',
    toggle() {
        this.collapsed = !this.collapsed;
        localStorage.setItem('sidebar-collapsed', this.collapsed ? '1' : '0');
    },
    expand() {
        this.collapsed = false;
        localStorage.setItem('sidebar-collapsed', '0');
    },
});

Alpine.data('barcodeForm', (products, companyName) => ({
    products,
    companyName,
    productId: '',
    format: 'roll',
    quantity: 1,
    submitted: false,

    get product() {
        return this.products.find((p) => p.id == this.productId) || null;
    },

    submit() {
        if (!this.productId) {
            return;
        }
        if (!this.quantity || this.quantity < 1) {
            this.quantity = 1;
        }
        this.submitted = true;

        this.$nextTick(() => {
            this.$root.querySelectorAll('.label svg').forEach((el) => this.renderBarcode(el));
        });
    },

    renderBarcode(el) {
        if (!this.product) {
            return;
        }
        JsBarcode(el, this.product.value, {
            format: 'CODE128',
            displayValue: false,
            height: 40,
            margin: 4,
        });
    },
}));

Alpine.data('productForm', (initial) => ({
    ...initial,
    // { [attributeId]: { [valueId]: label } } — selected values per attribute
    selected: window.__selectedValues || {},
    // Existing variants (edit mode) injected from the server, if any
    variants: window.__existingVariants || [],

    toggleValue(attrId, valueId, label, checked) {
        this.selected[attrId] = this.selected[attrId] || {};
        if (checked) {
            this.selected[attrId][valueId] = label;
        } else {
            delete this.selected[attrId][valueId];
            if (Object.keys(this.selected[attrId]).length === 0) delete this.selected[attrId];
        }
    },

    generateVariants() {
        const attrIds = Object.keys(this.selected);
        if (attrIds.length === 0) { this.variants = []; return; }

        // Cartesian product of selected values across attributes.
        let combos = [{ values: {}, labels: [] }];
        attrIds.forEach((attrId) => {
            const next = [];
            Object.entries(this.selected[attrId]).forEach(([valueId, label]) => {
                combos.forEach((combo) => {
                    next.push({
                        values: { ...combo.values, [attrId]: Number(valueId) },
                        labels: [...combo.labels, label],
                    });
                });
            });
            combos = next;
        });

        // Merge: keep existing rows whose value-combo matches; add new ones.
        const keyOf = (values) => Object.keys(values).sort().map((k) => k + ':' + values[k]).join('|');
        const existingByKey = {};
        this.variants.forEach((v) => { existingByKey[keyOf(v.values)] = v; });

        this.variants = combos.map((combo) => {
            const key = keyOf(combo.values);
            if (existingByKey[key]) return existingByKey[key];
            return {
                key,
                id: null,
                label: combo.labels.join(' / '),
                values: combo.values,
                sku: '',
                barcode: '',
                selling_price: this.$root.querySelector('#selling_price')?.value || '',
                estimated_cost: '',
                status: true,
            };
        });
    },
}));

Alpine.data('transferCart', (initial) => ({
    itemOptions: initial.itemOptions || [],
    items: [],
    query: '',
    open: false,

    // Already-added items drop out of the pickable pool — a transfer line
    // is one row per item, so re-picking just means "edit the existing row".
    get filtered() {
        const chosen = new Set(this.items.map((i) => i.id));
        const pool = this.itemOptions.filter((o) => !chosen.has(o.id));
        if (!this.query) return pool;
        const q = this.query.toLowerCase();
        return pool.filter((o) => o.name.toLowerCase().includes(q));
    },

    addItem(opt) {
        this.items.push({
            id: opt.id,
            name: opt.name,
            unit: opt.unit,
            balance: opt.balance,
            trackBatch: opt.trackBatch,
            trackExpiry: opt.trackExpiry,
            trackSerial: opt.trackSerial,
            quantity: '',
            batch_no: '',
            expiry_date: '',
            serial_no: '',
        });
        this.query = '';
        this.open = false;
    },

    removeItem(id) {
        this.items = this.items.filter((i) => i.id !== id);
    },
}));

Alpine.data('purchaseCart', (initial) => ({
    itemOptions: initial.itemOptions || [],
    items: [],
    query: '',
    open: false,

    get filtered() {
        const chosen = new Set(this.items.map((i) => i.id));
        const pool = this.itemOptions.filter((o) => !chosen.has(o.id));
        if (!this.query) return pool;
        const q = this.query.toLowerCase();
        return pool.filter((o) => o.name.toLowerCase().includes(q));
    },

    get total() {
        return this.items.reduce((sum, i) => sum + (Number(i.quantity) || 0) * (Number(i.unit_cost) || 0), 0);
    },

    addItem(opt) {
        this.items.push({
            id: opt.id,
            name: opt.name,
            unit: opt.unit,
            quantity: '',
            unit_cost: opt.cost || '',
        });
        this.query = '';
        this.open = false;
    },

    removeItem(id) {
        this.items = this.items.filter((i) => i.id !== id);
    },
}));

Alpine.data('saleCart', (initial) => ({
    itemOptions: initial.itemOptions || [],
    items: [],
    query: '',
    open: false,
    discount: '',

    get filtered() {
        const chosen = new Set(this.items.map((i) => i.id));
        const pool = this.itemOptions.filter((o) => !chosen.has(o.id));
        if (!this.query) return pool;
        const q = this.query.toLowerCase();
        return pool.filter((o) => o.name.toLowerCase().includes(q));
    },

    get subtotal() {
        return this.items.reduce((sum, i) => sum + (Number(i.quantity) || 0) * (Number(i.unit_price) || 0), 0);
    },

    get total() {
        return Math.max(this.subtotal - (Number(this.discount) || 0), 0);
    },

    addItem(opt) {
        this.items.push({
            id: opt.id,
            name: opt.name,
            unit: opt.unit,
            quantity: '',
            unit_price: opt.price || '',
        });
        this.query = '';
        this.open = false;
    },

    removeItem(id) {
        this.items = this.items.filter((i) => i.id !== id);
    },
}));

window.Alpine = Alpine;
window.Chart = Chart;

Chart.defaults.font.family = 'Figtree, sans-serif';
Chart.defaults.color = '#64748b';

Alpine.start();
