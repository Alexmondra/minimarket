<x-filament-panels::page>
    @php
        $qb = app(\App\Support\Reportes\ReporteQueryBuilder::class);
        $vencidos = $qb->productosVencidos()->get();
        $anuladas = $qb->ventasAnuladasBase()->whereDate('updated_at', '>=', today()->subDays(30))->get();
        $perdidaVencidos = $vencidos->sum(function($l) use ($qb) { return $l->lotePresentaciones->sum(fn($lp) => ($lp->stock ?? 0) * ($lp->precio_compra ?? 0)); });
        $totalAnulaciones = $anuladas->sum('total_neto');
    @endphp

    <div class="mb-6">
        <h2 class="text-lg font-black text-slate-900 dark:text-white">Reporte de Pérdidas</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Productos vencidos, mermas, devoluciones y anulaciones.</p>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
        <div class="kpi-card kpi-rose p-4">
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Vencidos</p>
            <p class="text-lg font-black text-slate-900 dark:text-white font-mono mt-1">{{ $vencidos->count() }}</p>
            <p class="text-[10px] text-rose-500 mt-1">Pérdida est: S/ {{ number_format($perdidaVencidos, 2) }}</p>
        </div>
        <div class="kpi-card kpi-amber p-4">
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Anulaciones (30d)</p>
            <p class="text-lg font-black text-slate-900 dark:text-white font-mono mt-1">{{ $anuladas->count() }}</p>
            <p class="text-[10px] text-amber-500 mt-1">Monto: S/ {{ number_format($totalAnulaciones, 2) }}</p>
        </div>
        <div class="kpi-card kpi-orange p-4">
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Mermas</p>
            <p class="text-lg font-black text-slate-900 dark:text-white font-mono mt-1">—</p>
            <p class="text-[10px] text-slate-400 mt-1">Próximamente</p>
        </div>
        <div class="kpi-card kpi-slate p-4">
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Devoluciones</p>
            <p class="text-lg font-black text-slate-900 dark:text-white font-mono mt-1">—</p>
            <p class="text-[10px] text-slate-400 mt-1">Próximamente</p>
        </div>
    </div>

    <div class="glass-card p-5">
        <h3 class="text-sm font-black text-slate-700 dark:text-slate-300 mb-4">Productos Vencidos con Stock</h3>
        @if($vencidos->isEmpty())
            <p class="text-sm text-slate-400 py-6 text-center">No hay productos vencidos con stock. ¡Excelente!</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800">
                            <th class="pb-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">Producto</th>
                            <th class="pb-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">Sucursal</th>
                            <th class="pb-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">Vencimiento</th>
                            <th class="pb-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 text-right">Stock</th>
                            <th class="pb-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 text-right">Pérdida Est.</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($vencidos as $lote)
                            @foreach($lote->lotePresentaciones as $lp)
                                @if($lp->stock > 0)
                                    <tr class="border-b border-slate-100 dark:border-slate-800/50">
                                        <td class="py-3 text-xs font-semibold text-slate-800 dark:text-slate-200">{{ $lote->producto_nombre }}</td>
                                        <td class="py-3 text-xs text-slate-500">{{ $lote->sucursal?->nombre_sucursal }}</td>
                                        <td class="py-3 text-xs text-rose-500 font-bold">{{ $lote->fecha_vencimiento?->format('d/m/Y') }}</td>
                                        <td class="py-3 text-xs font-bold text-slate-700 text-right font-mono">{{ $lp->stock }}</td>
                                        <td class="py-3 text-xs font-bold text-rose-500 text-right font-mono">S/ {{ number_format($lp->stock * ($lp->precio_compra ?? 0), 2) }}</td>
                                    </tr>
                                @endif
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-filament-panels::page>
