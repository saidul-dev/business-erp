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

window.Alpine = Alpine;
window.Chart = Chart;

Chart.defaults.font.family = 'Figtree, sans-serif';
Chart.defaults.color = '#64748b';

Alpine.start();
