<x-filament-panels::page>
    @php
        $qb = app(\App\Support\Reportes\ReporteQueryBuilder::class);
        $bajoStock = $qb->productosBajoStock()->get();
        $porVencer7 = $qb->productosPorVencer(7)->get();
        $porVencer30 = $qb->productosPorVencer(30)->get();
        $vencidos = $qb->productosVencidos()->get();
        $calc = app(\App\Support\Reportes\MetricCalculator::class);
        $top = $calc->topProductos(15);
    @endphp

    <div class="mb-6">
        <h2 class="text-lg font-black text-slate-900 dark:text-white">Reporte de Productos</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Bajo stock, por vencer, vencidos, más vendidos.</p>
    </div>

    {{-- Tabs via Alpine --}}
    <div x-data="{ tab: 'bajo_stock' }">
        <div class="flex items-center gap-1 bg-slate-100 dark:bg-slate-800 rounded-xl p-1 mb-6 overflow-x-auto w-fit">
            @php $tabs = [
                'bajo_stock' => 'Bajo Stock',
                'por_vencer_7' => 'Por Vencer (7d)',
                'por_vencer_30' => 'Por Vencer (30d)',
                'vencidos' => 'Vencidos',
                'top' => 'Más Vendidos',
            ]; @endphp
            @foreach($tabs as $key => $label)
                <button @click="tab = '{{ $key }}'" :class="tab === '{{ $key }}' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-sm' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700'"
                    class="rounded-lg px-3 py-1.5 text-[10px] font-bold transition-all duration-200 whitespace-nowrap">{{ $label }}</button>
            @endforeach
        </div>

        {{-- Bajo Stock --}}
        <div x-show="tab === 'bajo_stock'">
            <div class="glass-card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead><tr class="border-b border-slate-200 dark:border-slate-800">
                            <th class="px-4 py-3 text-[10px] font-bold uppercase text-slate-400">Producto</th>
                            <th class="px-4 py-3 text-[10px] font-bold uppercase text-slate-400">Sucursal</th>
                            <th class="px-4 py-3 text-[10px] font-bold uppercase text-slate-400 text-right">Stock</th>
                            <th class="px-4 py-3 text-[10px] font-bold uppercase text-slate-400 text-right">Mínimo</th>
                        </tr></thead>
                        <tbody>
                            @forelse($bajoStock as $ps)
                                <tr class="border-b border-slate-100 dark:border-slate-800/50">
                                    <td class="px-4 py-3 text-xs font-semibold text-slate-800 dark:text-slate-200">{{ $ps->producto?->nombre ?? '—' }}</td>
                                    <td class="px-4 py-3 text-xs text-slate-500">{{ $ps->sucursal?->nombre_sucursal }}</td>
                                    <td class="px-4 py-3 text-xs font-bold text-amber-600 text-right font-mono">{{ $ps->lotePresentacion?->stock ?? 0 }}</td>
                                    <td class="px-4 py-3 text-xs text-slate-500 text-right font-mono">{{ $ps->stock_minimo }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-4 py-10 text-center text-sm text-slate-400">No hay productos bajo stock. ¡Excelente!</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Por Vencer 7d --}}
        <div x-show="tab === 'por_vencer_7'" x-cloak>
            <div class="glass-card overflow-hidden">
                <table class="w-full text-left">
                    <thead><tr class="border-b border-slate-200 dark:border-slate-800">
                        <th class="px-4 py-3 text-[10px] font-bold uppercase text-slate-400">Producto</th>
                        <th class="px-4 py-3 text-[10px] font-bold uppercase text-slate-400">Sucursal</th>
                        <th class="px-4 py-3 text-[10px] font-bold uppercase text-slate-400">Vencimiento</th>
                        <th class="px-4 py-3 text-[10px] font-bold uppercase text-slate-400 text-right">Stock</th>
                    </tr></thead>
                    <tbody>
                        @forelse($porVencer7 as $lote)
                            @foreach($lote->lotePresentaciones->where('stock', '>', 0) as $lp)
                                <tr class="border-b border-slate-100 dark:border-slate-800/50">
                                    <td class="px-4 py-3 text-xs font-semibold text-slate-800 dark:text-slate-200">{{ $lote->producto_nombre }}</td>
                                    <td class="px-4 py-3 text-xs text-slate-500">{{ $lote->sucursal?->nombre_sucursal }}</td>
                                    <td class="px-4 py-3 text-xs text-amber-500 font-bold">{{ $lote->fecha_vencimiento?->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3 text-xs font-bold text-slate-700 text-right font-mono">{{ $lp->stock }}</td>
                                </tr>
                            @endforeach
                        @empty
                            <tr><td colspan="4" class="px-4 py-10 text-center text-sm text-slate-400">No hay productos por vencer esta semana.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Por Vencer 30d --}}
        <div x-show="tab === 'por_vencer_30'" x-cloak>
            <div class="glass-card overflow-hidden">
                <table class="w-full text-left">
                    <thead><tr class="border-b border-slate-200 dark:border-slate-800">
                        <th class="px-4 py-3 text-[10px] font-bold uppercase text-slate-400">Producto</th>
                        <th class="px-4 py-3 text-[10px] font-bold uppercase text-slate-400">Sucursal</th>
                        <th class="px-4 py-3 text-[10px] font-bold uppercase text-slate-400">Vencimiento</th>
                        <th class="px-4 py-3 text-[10px] font-bold uppercase text-slate-400 text-right">Stock</th>
                    </tr></thead>
                    <tbody>
                        @forelse($porVencer30 as $lote)
                            @foreach($lote->lotePresentaciones->where('stock', '>', 0) as $lp)
                                <tr class="border-b border-slate-100 dark:border-slate-800/50">
                                    <td class="px-4 py-3 text-xs font-semibold text-slate-800 dark:text-slate-200">{{ $lote->producto_nombre }}</td>
                                    <td class="px-4 py-3 text-xs text-slate-500">{{ $lote->sucursal?->nombre_sucursal }}</td>
                                    <td class="px-4 py-3 text-xs text-orange-500 font-bold">{{ $lote->fecha_vencimiento?->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3 text-xs font-bold text-slate-700 text-right font-mono">{{ $lp->stock }}</td>
                                </tr>
                            @endforeach
                        @empty
                            <tr><td colspan="4" class="px-4 py-10 text-center text-sm text-slate-400">No hay productos por vencer este mes.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Vencidos --}}
        <div x-show="tab === 'vencidos'" x-cloak>
            <div class="glass-card overflow-hidden">
                <table class="w-full text-left"><thead><tr class="border-b border-slate-200 dark:border-slate-800">
                    <th class="px-4 py-3 text-[10px] font-bold uppercase text-slate-400">Producto</th>
                    <th class="px-4 py-3 text-[10px] font-bold uppercase text-slate-400">Vencimiento</th>
                    <th class="px-4 py-3 text-[10px] font-bold uppercase text-slate-400 text-right">Stock</th>
                </tr></thead><tbody>
                    @forelse($vencidos as $lote)
                        @foreach($lote->lotePresentaciones->where('stock', '>', 0) as $lp)
                            <tr class="border-b border-slate-100 dark:border-slate-800/50">
                                <td class="px-4 py-3 text-xs font-semibold text-slate-800 dark:text-slate-200">{{ $lote->producto_nombre }}</td>
                                <td class="px-4 py-3 text-xs text-rose-500 font-bold">{{ $lote->fecha_vencimiento?->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-xs font-bold text-slate-700 text-right font-mono">{{ $lp->stock }}</td>
                            </tr>
                        @endforeach
                    @empty
                        <tr><td colspan="3" class="px-4 py-10 text-center text-sm text-slate-400">No hay productos vencidos.</td></tr>
                    @endforelse
                </tbody></table>
            </div>
        </div>

        {{-- Más Vendidos --}}
        <div x-show="tab === 'top'" x-cloak>
            <div class="glass-card overflow-hidden">
                <table class="w-full text-left"><thead><tr class="border-b border-slate-200 dark:border-slate-800">
                    <th class="px-4 py-3 text-[10px] font-bold uppercase text-slate-400">#</th>
                    <th class="px-4 py-3 text-[10px] font-bold uppercase text-slate-400">Producto</th>
                    <th class="px-4 py-3 text-[10px] font-bold uppercase text-slate-400 text-right">Ventas</th>
                    <th class="px-4 py-3 text-[10px] font-bold uppercase text-slate-400 text-right">Ingresos</th>
                </tr></thead><tbody>
                    @foreach($top as $i => $p)
                        <tr class="border-b border-slate-100 dark:border-slate-800/50">
                            <td class="px-4 py-3 text-xs font-bold text-slate-400">{{ $i + 1 }}</td>
                            <td class="px-4 py-3 text-xs font-semibold text-slate-800 dark:text-slate-200">{{ $p['producto_nombre'] }}</td>
                            <td class="px-4 py-3 text-xs font-bold text-slate-700 text-right font-mono">{{ $p['total_ventas'] }}</td>
                            <td class="px-4 py-3 text-xs font-bold text-emerald-600 text-right font-mono">S/ {{ number_format($p['total_ingresos'] ?? 0, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody></table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
