import Chart from 'chart.js/auto';
import { isDarkMode, getTextColor, getGridColor, getFontFamily } from './chart-helpers.js';

// Global Chart.js registry to avoid duplicate instances on Livewire re-render
const chartInstances = {};

// ─── Smart Dynamic Scale ───────────────────────────────────────────
// Calculates a suggestedMax based on actual data so charts look
// proportional with 1 sale or 10,000 sales — no microscopic bars, no
// broken scales.

function calcSmartMax(dataValues, { currency = false, floor = null } = {}) {
    const nums = dataValues.filter(v => v != null && !isNaN(v) && isFinite(v));
    if (!nums.length) {
        return floor ?? (currency ? 50 : 5);
    }
    const max = Math.max(...nums);
    if (max <= 0) {
        return floor ?? (currency ? 50 : 5);
    }
    const headroom = max * 1.18;
    const effectiveFloor = floor ?? (currency ? 50 : 5);
    return Math.max(headroom, effectiveFloor);
}

// Collect *all* numeric values from every dataset in the config into a
// flat array so we can compute a single smart Y-max for multi-series
// charts (e.g. ganancias has 3 series).

function collectAllValues(config) {
    const all = [];
    if (config?.data?.datasets) {
        for (const ds of config.data.datasets) {
            if (Array.isArray(ds.data)) {
                for (const v of ds.data) {
                    all.push(v);
                }
            }
        }
    }
    return all;
}

// ─── Donut Center Text Plugin ─────────────────────────────────────
// Renders the total in the centre of the doughnut for a premium feel.

const donutCenterPlugin = {
    id: 'donutCenterText',
    afterDraw(chart) {
        const { ctx, chartArea: { width, height, top, left } } = chart;
        if (!width || !height) return;

        const dataset = chart.data.datasets?.[0];
        if (!dataset) return;
        const total = dataset.data.reduce((a, b) => a + b, 0);

        const isColorful = ctx.canvas && ctx.canvas.closest('.colorful-card');
        const isChartDark = isDarkMode() || isColorful;

        ctx.save();
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        const centerX = left + width / 2;
        const centerY = top + height / 2;

        // Label
        ctx.font = `600 10px ${getFontFamily()}`;
        ctx.fillStyle = isColorful ? 'rgba(255, 255, 255, 0.8)' : getTextColor();
        ctx.fillText('Total', centerX, centerY - 8);

        // Value
        ctx.font = `800 14px ${getFontFamily()}`;
        ctx.fillStyle = isChartDark ? '#f1f5f9' : '#0f172a';
        ctx.fillText('S/ ' + Number(total).toLocaleString('es-PE', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }), centerX, centerY + 9);

        ctx.restore();
    }
};

// ─── Enrich Chart Config ──────────────────────────────────────────
// Applies premium SaaS styling, smart scales, and per-chart visual
// identity based on the chart ID.

