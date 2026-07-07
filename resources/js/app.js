import './bootstrap';

import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import Chart from 'chart.js/auto';

Alpine.plugin(collapse);

window.Alpine = Alpine;
window.Chart = Chart;

Chart.defaults.font.family = 'Figtree, sans-serif';
Chart.defaults.color = '#64748b';

Alpine.start();
