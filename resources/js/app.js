import './bootstrap';

import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import Chart from 'chart.js/auto';
import JsBarcode from 'jsbarcode';
import 'trix';

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

Alpine.data('productGallery', (initial) => ({
    existing: initial.existing || [],
    newFiles: [],

    // Mirrors the real file input exactly, since re-opening it replaces
    // (not appends to) its selection — appending here would show previews
    // for files that no longer submit with the form.
    addFiles(fileList) {
        this.newFiles = Array.from(fileList).map((file) => ({
            file,
            url: URL.createObjectURL(file),
        }));
    },

    removeExisting(id) {
        this.existing = this.existing.filter((img) => img.id !== id);
    },

    removeNew(index) {
        this.newFiles.splice(index, 1);

        // A file input can't drop a single entry from its FileList directly —
        // rebuild it via DataTransfer so what's previewed still matches what
        // will actually submit.
        const transfer = new DataTransfer();
        this.newFiles.forEach((item) => transfer.items.add(item.file));
        this.$refs.galleryInput.files = transfer.files;
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
    items: initial.items || [],
    query: '',
    open: false,
    discount: initial.discount ?? '',

    get filtered() {
        const chosen = new Set(this.items.map((i) => i.id));
        const pool = this.itemOptions.filter((o) => !chosen.has(o.id));
        if (!this.query) return pool;
        const q = this.query.toLowerCase();
        return pool.filter((o) => o.name.toLowerCase().includes(q));
    },

    get subtotal() {
        return this.items.reduce((sum, i) => sum + (Number(i.quantity) || 0) * (Number(i.unit_cost) || 0), 0);
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
    items: initial.items || [],
    query: '',
    open: false,
    discount: initial.discount ?? '',

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

Alpine.data('requisitionCart', (initial) => ({
    itemOptions: initial.itemOptions || [],
    items: initial.items || [],
    query: '',
    open: false,

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
            quantity: '',
        });
        this.query = '';
        this.open = false;
    },

    removeItem(id) {
        this.items = this.items.filter((i) => i.id !== id);
    },
}));

Alpine.data('quotationCart', (initial) => ({
    itemOptions: initial.itemOptions || [],
    items: initial.items || [],
    query: '',
    open: false,
    discount: initial.discount ?? '',

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

Alpine.data('posTerminal', (initial) => ({
    accounts: initial.accounts || [],
    productsUrl: initial.productsUrl,
    checkoutUrl: initial.checkoutUrl,
    customersUrl: initial.customersUrl,

    query: '',
    results: [],
    searching: false,
    searchTimer: null,

    cart: [],
    customer: null,
    discount: '',
    selectedAccountId: initial.accounts?.[0]?.id ?? null,
    cashTendered: '',
    referenceNo: '',

    newCustomerName: '',
    newCustomerPhone: '',
    customerError: '',
    savingCustomer: false,

    submitting: false,
    checkoutError: '',

    get selectedAccount() {
        return this.accounts.find((a) => a.id === this.selectedAccountId) || null;
    },

    get isCash() {
        return !!this.selectedAccount?.is_cash;
    },

    get subtotal() {
        return this.cart.reduce((sum, i) => sum + (Number(i.quantity) || 0) * (Number(i.price) || 0), 0);
    },

    get total() {
        return Math.max(this.subtotal - (Number(this.discount) || 0), 0);
    },

    get change() {
        return Math.max((Number(this.cashTendered) || 0) - this.total, 0);
    },

    search() {
        clearTimeout(this.searchTimer);
        const q = this.query.trim();
        if (!q) {
            this.results = [];
            return;
        }
        this.searchTimer = setTimeout(() => {
            this.searching = true;
            axios.get(this.productsUrl, { params: { q } })
                .then((res) => { this.results = res.data.results; })
                .finally(() => { this.searching = false; });
        }, 250);
    },

    handleEnter() {
        if (this.results.length === 1) {
            this.addResult(this.results[0]);
        }
    },

    addResult(item) {
        const existing = this.cart.find((i) => i.id === item.id);
        if (existing) {
            if (existing.quantity < item.available_qty) existing.quantity += 1;
        } else if (item.available_qty > 0) {
            this.cart.push({
                id: item.id,
                name: item.name,
                unit: item.unit,
                price: item.price,
                quantity: 1,
                available_qty: item.available_qty,
            });
        }
        this.query = '';
        this.results = [];
        this.$refs.scanInput?.focus();
    },

    incQty(row) {
        if (row.quantity < row.available_qty) row.quantity += 1;
    },

    decQty(row) {
        if (row.quantity > 1) row.quantity -= 1;
    },

    removeItem(id) {
        this.cart = this.cart.filter((i) => i.id !== id);
    },

    selectWalkIn() {
        this.customer = null;
    },

    openNewCustomer() {
        this.newCustomerName = '';
        this.newCustomerPhone = '';
        this.customerError = '';
        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'pos-new-customer' }));
    },

    saveCustomer() {
        this.savingCustomer = true;
        this.customerError = '';
        axios.post(this.customersUrl, { name: this.newCustomerName, phone: this.newCustomerPhone })
            .then((res) => {
                this.customer = res.data;
                window.dispatchEvent(new CustomEvent('close-modal', { detail: 'pos-new-customer' }));
            })
            .catch((err) => {
                this.customerError = err.response?.data?.errors
                    ? Object.values(err.response.data.errors).flat().join(' ')
                    : 'Could not save customer.';
            })
            .finally(() => { this.savingCustomer = false; });
    },

    checkout() {
        if (this.cart.length === 0 || this.submitting) return;
        this.submitting = true;
        this.checkoutError = '';

        axios.post(this.checkoutUrl, {
            party_id: this.customer?.id ?? null,
            discount_amount: this.discount || 0,
            ledger_account_id: this.selectedAccountId,
            cash_tendered: this.isCash ? (this.cashTendered || null) : null,
            reference_no: this.isCash ? null : (this.referenceNo || null),
            items: this.cart.map((i) => ({ item: i.id, quantity: i.quantity })),
        })
            .then((res) => {
                window.open(res.data.receipt_url, '_blank');
                this.cart = [];
                this.customer = null;
                this.discount = '';
                this.cashTendered = '';
                this.referenceNo = '';
                this.$refs.scanInput?.focus();
            })
            .catch((err) => {
                this.checkoutError = err.response?.data?.errors
                    ? Object.values(err.response.data.errors).flat().join(' ')
                    : 'Checkout failed.';
            })
            .finally(() => { this.submitting = false; });
    },
}));

window.Alpine = Alpine;
window.Chart = Chart;

Chart.defaults.font.family = 'Figtree, sans-serif';
Chart.defaults.color = '#64748b';

Alpine.start();