function enrichChartConfig(id, config, ctx) {
    if (!ctx) return config;
    const isColorful = ctx.canvas && ctx.canvas.closest('.colorful-card');
    const dark = isDarkMode() || isColorful;
    const txtColor = isColorful ? 'rgba(255, 255, 255, 0.85)' : (isDarkMode() ? '#94a3b8' : '#64748b');
    const gridColor = isColorful ? 'rgba(255, 255, 255, 0.12)' : (isDarkMode() ? 'rgba(255, 255, 255, 0.04)' : 'rgba(15, 23, 42, 0.04)');
    const fontFam = getFontFamily();
    const allValues = collectAllValues(config);
    const isCurrency = !id.includes('top_productos');

    // ── Base options ────────────────────────────────────────────
    config.options = config.options || {};
    config.options.responsive = true;
    config.options.maintainAspectRatio = false;
    config.options.animation = {
        duration: 800,
        easing: 'easeOutQuart',
    };

    config.options.plugins = config.options.plugins || {};

    // ── Premium glassmorphism tooltips ──────────────────────────
    config.options.plugins.tooltip = {
        backgroundColor: dark ? 'rgba(15, 23, 42, 0.96)' : 'rgba(255, 255, 255, 0.96)',
        titleColor: dark ? '#f1f5f9' : '#0f172a',
        bodyColor: dark ? '#cbd5e1' : '#475569',
        borderColor: dark ? 'rgba(51, 65, 85, 0.5)' : 'rgba(226, 232, 240, 0.8)',
        borderWidth: 1,
        padding: 12,
        cornerRadius: 11,
        titleFont: { family: fontFam, size: 12, weight: '700' },
        bodyFont: { family: fontFam, size: 11 },
        displayColors: id.includes('metodos_pago') || id.includes('ganancias'),
        boxWidth: 8,
        boxHeight: 8,
        boxPadding: 4,
        ...config.options.plugins.tooltip,
    };

    // ── Legend defaults ─────────────────────────────────────────
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
        ...config.options.plugins.legend.labels,
    };

    config.options.scales = config.options.scales || {};

    function applyScale(scaleId, opts = {}) {
        config.options.scales[scaleId] = config.options.scales[scaleId] || {};
        const s = config.options.scales[scaleId];
        s.grid = {
            color: gridColor,
            drawBorder: false,
            ...s.grid,
        };
        s.ticks = {
            color: txtColor,
            font: { family: fontFam, size: 10 },
            ...s.ticks,
        };
        if (opts.currency && scaleId === 'y') {
            s.ticks.callback = (v) =>
                'S/ ' + Number(v).toLocaleString('es-PE', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0,
                });
        }
        if (opts.beginAtZero !== undefined) s.beginAtZero = opts.beginAtZero;
        if (opts.suggestedMin !== undefined) s.suggestedMin = opts.suggestedMin;
        if (opts.suggestedMax !== undefined) s.suggestedMax = opts.suggestedMax;
    }

    // ═══════════════════════════════════════════════════════════════
    // 1. VENTAS ACUMULADAS DE LA SEMANA — Cumulative Emerald Line
    // ═══════════════════════════════════════════════════════════════
    if (id.includes('ventas_semana')) {
        applyScale('x', {});
        applyScale('y', {
            currency: true,
            beginAtZero: true,
            suggestedMin: 0,
            suggestedMax: calcSmartMax(allValues, { currency: true }),
        });

        config.options.interaction = { intersect: false, mode: 'index' };

        if (config.data?.datasets?.[0]) {
            const ds = config.data.datasets[0];
            ds.borderColor = isColorful ? '#ffffff' : '#10b981';
            ds.borderWidth = 3;
            ds.tension = 0.45;
            ds.fill = true;
            ds.pointRadius = 5;
            ds.pointHitRadius = 20;
            ds.pointHoverRadius = 7;
            ds.pointHoverBorderWidth = 3;
            ds.pointHoverBorderColor = isColorful ? '#047857' : '#fff';
            ds.pointHoverBackgroundColor = isColorful ? '#ffffff' : '#10b981';
            ds.pointBackgroundColor = isColorful ? '#ffffff' : '#10b981';
            ds.pointBorderColor = isColorful ? '#047857' : '#fff';
            ds.pointBorderWidth = 2;
            ds.spanGaps = false; // break line for future days (null)

            const grad = ctx.createLinearGradient(0, 0, 0, ctx.canvas.clientHeight || 250);
            if (isColorful) {
                grad.addColorStop(0, 'rgba(255, 255, 255, 0.28)');
                grad.addColorStop(0.5, 'rgba(255, 255, 255, 0.08)');
                grad.addColorStop(1, 'rgba(255, 255, 255, 0)');
            } else {
                grad.addColorStop(0, 'rgba(16, 185, 129, 0.28)');
                grad.addColorStop(0.5, 'rgba(16, 185, 129, 0.08)');
                grad.addColorStop(1, 'rgba(16, 185, 129, 0)');
            }
            ds.backgroundColor = grad;
        }

        config.options.plugins.tooltip.callbacks = {
            label: (ctx) => {
                const v = ctx.parsed.y;
                if (v == null) return ' Aún sin datos';
                return ` Acumulado: S/ ${v.toFixed(2)}`;
            },
        };
    }

    // ═══════════════════════════════════════════════════════════════
    // 2. VENTAS MENSUALES — Vertical Bar Chart (Indigo)
    // ═══════════════════════════════════════════════════════════════
    else if (id.includes('ventas_mes')) {
        applyScale('x', {});
        applyScale('y', {
            currency: true,
            beginAtZero: true,
            suggestedMin: 0,
            suggestedMax: calcSmartMax(allValues, { currency: true }),
        });

        if (config.data?.datasets?.[0]) {
            const ds = config.data.datasets[0];
            ds.borderWidth = 0;
            ds.borderRadius = { topLeft: 6, topRight: 6, bottomLeft: 0, bottomRight: 0 };
            ds.maxBarThickness = 32;
            ds.borderSkipped = false;

            const grad = ctx.createLinearGradient(0, 0, 0, ctx.canvas.clientHeight || 250);
            if (isColorful) {
                grad.addColorStop(0, 'rgba(255, 255, 255, 0.92)');
                grad.addColorStop(1, 'rgba(255, 255, 255, 0.25)');
                ds.backgroundColor = grad;
                ds.hoverBackgroundColor = 'rgba(255, 255, 255, 1)';
                ds.hoverBorderColor = 'rgba(255, 255, 255, 0.5)';
            } else {
                grad.addColorStop(0, 'rgba(99, 102, 241, 0.92)');
                grad.addColorStop(1, 'rgba(99, 102, 241, 0.18)');
                ds.backgroundColor = grad;
                ds.hoverBackgroundColor = 'rgba(99, 102, 241, 1)';
                ds.hoverBorderColor = 'rgba(255,255,255,0.3)';
            }
            ds.hoverBorderWidth = 2;
        }

        config.options.plugins.tooltip.callbacks = {
            label: (ctx) => {
                if (ctx.parsed.y == null) return ' Sin ventas este día';
                return ` Ventas: S/ ${ctx.parsed.y.toFixed(2)}`;
            },
        };
    }

    // ═══════════════════════════════════════════════════════════════
    // 3. MÉTODOS DE PAGO — Premium Donut
    // ═══════════════════════════════════════════════════════════════
    else if (id.includes('metodos_pago')) {
        delete config.options.scales; // donut has no scales
        config.options.plugins.legend.display = false;
        config.options.cutout = '72%';

        // Inject center-text plugin if not already present
        if (!config.options.plugins[donutCenterPlugin.id]) {
            config.options.plugins[donutCenterPlugin.id] = donutCenterPlugin;
        }

        if (config.data?.datasets?.[0]) {
            const ds = config.data.datasets[0];
            ds.spacing = 6;
            ds.borderRadius = 8;
            ds.borderWidth = isColorful ? 2 : (dark ? 2 : 1.5);
            ds.borderColor = isColorful ? '#6d28d9' : (dark ? '#0f172a' : '#fff');
            ds.hoverBorderWidth = 3;
            ds.hoverBorderColor = isColorful ? '#5b21b6' : (dark ? '#1e293b' : '#f8fafc');

            if (isColorful && Array.isArray(ds.backgroundColor)) {
                const pastelColors = ['#ffffff', '#f472b6', '#a78bfa', '#cbd5e1', '#fbcfe8'];
                ds.backgroundColor = ds.backgroundColor.map((col, idx) => pastelColors[idx % pastelColors.length]);
            }
        }

        config.options.plugins.tooltip.callbacks = {
            label: function (ctx) {
                const label = ctx.label || '';
                if (label === 'Sin datos') {
                    return ' Sin transacciones este mes';
                }
                const value = ctx.parsed || 0;
                const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                const pct = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                return ` ${label}: S/ ${value.toFixed(2)} (${pct}%)`;
            },
        };
    }

    // ═══════════════════════════════════════════════════════════════
    // 4. INGRESOS VS GANANCIA — Overlapping Area Chart
    // ═══════════════════════════════════════════════════════════════
    else if (id.includes('ganancias')) {
        applyScale('x', {});
        applyScale('y', {
            currency: true,
            beginAtZero: true,
            suggestedMin: 0,
            suggestedMax: calcSmartMax(allValues, { currency: true }),
        });

        config.options.plugins.legend.display = true;
        config.options.plugins.legend.position = 'top';
        config.options.plugins.legend.align = 'center';
        config.options.interaction = { intersect: false, mode: 'index' };

        if (config.data?.datasets) {
            // [0] Inversión — Blue, dashed, subtle area
            if (config.data.datasets[0]) {
                const d = config.data.datasets[0];
                d.borderColor = isColorful ? '#ffffff' : '#3b82f6';
                d.borderWidth = 2.5;
                d.borderDash = [6, 3];
                d.tension = 0.4;
                d.fill = true;
                d.pointRadius = 3;
                d.pointHitRadius = 15;
                d.pointHoverRadius = 5;
                d.pointHoverBorderWidth = 2;
                d.pointHoverBorderColor = isColorful ? '#0d9488' : '#fff';
                d.pointHoverBackgroundColor = isColorful ? '#ffffff' : '#3b82f6';
                d.pointBackgroundColor = isColorful ? '#ffffff' : '#3b82f6';
                d.pointBorderColor = isColorful ? '#0d9488' : '#fff';
                d.pointBorderWidth = 1.5;

                const g = ctx.createLinearGradient(0, 0, 0, ctx.canvas.clientHeight || 250);
                g.addColorStop(0, isColorful ? 'rgba(255, 255, 255, 0.15)' : 'rgba(59, 130, 246, 0.10)');
                g.addColorStop(1, 'rgba(59, 130, 246, 0)');
                d.backgroundColor = g;
            }

            // [1] Ventas — Indigo, solid, prominent
            if (config.data.datasets[1]) {
                const d = config.data.datasets[1];
                d.borderColor = isColorful ? '#e0f2fe' : '#6366f1';
                d.borderWidth = 2.5;
                d.tension = 0.4;
                d.fill = true;
                d.pointRadius = 4;
                d.pointHitRadius = 15;
                d.pointHoverRadius = 6;
                d.pointHoverBorderWidth = 2.5;
                d.pointHoverBorderColor = isColorful ? '#0d9488' : '#fff';
                d.pointHoverBackgroundColor = isColorful ? '#e0f2fe' : '#6366f1';
                d.pointBackgroundColor = isColorful ? '#e0f2fe' : '#6366f1';
                d.pointBorderColor = isColorful ? '#0d9488' : '#fff';
                d.pointBorderWidth = 2;

                const g = ctx.createLinearGradient(0, 0, 0, ctx.canvas.clientHeight || 250);
                g.addColorStop(0, isColorful ? 'rgba(224, 242, 254, 0.20)' : 'rgba(99, 102, 241, 0.16)');
                g.addColorStop(1, 'rgba(99, 102, 241, 0)');
                d.backgroundColor = g;
            }

            // [2] Ganancia Real — Teal, thickest, most prominent
            if (config.data.datasets[2]) {
                const d = config.data.datasets[2];
                d.borderColor = isColorful ? '#38bdf8' : '#14b8a6';
                d.borderWidth = 3.5;
                d.tension = 0.4;
                d.fill = true;
                d.pointRadius = 5;
                d.pointHitRadius = 20;
                d.pointHoverRadius = 7;
                d.pointHoverBorderWidth = 3;
                d.pointHoverBorderColor = isColorful ? '#0d9488' : '#fff';
                d.pointHoverBackgroundColor = isColorful ? '#38bdf8' : '#14b8a6';
                d.pointBackgroundColor = isColorful ? '#38bdf8' : '#14b8a6';
                d.pointBorderColor = isColorful ? '#0d9488' : '#fff';
                d.pointBorderWidth = 2.5;

                const g = ctx.createLinearGradient(0, 0, 0, ctx.canvas.clientHeight || 250);
                g.addColorStop(0, isColorful ? 'rgba(56, 189, 248, 0.28)' : 'rgba(20, 184, 166, 0.26)');
                g.addColorStop(1, 'rgba(20, 184, 166, 0)');
                d.backgroundColor = g;
            }
        }

        config.options.plugins.tooltip.callbacks = {
            label: (ctx) => ` ${ctx.dataset.label}: S/ ${ctx.parsed.y.toFixed(2)}`,
        };
    }

    // ═══════════════════════════════════════════════════════════════
    // 5. TOP 10 PRODUCTOS — Horizontal Bar Chart (Violet→Pink)
    // ═══════════════════════════════════════════════════════════════
    else if (id.includes('top_productos')) {
        config.options.indexAxis = 'y';

        applyScale('x', {
            beginAtZero: true,
            suggestedMin: 0,
            suggestedMax: calcSmartMax(allValues, { currency: false, floor: 5 }),
        });
        applyScale('y', {});

        config.options.scales.x.ticks.precision = 0;
        config.options.scales.x.grid.display = false;
        config.options.scales.y.grid.display = false;

        config.options.scales.y.ticks.callback = function (value, index) {
            const label = config.data?.labels?.[index];
            if (label && label.length > 20) {
                return label.substring(0, 17) + '...';
            }
            return label || '';
        };

        if (config.data?.datasets?.[0]) {
            const ds = config.data.datasets[0];
            ds.borderWidth = 0;
            ds.borderRadius = 5;
            ds.maxBarThickness = 22;
            ds.borderSkipped = false;

            const grad = ctx.createLinearGradient(0, 0, ctx.canvas.clientWidth || 350, 0);
            if (isColorful) {
                grad.addColorStop(0, 'rgba(255, 255, 255, 0.95)');
                grad.addColorStop(1, 'rgba(255, 255, 255, 0.35)');
                ds.backgroundColor = grad;
                ds.hoverBackgroundColor = 'rgba(255, 255, 255, 1)';
                ds.hoverBorderColor = 'rgba(255, 255, 255, 0.5)';
            } else {
                grad.addColorStop(0, 'rgba(139, 92, 246, 0.92)');
                grad.addColorStop(0.5, 'rgba(168, 85, 247, 0.7)');
                grad.addColorStop(1, 'rgba(236, 72, 153, 0.45)');
                ds.backgroundColor = grad;
                ds.hoverBackgroundColor = 'rgba(139, 92, 246, 1)';
                ds.hoverBorderColor = 'rgba(255,255,255,0.25)';
            }
            ds.hoverBorderWidth = 1.5;
        }

        config.options.plugins.tooltip.callbacks = {
            label: (ctx) => ` ${ctx.parsed.x} unidades vendidas`,
        };
    }

    return config;
}

