import './bootstrap';

import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import Chart from 'chart.js/auto';

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

window.Alpine = Alpine;
window.Chart = Chart;

Chart.defaults.font.family = 'Figtree, sans-serif';
Chart.defaults.color = '#64748b';

Alpine.start();
