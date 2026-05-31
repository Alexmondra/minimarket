import Chart from 'chart.js/auto';
import './charts/chart-registry.js';
import './charts/chart-helpers.js';

// Make Chart available globally for the registry
window.Chart = Chart;

Chart.defaults.font.family = 'Inter, system-ui, sans-serif';