// ─── Public API ────────────────────────────────────────────────────

export function registerChart(id, config) {
    destroyChart(id);
    const canvas = document.getElementById(id);
    if (!canvas) return null;
    const ctx = canvas.getContext('2d');
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

// ─── Theme reactivity ─────────────────────────────────────────────

export function updateChartThemes() {
    const dark = isDarkMode();
    const fontFam = getFontFamily();

    Object.keys(chartInstances).forEach(id => {
        const chart = chartInstances[id];
        if (!chart) return;

        const isColorful = chart.canvas && chart.canvas.closest('.colorful-card');
        const isChartDark = dark || isColorful;

        const txtColor = isColorful ? 'rgba(255, 255, 255, 0.85)' : (dark ? '#94a3b8' : '#64748b');
        const gridColor = isColorful ? 'rgba(255, 255, 255, 0.12)' : (dark ? 'rgba(255, 255, 255, 0.04)' : 'rgba(15, 23, 42, 0.04)');

        if (chart.options.scales) {
            Object.keys(chart.options.scales).forEach(scaleId => {
                const scale = chart.options.scales[scaleId];
                if (scale.grid) scale.grid.color = gridColor;
                if (scale.ticks) {
                    scale.ticks.color = txtColor;
                    scale.ticks.font = { ...scale.ticks.font, family: fontFam };
                }
            });
        }

        if (chart.options.plugins) {
            if (chart.options.plugins.legend?.labels) {
                chart.options.plugins.legend.labels.color = txtColor;
                chart.options.plugins.legend.labels.font = {
                    ...chart.options.plugins.legend.labels.font,
                    family: fontFam,
                };
            }
            if (chart.options.plugins.tooltip) {
                chart.options.plugins.tooltip.backgroundColor = isChartDark
                    ? 'rgba(15, 23, 42, 0.96)'
                    : 'rgba(255, 255, 255, 0.96)';
                chart.options.plugins.tooltip.titleColor = isChartDark ? '#f1f5f9' : '#0f172a';
                chart.options.plugins.tooltip.bodyColor = isChartDark ? '#cbd5e1' : '#475569';
                chart.options.plugins.tooltip.borderColor = isChartDark
                    ? 'rgba(51, 65, 85, 0.5)'
                    : 'rgba(226, 232, 240, 0.8)';
            }
        }

        if (id.includes('metodos_pago') && chart.data.datasets?.[0]) {
            chart.data.datasets[0].borderColor = isColorful ? '#6d28d9' : (dark ? '#0f172a' : '#fff');
            chart.data.datasets[0].borderWidth = isColorful ? 2 : (dark ? 2 : 1.5);
        }

        chart.update();
    });
}

// ─── Chart refresh (called when Livewire dispatches chart-refresh) ─

function refreshChart(chartId, newConfig) {
    const chart = chartInstances[chartId];
    if (!chart) return;

    // Strip any Alpine reactive proxy wrappers
    const clean = JSON.parse(JSON.stringify(newConfig));
    const canvas = document.getElementById(chartId);
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    const enriched = enrichChartConfig(chartId, clean, ctx);

    chart.data = enriched.data;
    chart.options = enriched.options;
    chart.update();
}

// ─── Alpine.js directive ──────────────────────────────────────────

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
        },
    }));
});

// ─── Livewire event: chart data changed (from PHP updatedChartConfig) ─

document.addEventListener('livewire:initialized', () => {
    Livewire.on('chart-refresh', (payload) => {
        if (Array.isArray(payload)) {
            // Livewire 3 wraps event params in an array
            for (const item of payload) {
                if (item?.chartId && item?.config) {
                    refreshChart(item.chartId, item.config);
                }
            }
        } else if (payload?.chartId && payload?.config) {
            refreshChart(payload.chartId, payload.config);
        }
    });
});

// ─── Dark-mode observer ──────────────────────────────────────────

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
        attributeFilter: ['class'],
    });
}

// ─── Livewire navigation cleanup ──────────────────────────────────

document.addEventListener('livewire:navigating', () => {
    destroyAll();
});
