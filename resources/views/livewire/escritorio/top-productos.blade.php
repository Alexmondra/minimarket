<div class="glass-card colorful-card card-slate p-6">
    <div class="flex items-center justify-between mb-5 flex-wrap gap-2">
        <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-2">
            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-violet-500/10 text-violet-500 border border-violet-500/15">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/></svg>
            </span>
            Rendimiento de Productos
            <span class="text-[9px] font-bold text-slate-400 dark:text-slate-500 lowercase">· este mes</span>
        </h3>
        <div class="flex items-center gap-0.5 bg-slate-100 dark:bg-slate-900/60 rounded-lg p-0.5 border border-slate-200/30 dark:border-slate-800/20">
            <button wire:click="setTab('mas_vendidos')" @class([
                'rounded-md px-2.5 py-1 text-[10px] font-bold transition-all duration-200 border border-transparent',
                'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-xs border-slate-200/30 dark:border-slate-700/20' => $tab === 'mas_vendidos',
                'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300' => $tab !== 'mas_vendidos',
            ])>Más Vendidos</button>
            <button wire:click="setTab('sin_movimiento')" @class([
                'rounded-md px-2.5 py-1 text-[10px] font-bold transition-all duration-200 border border-transparent',
                'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-xs border-slate-200/30 dark:border-slate-700/20' => $tab === 'sin_movimiento',
                'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300' => $tab !== 'sin_movimiento',
            ])>Sin Movimiento</button>
        </div>
    </div>

    @if(empty($productos))
        <div class="flex flex-col items-center justify-center py-8 text-center border-2 border-dashed border-slate-200/50 dark:border-slate-800/30 rounded-2xl p-6">
            <svg class="h-6 w-6 text-slate-300 dark:text-slate-700 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
            </svg>
            <p class="text-xs font-black uppercase tracking-wider text-slate-500 dark:text-slate-400">Sin registros</p>
            <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5">No hay productos en esta categoría para el período seleccionado.</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800/80">
                        <th class="pb-3 text-[9.5px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500 pl-1 w-12 text-center">Pos</th>
                        <th class="pb-3 text-[9.5px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500">Detalle del Producto</th>
                        <th class="pb-3 text-[9.5px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500 text-right w-24">Ventas</th>
                        <th class="pb-3 text-[9.5px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500 text-right w-36 pr-1">Ingresos Totales</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100/50 dark:divide-slate-800/40">
                    @foreach($productos as $i => $p)
                        @php
                            $rank = $i + 1;
                            $rankBadge = match($rank) {
                                1 => 'bg-amber-500/10 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400 border border-amber-500/20',
                                2 => 'bg-slate-400/10 text-slate-600 dark:bg-slate-400/15 dark:text-slate-300 border border-slate-400/20',
                                3 => 'bg-orange-400/10 text-orange-600 dark:bg-orange-400/15 dark:text-orange-450 border border-orange-450/20',
                                default => 'text-slate-400 dark:text-slate-500 font-medium'
                            };
                        @endphp
                        <tr class="hover:bg-slate-50/40 dark:hover:bg-slate-900/40 transition-colors">
                            <td class="py-3.5 text-center">
                                <span class="inline-flex h-5 w-5 items-center justify-center rounded-full text-[10px] font-bold {{ $rankBadge }}">
                                    {{ $rank }}
                                </span>
                            </td>
                            <td class="py-3.5 text-xs font-semibold text-slate-700 dark:text-slate-200">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm shrink-0">📦</span>
                                    <span class="truncate max-w-[250px] sm:max-w-md" title="{{ $p['producto_nombre'] }}">{{ $p['producto_nombre'] }}</span>
                                </div>
                            </td>
                            <td class="py-3.5 text-xs font-bold text-slate-700 dark:text-slate-300 text-right font-mono tracking-tight">{{ $p['total_ventas'] }}</td>
                            <td class="py-3.5 text-xs font-bold text-emerald-600 dark:text-emerald-400 text-right font-mono tracking-tight pr-1">
                                @if(isset($p['total_ingresos']))
                                    <span class="text-[9px] font-semibold opacity-75 mr-0.5">S/</span>{{ number_format($p['total_ingresos'], 2) }}
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div wire:poll.300s="loadData"></div>
</div>

