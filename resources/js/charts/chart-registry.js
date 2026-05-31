// Global Chart.js registry to avoid duplicate instances on Livewire re-render
const chartInstances = {};

export function registerChart(id, config) {
    destroyChart(id);
    const canvas = document.getElementById(id);
    if (!canvas) return null;
    const ctx = canvas.getContext('2d');
    chartInstances[id] = new Chart(ctx, config);
    return chartInstances[id];
}

export function destroyChart(id) {
    if (chartInstances[id]) {
        chartInstances[id].destroy();
        delete chartInstances[id];
    }
}

export function destroyAll() {
    Object.keys(chartInstances).forEach(destroyChart);
}

// Alpine.js directive for chart components
document.addEventListener('alpine:init', () => {
    Alpine.data('chartComponent', (chartId, config) => ({
        chart: null,
        init() {
            this.$nextTick(() => {
                this.chart = registerChart(chartId, config);
            });
        },
        destroy() {
            if (this.chart) {
                this.chart.destroy();
                this.chart = null;
            }
        }
    }));
});

// Listen for Livewire navigation to clean up charts
document.addEventListener('livewire:navigating', () => {
    destroyAll();
});
