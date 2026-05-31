// Shared Chart.js defaults: colors, fonts, dark mode, animations

export function isDarkMode() {
    return document.documentElement.classList.contains('dark');
}

export function getTextColor() {
    return isDarkMode() ? '#94a3b8' : '#64748b';
}

export function getGridColor() {
    return isDarkMode() ? 'rgba(51,65,85,0.3)' : 'rgba(226,232,240,0.7)';
}

export function getFontFamily() {
    return getComputedStyle(document.documentElement).getPropertyValue('--font-family') || 'Inter, sans-serif';
}

export const PALETTE = {
    emerald: 'rgb(16, 185, 129)',
    teal: 'rgb(20, 184, 166)',
    cyan: 'rgb(6, 182, 212)',
    blue: 'rgb(59, 130, 246)',
    indigo: 'rgb(99, 102, 241)',
    violet: 'rgb(139, 92, 246)',
    purple: 'rgb(168, 85, 247)',
    amber: 'rgb(245, 158, 11)',
    orange: 'rgb(251, 146, 60)',
    rose: 'rgb(244, 63, 94)',
    slate: 'rgb(100, 116, 139)',
};

export const PALETTE_ALPHA = {
    emerald: 'rgba(16, 185, 129, 0.25)',
    teal: 'rgba(20, 184, 166, 0.25)',
    blue: 'rgba(59, 130, 246, 0.25)',
    indigo: 'rgba(99, 102, 241, 0.25)',
    violet: 'rgba(139, 92, 246, 0.25)',
    amber: 'rgba(245, 158, 11, 0.25)',
    rose: 'rgba(244, 63, 94, 0.25)',
};

export function getBaseOptions(extra = {}) {
    const dark = isDarkMode();
    return {
        responsive: true,
        maintainAspectRatio: false,
        animation: {
            duration: 800,
            easing: 'easeOutQuart',
        },
        interaction: {
            intersect: false,
            mode: 'index',
        },
        plugins: {
            legend: {
                labels: {
                    color: getTextColor(),
                    font: { family: getFontFamily(), size: 11, weight: '600' },
                    usePointStyle: true,
                    pointStyleWidth: 8,
                    padding: 20,
                },
            },
            tooltip: {
                backgroundColor: dark ? 'rgba(15,23,42,0.95)' : 'rgba(255,255,255,0.95)',
                titleColor: dark ? '#f1f5f9' : '#0f172a',
                bodyColor: dark ? '#cbd5e1' : '#475569',
                borderColor: dark ? 'rgba(51,65,85,0.6)' : 'rgba(226,232,240,0.8)',
                borderWidth: 1,
                padding: 12,
                cornerRadius: 12,
                titleFont: { family: getFontFamily(), size: 12, weight: '700' },
                bodyFont: { family: getFontFamily(), size: 11 },
                displayColors: false,
            },
        },
        scales: {
            x: {
                grid: { color: getGridColor(), drawBorder: false },
                ticks: { color: getTextColor(), font: { family: getFontFamily(), size: 10 } },
            },
            y: {
                grid: { color: getGridColor(), drawBorder: false },
                ticks: { color: getTextColor(), font: { family: getFontFamily(), size: 10 }, callback: (v) => 'S/ ' + v },
                beginAtZero: true,
            },
        },
        ...extra,
    };
}
