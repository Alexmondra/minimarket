import { isDarkMode, getTextColor, getGridColor, getFontFamily } from './chart-helpers.js';

// Global Chart.js registry to avoid duplicate instances on Livewire re-render
const chartInstances = {};

// Enrich config with premium styles, gradients, and custom tooltips
function enrichChartConfig(id, config, ctx) {
    if (!ctx) return config;
    const dark = isDarkMode();
    const txtColor = getTextColor();
    const gridColor = getGridColor();
    const fontFam = getFontFamily();

    // 1. Base options structure
    config.options = config.options || {};
    config.options.responsive = true;
    config.options.maintainAspectRatio = false;
    
    // Smooth transitions
    config.options.animation = {
        duration: 1000,
        easing: 'easeOutQuart'
    };

    config.options.plugins = config.options.plugins || {};
    
    // Premium SaaS tooltips
    config.options.plugins.tooltip = {
        backgroundColor: dark ? 'rgba(15, 23, 42, 0.95)' : 'rgba(255, 255, 255, 0.95)',
        titleColor: dark ? '#f1f5f9' : '#0f172a',
        bodyColor: dark ? '#cbd5e1' : '#475569',
        borderColor: dark ? 'rgba(51, 65, 85, 0.5)' : 'rgba(226, 232, 240, 0.8)',
        borderWidth: 1,
        padding: 12,
        cornerRadius: 12,
        titleFont: { family: fontFam, size: 12, weight: '700' },
        bodyFont: { family: fontFam, size: 11 },
        displayColors: id.includes('metodos_pago'), // only show colors in payment methods ring
        boxWidth: 8,
        boxHeight: 8,
        boxPadding: 4,
        ...config.options.plugins.tooltip
    };

    // Default legend config (clean look)
    config.options.plugins.legend = config.options.plugins.legend || {};
    if (config.options.plugins.legend.display === undefined) {
        config.options.plugins.legend.display = false;
    }
    config.options.plugins.legend.labels = {
        color: txtColor,
        font: { family: fontFam, size: 11, weight: '600' },
        usePointStyle: true,
        pointStyleWidth: 8,
        padding: 15,
        ...config.options.plugins.legend.labels
    };

    config.options.scales = config.options.scales || {};

    const scaleDefaults = (scaleId, isCurrency) => {
        config.options.scales[scaleId] = config.options.scales[scaleId] || {};
        config.options.scales[scaleId].grid = {
            color: gridColor,
            drawBorder: false,
            ...config.options.scales[scaleId].grid
        };
        config.options.scales[scaleId].ticks = {
            color: txtColor,
            font: { family: fontFam, size: 10 },
            ...config.options.scales[scaleId].ticks
        };
        if (isCurrency && scaleId === 'y') {
            config.options.scales[scaleId].ticks.callback = (v) => 'S/ ' + Number(v).toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
    };

    // 2. Specific chart modifications based on ID
    if (id.includes('ventas_dia')) {
        // Line chart
        scaleDefaults('x', false);
        scaleDefaults('y', true);
        config.options.interaction = { intersect: false, mode: 'index' };
        
        if (config.data && config.data.datasets && config.data.datasets[0]) {
            const ds = config.data.datasets[0];
            ds.borderColor = 'rgb(16, 185, 129)'; // Emerald border
            ds.borderWidth = 3;
            ds.tension = 0.4;
            ds.fill = true;
            
            // Soft emerald desaturated gradient fill
            const grad = ctx.createLinearGradient(0, 0, 0, ctx.canvas.clientHeight || 250);
            grad.addColorStop(0, 'rgba(16, 185, 129, 0.22)');
            grad.addColorStop(1, 'rgba(16, 185, 129, 0)');
            ds.backgroundColor = grad;

            // Sleek hover point animation
            ds.pointRadius = 0;
            ds.pointHitRadius = 20;
            ds.pointHoverRadius = 6;
            ds.pointHoverBorderWidth = 3;
            ds.pointHoverBorderColor = '#fff';
            ds.pointHoverBackgroundColor = 'rgb(16, 185, 129)';
        }

        config.options.plugins.tooltip.callbacks = {
            label: (context) => ` Ventas: S/ ${context.parsed.y.toFixed(2)}`
        };

    } else if (id.includes('ventas_mes')) {
        // Vertical Bar Chart
        scaleDefaults('x', false);
        scaleDefaults('y', true);

        if (config.data && config.data.datasets && config.data.datasets[0]) {
            const ds = config.data.datasets[0];
            ds.borderWidth = 0;
            ds.borderRadius = { topLeft: 6, topRight: 6, bottomLeft: 0, bottomRight: 0 };
            
            // Smooth Indigo gradient
            const grad = ctx.createLinearGradient(0, 0, 0, ctx.canvas.clientHeight || 250);
            grad.addColorStop(0, 'rgba(99, 102, 241, 0.85)');
            grad.addColorStop(1, 'rgba(99, 102, 241, 0.15)');
            ds.backgroundColor = grad;
        }

        config.options.plugins.tooltip.callbacks = {
            label: (context) => ` Ventas: S/ ${context.parsed.y.toFixed(2)}`
        };

    } else if (id.includes('metodos_pago')) {
        // Doughnut Chart
        delete config.options.scales; // Doughnut doesn't use scales
        config.options.plugins.legend.display = true;
        config.options.plugins.legend.position = 'right';

        if (config.data && config.data.datasets && config.data.datasets[0]) {
            const ds = config.data.datasets[0];
            ds.spacing = 5;
            ds.borderRadius = 6;
            ds.borderWidth = dark ? 2 : 1;
            ds.borderColor = dark ? '#0f172a' : '#fff'; // card bg color matching
        }

        config.options.cutout = '75%';

        config.options.plugins.tooltip.callbacks = {
            label: function(context) {
                const label = context.label || '';
                const value = context.parsed || 0;
                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                return ` ${label}: S/ ${value.toFixed(2)} (${percentage}%)`;
            }
        };

    } else if (id.includes('ganancias')) {
        // Double Area Chart (Ingresos vs Ganancias)
        scaleDefaults('x', false);
        scaleDefaults('y', true);
        config.options.plugins.legend.display = true;
        config.options.plugins.legend.position = 'top';
        config.options.interaction = { intersect: false, mode: 'index' };

        if (config.data && config.data.datasets) {
            // Ingresos (Blue gradient)
            if (config.data.datasets[0]) {
                const ds = config.data.datasets[0];
                ds.borderColor = 'rgb(59, 130, 246)';
                ds.borderWidth = 2.5;
                ds.tension = 0.4;
                ds.fill = true;
                
                const gradBlue = ctx.createLinearGradient(0, 0, 0, ctx.canvas.clientHeight || 250);
                gradBlue.addColorStop(0, 'rgba(59, 130, 246, 0.15)');
                gradBlue.addColorStop(1, 'rgba(59, 130, 246, 0)');
                ds.backgroundColor = gradBlue;

                ds.pointRadius = 0;
                ds.pointHitRadius = 15;
                ds.pointHoverRadius = 5;
                ds.pointHoverBorderWidth = 2.5;
                ds.pointHoverBorderColor = '#fff';
                ds.pointHoverBackgroundColor = 'rgb(59, 130, 246)';
            }
            // Ganancias (Teal gradient)
            if (config.data.datasets[1]) {
                const ds = config.data.datasets[1];
                ds.borderColor = 'rgb(20, 184, 166)';
                ds.borderWidth = 3;
                ds.tension = 0.4;
                ds.fill = true;
                
                const gradTeal = ctx.createLinearGradient(0, 0, 0, ctx.canvas.clientHeight || 250);
                gradTeal.addColorStop(0, 'rgba(20, 184, 166, 0.22)');
                gradTeal.addColorStop(1, 'rgba(20, 184, 166, 0)');
                ds.backgroundColor = gradTeal;

                ds.pointRadius = 0;
                ds.pointHitRadius = 15;
                ds.pointHoverRadius = 6;
                ds.pointHoverBorderWidth = 3;
                ds.pointHoverBorderColor = '#fff';
                ds.pointHoverBackgroundColor = 'rgb(20, 184, 166)';
            }
        }

        config.options.plugins.tooltip.callbacks = {
            label: (context) => ` ${context.dataset.label}: S/ ${context.parsed.y.toFixed(2)}`
        };

    } else if (id.includes('top_productos')) {
        // Horizontal Bar Chart
        config.options.indexAxis = 'y';
        
        scaleDefaults('x', false); // quantities on X
        scaleDefaults('y', false); // product names on Y

        config.options.scales.x.ticks.precision = 0;
        config.options.scales.x.grid.display = false;
        
        config.options.scales.y.grid.display = false;
        config.options.scales.y.ticks.callback = function(value, index) {
            if (config.data && config.data.labels) {
                const label = config.data.labels[index];
                if (label && label.length > 20) {
                    return label.substring(0, 17) + '...';
                }
                return label;
            }
            return value;
        };

        if (config.data && config.data.datasets && config.data.datasets[0]) {
            const ds = config.data.datasets[0];
            ds.borderWidth = 0;
            ds.borderRadius = 4;
            
            // Premium Violet to Pink horizontal gradient
            const grad = ctx.createLinearGradient(0, 0, ctx.canvas.clientWidth || 350, 0);
            grad.addColorStop(0, 'rgba(139, 92, 246, 0.85)');
            grad.addColorStop(1, 'rgba(236, 72, 153, 0.4)');
            ds.backgroundColor = grad;
        }

        config.options.plugins.tooltip.callbacks = {
            label: (context) => ` Ventas: ${context.parsed.x} unidades`
        };
    }

    return config;
}

export function registerChart(id, config) {
    destroyChart(id);
    const canvas = document.getElementById(id);
    if (!canvas) return null;
    const ctx = canvas.getContext('2d');
    
    // Apply premium styling logic
    const enriched = enrichChartConfig(id, config, ctx);
    
    chartInstances[id] = new Chart(ctx, enriched);
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

// Update scales and fonts dynamically on theme changes
export function updateChartThemes() {
    const dark = isDarkMode();
    const txtColor = getTextColor();
    const gridColor = getGridColor();
    const fontFam = getFontFamily();

    Object.keys(chartInstances).forEach(id => {
        const chart = chartInstances[id];
        if (!chart) return;

        if (chart.options.scales) {
            Object.keys(chart.options.scales).forEach(scaleId => {
                const scale = chart.options.scales[scaleId];
                if (scale.grid) {
                    scale.grid.color = gridColor;
                }
                if (scale.ticks) {
                    scale.ticks.color = txtColor;
                    scale.ticks.font = { ...scale.ticks.font, family: fontFam };
                }
            });
        }

        if (chart.options.plugins) {
            if (chart.options.plugins.legend && chart.options.plugins.legend.labels) {
                chart.options.plugins.legend.labels.color = txtColor;
                chart.options.plugins.legend.labels.font = {
                    ...chart.options.plugins.legend.labels.font,
                    family: fontFam
                };
            }
            if (chart.options.plugins.tooltip) {
                chart.options.plugins.tooltip.backgroundColor = dark ? 'rgba(15, 23, 42, 0.95)' : 'rgba(255, 255, 255, 0.95)';
                chart.options.plugins.tooltip.titleColor = dark ? '#f1f5f9' : '#0f172a';
                chart.options.plugins.tooltip.bodyColor = dark ? '#cbd5e1' : '#475569';
                chart.options.plugins.tooltip.borderColor = dark ? 'rgba(51, 65, 85, 0.5)' : 'rgba(226, 232, 240, 0.8)';
            }
        }

        if (id.includes('metodos_pago') && chart.data.datasets && chart.data.datasets[0]) {
            chart.data.datasets[0].borderColor = dark ? '#0f172a' : '#fff';
            chart.data.datasets[0].borderWidth = dark ? 2 : 1;
        }

        chart.update();
    });
}

// Alpine.js directive for chart components
document.addEventListener('alpine:init', () => {
    Alpine.data('chartComponent', (chartId, config) => ({
        chart: null,
        init() {
            this.$nextTick(() => {
                this.chart = registerChart(chartId, config);
            });
            // Watch for changes in Livewire's chartConfig property
            if (this.$wire) {
                this.$wire.$watch('chartConfig', (newConfig) => {
                    if (newConfig && this.chart) {
                        this.chart.data = newConfig.data;
                        if (newConfig.options) {
                            this.chart.options = { ...this.chart.options, ...newConfig.options };
                        }
                        this.chart.update();
                    }
                });
            }
        },
        destroy() {
            if (this.chart) {
                this.chart.destroy();
                this.chart = null;
            }
        }
    }));
});

// Watch html theme changes
if (typeof window !== 'undefined') {
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.attributeName === 'class') {
                setTimeout(updateChartThemes, 50);
            }
        });
    });
    observer.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['class']
    });
}

// Listen for Livewire navigation to clean up charts
document.addEventListener('livewire:navigating', () => {
    destroyAll();
});

