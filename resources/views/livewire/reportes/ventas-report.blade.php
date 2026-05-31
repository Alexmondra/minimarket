<div>
    {{-- Header with title --}}
    <div class="mb-6">
        <h2 class="text-lg font-black text-slate-900 dark:text-white">Reporte de Ventas</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Análisis detallado de todas las ventas registradas.</p>
    </div>

    {{-- Filter Bar --}}
    <div class="glass-card p-4 mb-6">
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-3">
            <div>
                <label class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Desde</label>
                <input type="date" wire:model.live="fechaDesde"
                    class="w-full mt-1 h-10 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 text-xs font-semibold text-slate-900 dark:text-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10 outline-none transition">
            </div>
            <div>
                <label class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Hasta</label>
                <input type="date" wire:model.live="fechaHasta"
                    class="w-full mt-1 h-10 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 text-xs font-semibold text-slate-900 dark:text-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10 outline-none transition">
            </div>
            <div>
                <label class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Medio Pago</label>
                <select wire:model.live="medioPago"
                    class="w-full mt-1 h-10 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 text-xs font-semibold text-slate-900 dark:text-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10 outline-none transition">
                    <option value="">Todos</option>
                    @foreach($metodos as $m)
                        <option value="{{ $m }}">{{ ucfirst(strtolower($m)) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Buscar</label>
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="Cliente, comprobante..."
                    class="w-full mt-1 h-10 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 text-xs font-semibold text-slate-900 dark:text-white placeholder:text-slate-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10 outline-none transition">
            </div>
            <div class="flex items-end">
                <button wire:click="exportar"
                    class="w-full h-10 rounded-xl bg-slate-900 dark:bg-white text-white dark:text-slate-900 text-xs font-bold hover:bg-slate-800 dark:hover:bg-slate-100 transition">
                    Exportar
                </button>
            </div>
        </div>
    </div>

    {{-- Stats Row --}}
    <div class="grid grid-cols-3 gap-3 mb-6">
        <div class="kpi-card kpi-emerald p-4">
            <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Ventas</p>
            <p class="text-lg font-black text-slate-900 dark:text-white font-mono mt-1">S/ {{ $stats['total_ventas'] ?? '0.00' }}</p>
        </div>
        <div class="kpi-card kpi-blue p-4">
            <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Cantidad</p>
            <p class="text-lg font-black text-slate-900 dark:text-white font-mono mt-1">{{ $stats['cantidad'] ?? 0 }}</p>
        </div>
        <div class="kpi-card kpi-violet p-4">
            <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Ticket Promedio</p>
            <p class="text-lg font-black text-slate-900 dark:text-white font-mono mt-1">S/ {{ $stats['promedio'] ?? '0.00' }}</p>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="glass-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800">
                        <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Fecha</th>
                        <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Comprobante</th>
                        <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Cliente</th>
                        <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Medio Pago</th>
                        <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Total</th>
                        <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ventas as $venta)
                        <tr class="border-b border-slate-100 dark:border-slate-800/50 hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                            <td class="px-4 py-3 text-xs text-slate-700 dark:text-slate-300 font-mono">{{ $venta->fecha_emision?->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-xs font-semibold text-slate-800 dark:text-slate-200">{{ $venta->serie }}-{{ $venta->numero }}</td>
                            <td class="px-4 py-3 text-xs text-slate-600 dark:text-slate-400">{{ $venta->cliente?->nombre ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-lg px-2 py-0.5 text-[10px] font-bold
                                    @if($venta->medio_pago === 'EFECTIVO') bg-emerald-500/10 text-emerald-600 dark:text-emerald-400
                                    @elseif($venta->medio_pago === 'YAPE') bg-purple-500/10 text-purple-600 dark:text-purple-400
                                    @elseif($venta->medio_pago === 'TARJETA') bg-amber-500/10 text-amber-600 dark:text-amber-400
                                    @else bg-slate-500/10 text-slate-600 dark:text-slate-400 @endif">
                                    {{ $venta->medio_pago ?? '—' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-xs font-bold text-slate-800 dark:text-slate-200 font-mono">S/ {{ number_format($venta->total_neto, 2) }}</td>
                            <td class="px-4 py-3">
                                <span @class([
                                    'inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold',
                                    'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' => $venta->estado,
                                    'bg-rose-500/10 text-rose-600 dark:text-rose-400' => !$venta->estado,
                                ])>
                                    {{ $venta->estado ? 'Activo' : 'Anulado' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-sm text-slate-400 dark:text-slate-500">
                                No se encontraron ventas para los filtros seleccionados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($ventas->hasPages())
            <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-800">
                {{ $ventas->links() }}
            </div>
        @endif
    </div>
</div>
