<x-filament-panels::page>
    @php
        $calc = app(\App\Support\Reportes\MetricCalculator::class);
        $data = $calc->gananciasUltimosMeses(12);
        $chartId = 'chart_ganancias_report_' . uniqid();
        $chartConfig = [
            'type' => 'line',
            'data' => [
                'labels' => $data['labels'],
                'datasets' => [
                    ['label' => 'Ingresos', 'data' => $data['ingresos'], 'borderColor' => 'rgb(59,130,246)', 'backgroundColor' => 'rgba(59,130,246,0.06)', 'fill' => true, 'tension' => 0.4, 'borderWidth' => 2, 'pointRadius' => 3],
                    ['label' => 'Ganancia', 'data' => $data['ganancias'], 'borderColor' => 'rgb(16,185,129)', 'backgroundColor' => 'rgba(16,185,129,0.08)', 'fill' => true, 'tension' => 0.4, 'borderWidth' => 3, 'pointRadius' => 4],
                ],
            ],
        ];
        $totalIngresos = array_sum($data['ingresos']);
        $totalGanancia = array_sum($data['ganancias']);
        $margen = $totalIngresos > 0 ? round(($totalGanancia / $totalIngresos) * 100, 1) : 0;
    @endphp

    <div class="mb-6">
        <h2 class="text-lg font-black text-slate-900 dark:text-white">Reporte de Ganancias</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
            Cálculo: <strong>total_neto (venta) − precio_compra × cantidad</strong> por cada línea de venta.
        </p>
    </div>

    {{-- Info alert — ganancia es estimada --}}
    <div class="flex items-start gap-3 p-4 rounded-2xl border border-amber-200/80 dark:border-amber-800/40 bg-amber-50/50 dark:bg-amber-950/20 mb-6">
        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
        </div>
        <div class="flex-1">
            <p class="text-xs font-black text-amber-800 dark:text-amber-300">Ganancia Estimada</p>
            <p class="text-[11px] text-amber-600/80 dark:text-amber-400/80 mt-0.5 leading-relaxed">
                Este cálculo usa <strong>precio_compra</strong> del lote/detalle_compra vs <strong>subtotal_neto</strong> del detalle_documento.
                No descuenta productos vencidos o mermados en stock. Para ver pérdidas reales por vencimiento, usa el reporte de pérdidas.
            </p>
            <a href="{{ \App\Filament\Clusters\Reportes\Resources\Reportes\Pages\ReportePerdidas::getUrl() }}"
               class="inline-flex items-center gap-1.5 mt-2 text-[10px] font-bold text-amber-700 dark:text-amber-400 hover:text-amber-800 dark:hover:text-amber-300 transition-colors">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m5.25 4.5 7.5 7.5-7.5 7.5m6-15 7.5 7.5-7.5 7.5"/></svg>
                Rastrear pérdidas por vencidos →
            </a>
        </div>
    </div>

    {{-- Sucursal context indicator --}}
    @php
        $ctx = app(\App\Support\SucursalContext::class);
        $sucursalActiva = $ctx->activeSucursal();
    @endphp
    @if($sucursalActiva)
        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-3">
            Sucursal: {{ $sucursalActiva->nombre_sucursal }}
        </p>
    @endif

    <div class="grid grid-cols-3 gap-3 mb-6">
        <div class="kpi-card kpi-blue p-4">
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Ingresos Totales</p>
            <p class="text-lg font-black text-slate-900 dark:text-white font-mono mt-1">S/ {{ number_format($totalIngresos, 2) }}</p>
        </div>
        <div class="kpi-card kpi-emerald p-4">
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Ganancia Estimada</p>
            <p class="text-lg font-black text-slate-900 dark:text-white font-mono mt-1">S/ {{ number_format($totalGanancia, 2) }}</p>
        </div>
        <div class="kpi-card kpi-teal p-4">
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Margen Est.</p>
            <p class="text-lg font-black text-slate-900 dark:text-white font-mono mt-1">{{ $margen }}%</p>
        </div>
    </div>

    <div class="glass-card p-5">
        <h3 class="text-sm font-black text-slate-700 dark:text-slate-300 mb-4">Tendencia de Ganancias</h3>
        <div class="relative h-72" wire:ignore x-data="chartComponent(@js($chartId), @js($chartConfig))">
            <canvas id="{{ $chartId }}"></canvas>
        </div>
    </div>
</x-filament-panels::page>
