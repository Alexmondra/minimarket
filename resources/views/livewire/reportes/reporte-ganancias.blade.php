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

        // Daily calculations
        $kpiVentasDia = $calc->ventasDelDia();
        $kpiGananciaDia = $calc->gananciaNeta();
        $ventasDiaRaw = (float) str_replace(',', '', $kpiVentasDia['value']);
        $gananciaDiaRaw = (float) str_replace(',', '', $kpiGananciaDia['value']);
        $margenDia = $ventasDiaRaw > 0 ? round(($gananciaDiaRaw / $ventasDiaRaw) * 100, 1) : 0;

        // Monthly calculations
        $kpiIngresosMes = $calc->totalIngresos();
        $kpiGananciaMes = $calc->gananciaMensual();
        $ventasMesRaw = (float) str_replace(',', '', $kpiIngresosMes['value']);
        $gananciaMesRaw = (float) str_replace(',', '', $kpiGananciaMes['value']);
        $margenMes = $ventasMesRaw > 0 ? round(($gananciaMesRaw / $ventasMesRaw) * 100, 1) : 0;
    @endphp

 

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

    {{-- KPIs Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        {{-- Hoy Section --}}
        <div class="glass-card p-5 border border-slate-100 dark:border-slate-800 rounded-2xl">
            <h3 class="text-xs font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-4 flex items-center gap-1.5">
                <svg class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                Resumen de Hoy
            </h3>
            <div class="space-y-4">
                <div>
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Ventas Netas Hoy</p>
                    <p class="text-xl font-black text-slate-900 dark:text-white font-mono mt-0.5">S/ {{ number_format($ventasDiaRaw, 2) }}</p>
                </div>
                <div class="pt-2 border-t border-slate-100 dark:border-slate-800/60">
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Ganancia Est. Hoy</p>
                    <p class="text-xl font-black text-emerald-600 dark:text-emerald-400 font-mono mt-0.5 font-bold">S/ {{ number_format($gananciaDiaRaw, 2) }}</p>
                </div>
                <div class="pt-2 border-t border-slate-100 dark:border-slate-800/60">
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Margen Est. Hoy</p>
                    <p class="text-base font-bold text-teal-600 dark:text-teal-400 font-mono mt-0.5">{{ $margenDia }}%</p>
                </div>
            </div>
        </div>

        {{-- Este Mes Section --}}
        <div class="glass-card p-5 border border-slate-100 dark:border-slate-800 rounded-2xl">
            <h3 class="text-xs font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-4 flex items-center gap-1.5">
                <svg class="h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z"/></svg>
                Resumen de este Mes
            </h3>
            <div class="space-y-4">
                <div>
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Ventas Netas Mes</p>
                    <p class="text-xl font-black text-slate-900 dark:text-white font-mono mt-0.5">S/ {{ number_format($ventasMesRaw, 2) }}</p>
                </div>
                <div class="pt-2 border-t border-slate-100 dark:border-slate-800/60">
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Ganancia Est. Mes</p>
                    <p class="text-xl font-black text-emerald-600 dark:text-emerald-400 font-mono mt-0.5 font-bold">S/ {{ number_format($gananciaMesRaw, 2) }}</p>
                </div>
                <div class="pt-2 border-t border-slate-100 dark:border-slate-800/60">
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Margen Est. Mes</p>
                    <p class="text-base font-bold text-teal-600 dark:text-teal-400 font-mono mt-0.5">{{ $margenMes }}%</p>
                </div>
            </div>
        </div>

        {{-- Anual (Últimos 12 Meses) Section --}}
        <div class="glass-card p-5 border border-slate-100 dark:border-slate-800 rounded-2xl">
            <h3 class="text-xs font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-4 flex items-center gap-1.5">
                <svg class="h-4 w-4 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 1 0 7.5 7.5h-7.5V6Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0 0 13.5 3v7.5Z"/></svg>
                Últimos 12 Meses
            </h3>
            <div class="space-y-4">
                <div>
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Ingresos Totales</p>
                    <p class="text-xl font-black text-slate-900 dark:text-white font-mono mt-0.5">S/ {{ number_format($totalIngresos, 2) }}</p>
                </div>
                <div class="pt-2 border-t border-slate-100 dark:border-slate-800/60">
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Ganancia Est. Total</p>
                    <p class="text-xl font-black text-emerald-600 dark:text-emerald-400 font-mono mt-0.5 font-bold">S/ {{ number_format($totalGanancia, 2) }}</p>
                </div>
                <div class="pt-2 border-t border-slate-100 dark:border-slate-800/60">
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Margen Est. Total</p>
                    <p class="text-base font-bold text-teal-600 dark:text-teal-400 font-mono mt-0.5">{{ $margen }}%</p>
                </div>
            </div>
        </div>
    </div>

    <div class="glass-card p-5">
        <h3 class="text-sm font-black text-slate-700 dark:text-slate-300 mb-4">Tendencia de Ganancias</h3>
        <div class="relative h-72" wire:ignore x-data="chartComponent(@js($chartId), @js($chartConfig))">
            <canvas id="{{ $chartId }}"></canvas>
        </div>
    </div>
</x-filament-panels::page>
