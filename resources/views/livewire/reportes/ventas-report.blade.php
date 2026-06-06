<div>
    {{-- Header with title --}}
    <div class="mb-6">
        <h2 class="text-lg font-black text-slate-900 dark:text-white">Reporte de Ventas</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Análisis detallado de todas las ventas registradas.</p>
    </div>

    {{-- Filter Bar --}}
    <div class="glass-card p-4 mb-6">
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-4 gap-3">
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
        </div>
    </div>

    {{-- Export Center --}}
    <div class="mb-6 overflow-hidden rounded-3xl border border-slate-200 bg-white p-4 shadow-sm shadow-slate-200/60 dark:border-slate-800 dark:bg-slate-950 dark:shadow-none">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="max-w-2xl">
                <div class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-[10px] font-black uppercase tracking-wider text-emerald-700 dark:border-emerald-900/70 dark:bg-emerald-950/40 dark:text-emerald-300">
                    Exportacion inteligente
                </div>
                <h3 class="mt-3 text-base font-black text-slate-950 dark:text-white">Descarga el reporte listo para contabilidad</h3>
                <p class="mt-1 text-xs leading-5 text-slate-600 dark:text-slate-300">
                    Usa <strong>Boletas y facturas</strong> para reportes SUNAT. Usa <strong>Todo con tickets</strong> cuando necesites ver la venta real completa del minimarket.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <button wire:click="exportarExcel('sunat')" wire:loading.attr="disabled"
                    class="group h-12 rounded-2xl bg-emerald-600 px-4 text-left text-xs font-black text-white shadow-lg shadow-emerald-600/20 ring-1 ring-emerald-500/20 transition-all duration-200 hover:-translate-y-0.5 hover:bg-emerald-500 disabled:cursor-wait disabled:opacity-70 dark:bg-emerald-500 dark:text-emerald-950 dark:shadow-emerald-950/30 dark:hover:bg-emerald-400">
                    <span class="block text-[10px] uppercase tracking-wider text-emerald-100 dark:text-emerald-950/70">Excel</span>
                    Boletas y facturas
                </button>
                <button wire:click="exportarPdf('sunat')" wire:loading.attr="disabled"
                    class="group h-12 rounded-2xl bg-slate-900 px-4 text-left text-xs font-black text-white shadow-lg shadow-slate-900/20 ring-1 ring-slate-800/20 transition-all duration-200 hover:-translate-y-0.5 hover:bg-slate-800 disabled:cursor-wait disabled:opacity-70 dark:bg-slate-100 dark:text-slate-950 dark:shadow-none dark:hover:bg-white">
                    <span class="block text-[10px] uppercase tracking-wider text-slate-300 dark:text-slate-500">PDF</span>
                    Boletas y facturas
                </button>
                <button wire:click="exportarExcel('todo')" wire:loading.attr="disabled"
                    class="group h-12 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 text-left text-xs font-black text-emerald-800 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-emerald-300 hover:bg-emerald-100 disabled:cursor-wait disabled:opacity-70 dark:border-emerald-900/70 dark:bg-emerald-950/30 dark:text-emerald-200 dark:hover:bg-emerald-950/60">
                    <span class="block text-[10px] uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Excel</span>
                    Todo con tickets
                </button>
                <button wire:click="exportarPdf('todo')" wire:loading.attr="disabled"
                    class="group h-12 rounded-2xl border border-slate-200 bg-slate-50 px-4 text-left text-xs font-black text-slate-800 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-slate-300 hover:bg-slate-100 disabled:cursor-wait disabled:opacity-70 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100 dark:hover:bg-slate-800">
                    <span class="block text-[10px] uppercase tracking-wider text-slate-500 dark:text-slate-400">PDF</span>
                    Todo con tickets
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
