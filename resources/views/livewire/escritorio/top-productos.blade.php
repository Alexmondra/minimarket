<div class="glass-card p-5">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-black text-slate-700 dark:text-slate-300 flex items-center gap-2">
            <svg class="h-4 w-4 text-violet-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/></svg>
            Top Productos del Mes
        </h3>
        <div class="flex items-center gap-1 bg-slate-100 dark:bg-slate-800 rounded-xl p-1">
            <button wire:click="setTab('mas_vendidos')" @class([
                'rounded-lg px-3 py-1.5 text-[10px] font-bold transition-all duration-200',
                'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-sm' => $tab === 'mas_vendidos',
                'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300' => $tab !== 'mas_vendidos',
            ])>Más Vendidos</button>
            <button wire:click="setTab('sin_movimiento')" @class([
                'rounded-lg px-3 py-1.5 text-[10px] font-bold transition-all duration-200',
                'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-sm' => $tab === 'sin_movimiento',
                'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300' => $tab !== 'sin_movimiento',
            ])>Sin Movimiento</button>
        </div>
    </div>

    @if(empty($productos))
        <div class="flex flex-col items-center justify-center py-8 text-center">
            <p class="text-sm text-slate-400 dark:text-slate-500">No hay datos disponibles para este período.</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800">
                        <th class="pb-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">#</th>
                        <th class="pb-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Producto</th>
                        <th class="pb-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 text-right">Ventas</th>
                        <th class="pb-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 text-right">Ingresos</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($productos as $i => $p)
                        <tr class="border-b border-slate-100 dark:border-slate-800/50 hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                            <td class="py-3 text-xs font-bold text-slate-400 dark:text-slate-500">{{ $i + 1 }}</td>
                            <td class="py-3 text-xs font-semibold text-slate-800 dark:text-slate-200 max-w-[200px] truncate">{{ $p['producto_nombre'] }}</td>
                            <td class="py-3 text-xs font-bold text-slate-700 dark:text-slate-300 text-right font-mono">{{ $p['total_ventas'] }}</td>
                            <td class="py-3 text-xs font-bold text-emerald-600 dark:text-emerald-400 text-right font-mono">S/ {{ number_format($p['total_ingresos'] ?? 0, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div wire:poll.300s="loadData"></div>
</div>
