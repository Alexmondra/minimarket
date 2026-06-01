<x-filament-panels::page>
    <div class="space-y-6 animate-fade-in">
        
        <!-- Tarjetas de Estadísticas (KPIs) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total de Existencias -->
            <div class="kpi-card kpi-indigo">
                <div class="flex justify-between items-start">
                    <div class="space-y-1.5">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Existencias Totales</span>
                        <div class="text-2xl font-black text-slate-950 dark:text-white">
                            {{ number_format($this->stats['total_items']) }}
                        </div>
                        <span class="text-[10px] text-slate-400 dark:text-slate-500 font-semibold block">Unidades en inventario</span>
                    </div>
                    <div class="p-2.5 bg-indigo-500/10 text-indigo-500 rounded-xl">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7.5L12 3 4 7.5M20 7.5v9L12 21M20 7.5l-8 4.5M4 7.5v9L12 21M4 7.5l8 4.5" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Variantes de Presentación -->
            <div class="kpi-card kpi-emerald">
                <div class="flex justify-between items-start">
                    <div class="space-y-1.5">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Presentaciones Activas</span>
                        <div class="text-2xl font-black text-slate-950 dark:text-white">
                            {{ $this->stats['total_presentations'] }}
                        </div>
                        <span class="text-[10px] text-slate-400 dark:text-slate-500 font-semibold block">Con existencias asociadas</span>
                    </div>
                    <div class="p-2.5 bg-emerald-500/10 text-emerald-500 rounded-xl">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Alertas de Stock Bajo -->
            <div class="kpi-card kpi-amber alert-pulse">
                <div class="flex justify-between items-start">
                    <div class="space-y-1.5">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Alertas Stock Bajo</span>
                        <div class="text-2xl font-black text-slate-950 dark:text-white">
                            {{ $this->stats['low_stock_count'] }}
                        </div>
                        <span class="text-[10px] text-slate-400 dark:text-slate-500 font-semibold block">Igual o menor al mínimo</span>
                    </div>
                    <div class="p-2.5 bg-amber-500/10 text-amber-500 rounded-xl">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Agotados -->
            <div class="kpi-card kpi-rose">
                <div class="flex justify-between items-start">
                    <div class="space-y-1.5">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Presentaciones Agotadas</span>
                        <div class="text-2xl font-black text-slate-950 dark:text-white">
                            {{ $this->stats['out_of_stock_count'] }}
                        </div>
                        <span class="text-[10px] text-slate-400 dark:text-slate-500 font-semibold block">Sin existencias (0 stock)</span>
                    </div>
                    <div class="p-2.5 bg-rose-500/10 text-rose-500 rounded-xl">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros y Búsqueda -->
        <div class="glass-card p-4">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                
                <!-- Buscador e inputs -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 flex-1">
                    <!-- Buscador -->
                    <div class="relative">
                        <input type="text" 
                               wire:model.live="search"
                               placeholder="Buscar por producto o presentación..."
                               class="w-full pl-9.5 pr-4 py-2.5 text-xs rounded-xl border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition">
                    </div>

                    <!-- Filtro Categoría -->
                    <div>
                        <select wire:model.live="selectedCategoriaId"
                                class="w-full py-2.5 px-3 text-xs rounded-xl border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition">
                            <option value="all">Todas las categorías</option>
                            @foreach ($this->categorias as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filtro Estado Stock -->
                    <div>
                        <select wire:model.live="selectedStockEstado"
                                class="w-full py-2.5 px-3 text-xs rounded-xl border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition">
                            <option value="all">Todos los stocks</option>
                            <option value="normal">Stock Normal</option>
                            <option value="bajo">Stock Bajo</option>
                            <option value="agotado">Agotados</option>
                        </select>
                    </div>
                </div>

                <!-- Toggles de Modo de Vista [ Cards / Lista ] -->
                <div class="flex items-center gap-1.5 p-1 bg-slate-100 dark:bg-slate-900/80 rounded-xl border dark:border-slate-800/60 shrink-0 self-start lg:self-center">
                    <button type="button" 
                            wire:click="$set('viewMode', 'cards')"
                            class="px-3.5 py-1.5 rounded-lg text-xs font-extrabold transition flex items-center gap-1.5 {{ $viewMode === 'cards' ? 'bg-white dark:bg-slate-800 text-slate-950 dark:text-white shadow-sm' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-300' }}">
                        <span>🔳</span>
                        <span>Cards</span>
                    </button>
                    <button type="button" 
                            wire:click="$set('viewMode', 'list')"
                            class="px-3.5 py-1.5 rounded-lg text-xs font-extrabold transition flex items-center gap-1.5 {{ $viewMode === 'list' ? 'bg-white dark:bg-slate-800 text-slate-950 dark:text-white shadow-sm' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-300' }}">
                        <span>☰</span>
                        <span>Lista</span>
                    </button>
                </div>
            </div>

            <!-- Filtro Sucursal central logic -->
            <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800/40 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Sucursal:</span>
                    @if ($this->isSucursalLocked)
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-xl text-[10px] font-extrabold bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 shadow-sm">
                            🔒 Bloqueada por Sucursal Activa
                        </span>
                    @endif
                </div>

                <div class="relative w-full sm:w-72">
                    @if ($this->isSucursalLocked)
                        <select disabled
                                class="w-full py-2 px-3 text-xs font-bold rounded-xl border-slate-200 bg-slate-50 dark:bg-slate-900/60 dark:border-slate-800 text-slate-500 dark:text-slate-400 transition cursor-not-allowed">
                            <option>{{ app(\App\Support\SucursalContext::class)->activeSucursal()?->nombre_sucursal }}</option>
                        </select>
                    @else
                        <select wire:model.live="selectedSucursalId"
                                class="w-full py-2 px-3 text-xs font-bold rounded-xl border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition">
                            <option value="all">Todas las sucursales</option>
                            @foreach ($sucursales as $suc)
                                <option value="{{ $suc['id'] }}">{{ $suc['nombre'] }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>
            </div>
        </div>

        <!-- Renderización condicional por Modo de Vista -->
        @if ($viewMode === 'cards')
            <!-- VISTA EN MODO CARDS -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @forelse ($this->existencias as $item)
                    @php
                        $isAgotado = $item->total_stock == 0;
                        $isBajo = !$isAgotado && $item->total_stock <= $item->max_stock_minimo;
                    @endphp
                    <div class="glass-card hover:-translate-y-1 transition duration-300 p-5 flex flex-col justify-between h-76 group">
                        <div class="space-y-4">
                            <!-- Imagen & Badges -->
                            <div class="flex justify-between items-start gap-3">
                                <div class="relative w-16 h-16 rounded-2xl overflow-hidden bg-slate-100 dark:bg-slate-800 border dark:border-slate-700/60 flex items-center justify-center shrink-0 shadow-inner">
                                    @if ($item->imagen)
                                        <img src="{{ url('storage/' . $item->imagen) }}" class="w-full h-full object-cover" />
                                    @else
                                        <div class="absolute inset-0 bg-gradient-to-tr from-indigo-500/10 to-violet-500/5 dark:from-indigo-500/20 dark:to-violet-500/10"></div>
                                        <svg class="w-8 h-8 text-indigo-500/70" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25" />
                                        </svg>
                                    @endif
                                </div>

                                <div class="flex flex-col items-end gap-1.5">
                                    @if ($isAgotado)
                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-xl text-[9px] font-extrabold bg-rose-100 text-rose-700 border border-rose-300 dark:bg-rose-950/20 dark:text-rose-400 dark:border-rose-900/40">
                                            ● Agotado
                                        </span>
                                    @elseif ($isBajo)
                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-xl text-[9px] font-extrabold bg-amber-100 text-amber-700 border border-amber-300 dark:bg-amber-950/20 dark:text-amber-400 dark:border-amber-900/40 alert-pulse">
                                            ⚠️ Stock Bajo
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-xl text-[9px] font-extrabold bg-emerald-100 text-emerald-700 border border-emerald-300 dark:bg-emerald-950/20 dark:text-emerald-400 dark:border-emerald-900/40">
                                            ● Stock Normal
                                        </span>
                                    @endif

                                    @if ($item->categoria_nombre)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[9px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border dark:border-slate-700/60">
                                            {{ $item->categoria_nombre }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Detalles -->
                            <div class="space-y-1.5">
                                <h3 class="font-extrabold text-slate-800 dark:text-white text-sm group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors truncate" title="{{ $item->producto_nombre }}">
                                    {{ $item->producto_nombre }}
                                </h3>
                                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                                    {{ $item->tipo_presentacion }}
                                </p>
                                
                                <div class="flex justify-between items-center text-xs font-bold pt-2 border-t border-slate-100 dark:border-slate-800/40">
                                    <div class="text-slate-400">Total Stock:</div>
                                    <div class="{{ $isAgotado ? 'text-rose-600' : ($isBajo ? 'text-amber-600' : 'text-slate-800 dark:text-slate-200') }}">
                                        {{ $item->total_stock }} uds
                                    </div>
                                </div>

                                <div class="flex justify-between items-center text-xs font-bold">
                                    <div class="text-slate-400">Precio Rango:</div>
                                    <div class="text-indigo-600 dark:text-indigo-400">
                                        @if ($item->min_precio != $item->max_precio)
                                            S/ {{ number_format($item->min_precio, 2) }} - S/ {{ number_format($item->max_precio, 2) }}
                                        @else
                                            S/ {{ number_format($item->min_precio, 2) }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botón Ver Lotes -->
                        <div class="pt-4">
                            <button type="button" 
                                    wire:click="verLotes({{ $item->producto_id }}, {{ $item->producto_presentacion_id }})"
                                    class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2.5 text-xs font-bold text-white bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 active:scale-95 transition-all shadow-md shadow-indigo-500/20 rounded-xl">
                                <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span>👁 Ver Lotes</span>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-16 text-center">
                        <div class="max-w-md mx-auto space-y-4">
                            <div class="inline-flex p-4 bg-slate-50 dark:bg-slate-900/60 rounded-full border border-slate-200/50 dark:border-slate-800/40 text-slate-400">
                                <svg class="w-12 h-12 stroke-[1.25]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.602 10.602z" />
                                </svg>
                            </div>
                            <div class="space-y-1">
                                <h3 class="text-sm font-bold text-slate-800 dark:text-white">Sin existencias registradas</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    No se encontraron lotes activos para esta búsqueda con los filtros aplicados.
                                </p>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
        @else
            <!-- VISTA EN MODO LISTA (TABLA SAAS PREMIUM) -->
            <div class="glass-card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50/60 dark:bg-slate-950/20 text-slate-400 dark:text-slate-500 font-bold uppercase tracking-wider border-b dark:border-slate-800">
                                <th class="px-6 py-4">Presentación / Producto</th>
                                <th class="px-6 py-4">Categoría</th>
                                <th class="px-6 py-4">Precio Venta</th>
                                <th class="px-6 py-4">Stock Mínimo</th>
                                <th class="px-6 py-4">Stock Actual</th>
                                <th class="px-6 py-4">Estado</th>
                                <th class="px-6 py-4 text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/40 text-slate-700 dark:text-slate-300">
                            @forelse ($this->existencias as $item)
                                @php
                                    $isAgotado = $item->total_stock == 0;
                                    $isBajo = !$isAgotado && $item->total_stock <= $item->max_stock_minimo;
                                @endphp
                                <tr class="hover:bg-slate-50/40 dark:hover:bg-slate-900/30 transition duration-150">
                                    <td class="px-6 py-4 flex items-center gap-3.5">
                                        <div class="relative w-10 h-10 rounded-xl overflow-hidden bg-slate-100 dark:bg-slate-800 border dark:border-slate-700/40 flex items-center justify-center shrink-0">
                                            @if ($item->imagen)
                                                <img src="{{ url('storage/' . $item->imagen) }}" class="w-full h-full object-cover" />
                                            @else
                                                <div class="absolute inset-0 bg-gradient-to-tr from-indigo-500/10 to-violet-500/5"></div>
                                                <span class="text-xs">📦</span>
                                            @endif
                                        </div>
                                        <div>
                                            <h4 class="font-extrabold text-slate-900 dark:text-white truncate max-w-[200px]">{{ $item->producto_nombre }}</h4>
                                            <span class="text-[10px] text-slate-400 dark:text-slate-500 font-semibold">{{ $item->tipo_presentacion }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($item->categoria_nombre)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border dark:border-slate-700/60">
                                                {{ $item->categoria_nombre }}
                                            </span>
                                        @else
                                            <span class="text-slate-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 font-bold text-slate-900 dark:text-slate-200">
                                        @if ($item->min_precio != $item->max_precio)
                                            S/ {{ number_format($item->min_precio, 2) }} - S/ {{ number_format($item->max_precio, 2) }}
                                        @else
                                            S/ {{ number_format($item->min_precio, 2) }}
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 font-semibold text-slate-500">
                                        {{ $item->max_stock_minimo }} uds
                                    </td>
                                    <td class="px-6 py-4 font-extrabold">
                                        <span class="{{ $isAgotado ? 'text-rose-600' : ($isBajo ? 'text-amber-600' : 'text-slate-800 dark:text-slate-200') }}">
                                            {{ $item->total_stock }} uds
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($isAgotado)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[9px] font-extrabold bg-rose-100 text-rose-700 border border-rose-300 dark:bg-rose-950/20 dark:text-rose-400 dark:border-rose-900/40">
                                                Agotado
                                            </span>
                                        @elseif ($isBajo)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[9px] font-extrabold bg-amber-100 text-amber-700 border border-amber-300 dark:bg-amber-950/20 dark:text-amber-400 dark:border-amber-900/40 alert-pulse">
                                                Stock Bajo
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[9px] font-extrabold bg-emerald-100 text-emerald-700 border border-emerald-300 dark:bg-emerald-950/20 dark:text-emerald-400 dark:border-emerald-900/40">
                                                Normal
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <button type="button" 
                                                wire:click="verLotes({{ $item->producto_id }}, {{ $item->producto_presentacion_id }})"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 text-[10px] font-bold text-white bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 rounded-xl transition shadow-sm">
                                            <span>👁 Ver Lotes</span>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-slate-400 font-medium">
                                        No se encontraron registros de existencias.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Paginación -->
        @if ($this->existencias->hasPages())
            <div class="px-5 py-4 bg-white/40 dark:bg-slate-900/20 border border-slate-200/50 dark:border-slate-800/40 rounded-2xl">
                {{ $this->existencias->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL DETALLE DE LOTES (DRAWER PREMIUM) -->
    <div x-data="{ open: @entangle('showLotesModal') }" 
         x-show="open" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-end p-4 overflow-y-auto"
         style="display: none;">
        
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity"
             @click="open = false">
        </div>

        <!-- Drawer Content -->
        <div class="relative bg-white dark:bg-[#0c101d] border-l border-slate-200 dark:border-[#1c243a] w-full max-w-xl h-full flex flex-col justify-between shadow-2xl transition-all z-10 animate-fade-in-right">
            
            <!-- Cabecera -->
            <div class="flex items-center justify-between px-6 py-4.5 border-b border-slate-100 dark:border-[#1d2745]/30 bg-slate-50/50 dark:bg-slate-950/20">
                <div>
                    <h2 class="text-sm font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                        <span class="p-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-500/10">
                            <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5c-.621 0-1.125-.504-1.125-1.125v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5c-.621 0-1.125-.504-1.125-1.125v-4.5z" />
                            </svg>
                        </span>
                        <span>Detalle de Lotes</span>
                    </h2>
                    @if ($selectedPresentacion)
                        <p class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 mt-1">
                            {{ $selectedPresentacion->producto?->nombre }} ({{ $selectedPresentacion->tipo_presentacion }})
                        </p>
                    @endif
                </div>

                <button type="button" 
                        @click="open = false"
                        class="p-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-900 text-slate-400 hover:text-slate-600 dark:text-slate-500 dark:hover:text-slate-300 transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Cuerpo / Listado -->
            <div class="p-6 flex-1 overflow-y-auto space-y-5 scrollbar-thin">
                
                <!-- Acciones Generales del Drawer -->
                <div class="grid grid-cols-2 gap-3">
                    <button type="button" 
                            wire:click="openGeneralPrice"
                            class="inline-flex items-center justify-center gap-1.5 px-3 py-2.5 text-[11px] font-bold text-white bg-indigo-600 hover:bg-indigo-500 active:scale-95 transition rounded-xl shadow-sm">
                        <span>⚡ Asignar Precio General</span>
                    </button>
                    <button type="button" 
                            wire:click="openStockMinimo"
                            class="inline-flex items-center justify-center gap-1.5 px-3 py-2.5 text-[11px] font-bold text-slate-700 dark:text-slate-200 bg-slate-100 hover:bg-slate-250 dark:bg-slate-800 dark:hover:bg-slate-700 active:scale-95 transition rounded-xl border dark:border-slate-700">
                        <span>⚠️ Configurar Stock Mínimo</span>
                    </button>
                </div>

                <!-- Listado de lotes (Mini cards premium) -->
                <div class="space-y-4">
                    <h3 class="text-[11px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Lotes de Producto</h3>
                    
                    @forelse ($lotesDetails as $record)
                        @php
                            $expiry = $record->lotePresentacion?->lote?->fecha_vencimiento;
                            $isExpired = $expiry && $expiry->isPast();
                            $isNearExpiry = $expiry && !$isExpired && $expiry->diffInDays(now()) <= 90;
                        @endphp
                        <div class="p-4 bg-slate-50 dark:bg-slate-950/40 border border-slate-100 dark:border-slate-800/85 rounded-2xl hover:border-indigo-500/30 transition-all">
                            <div class="flex justify-between items-start gap-4">
                                <div class="space-y-2 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="text-xs font-extrabold text-slate-900 dark:text-white bg-slate-200/50 dark:bg-slate-800 px-2 py-0.5 rounded-lg border dark:border-slate-700/60">
                                            Lote: {{ $record->lotePresentacion?->lote?->codigo_lote ?? 'Sin código' }}
                                        </span>
                                        
                                        <!-- Expiry Badge -->
                                        @if ($expiry)
                                            @if ($isExpired)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[9px] font-bold bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20">
                                                    ⚠️ Vencido ({{ $expiry->format('d/m/Y') }})
                                                </span>
                                            @elseif ($isNearExpiry)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[9px] font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 alert-pulse">
                                                    ⏳ Por vencer ({{ $expiry->format('d/m/Y') }})
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[9px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                                    📅 Vence: {{ $expiry->format('d/m/Y') }}
                                                </span>
                                            @endif
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[9px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500">
                                                Sin vencimiento
                                            </span>
                                        @endif

                                        <!-- Sucursal Badge (if viewing all sucursales) -->
                                        @if (!$selectedSucursalId)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[9px] font-bold bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20">
                                                🏢 {{ $record->sucursal?->nombre_sucursal }}
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Datos de stock y precios -->
                                    <div class="grid grid-cols-2 gap-4 mt-3 text-xs text-slate-500 dark:text-slate-400 font-medium">
                                        <div class="space-y-1">
                                            <p class="flex justify-between border-b dark:border-slate-800/80 pb-1">
                                                <span>Stock Actual:</span>
                                                <strong class="text-slate-800 dark:text-slate-200">{{ $record->lotePresentacion?->stock ?? 0 }} uds</strong>
                                            </p>
                                            <p class="flex justify-between border-b dark:border-slate-800/80 pb-1">
                                                <span>P. Mayorista:</span>
                                                <strong class="text-slate-800 dark:text-slate-200">S/ {{ number_format($record->precio_mayorista, 2) }}</strong>
                                            </p>
                                        </div>
                                        <div class="space-y-1">
                                            <p class="flex justify-between border-b dark:border-slate-800/80 pb-1">
                                                <span>Precio Venta:</span>
                                                <strong class="text-indigo-600 dark:text-indigo-400">S/ {{ number_format($record->precio, 2) }}</strong>
                                            </p>
                                            <p class="flex justify-between border-b dark:border-slate-800/80 pb-1">
                                                <span>Mín. Mayorista:</span>
                                                <strong class="text-slate-800 dark:text-slate-200">desde {{ $record->minimo_mayorista }} uds</strong>
                                            </p>
                                        </div>
                                    </div>
                                    <p class="flex justify-between text-[10px] text-slate-400 font-semibold pt-1">
                                        <span>Precio Compra: S/ {{ number_format($record->lotePresentacion?->precio_compra ?? 0, 2) }}</span>
                                        <span>Stock Mínimo: {{ $record->stock_minimo }} uds</span>
                                    </p>
                                </div>

                                <!-- Acciones del Lote -->
                                <div class="flex flex-col items-end gap-3 shrink-0">
                                    <!-- Toggle Activo -->
                                    <button type="button" 
                                            wire:click="toggleActivo({{ $record->id }})"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl text-[10px] font-extrabold transition-all border {{ $record->activo ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 border-transparent' }}">
                                        {{ $record->activo ? '✅ Activo' : '❌ Inactivo' }}
                                    </button>

                                    <!-- Botón Editar Precio Lote -->
                                    <button type="button" 
                                            wire:click="openEditPrice({{ $record->id }})"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 text-[10px] font-bold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition shadow-sm">
                                        <span>💲 Controlar Precio</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 dark:text-slate-500 text-center py-4">No hay lotes registrados para esta sucursal.</p>
                    @endforelse
                </div>

            </div>

            <!-- Footer -->
            <div class="flex items-center justify-end gap-3 p-4 bg-slate-50 dark:bg-slate-950/20 border-t border-slate-100 dark:border-[#1d2745]/30">
                <button type="button" 
                        @click="open = false"
                        class="px-5 py-2.5 text-xs font-bold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl border dark:border-slate-700 transition">
                    Cerrar
                </button>
            </div>

        </div>
    </div>

    <!-- MODAL CONFIGURAR STOCK MÍNIMO -->
    <div x-data="{ openMin: @entangle('showStockMinimoModal') }" 
         x-show="openMin" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto"
         style="display: none;">
        
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity"
             @click="openMin = false">
        </div>

        <!-- Content -->
        <div class="relative bg-white dark:bg-[#0c101d] border border-slate-200 dark:border-[#1c243a] rounded-3xl w-full max-w-md overflow-hidden shadow-2xl transition-all z-10">
            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-[#1d2745]/30 bg-slate-50/50 dark:bg-slate-950/20">
                <h3 class="text-sm font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="p-1.5 rounded-lg bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400">
                        ⚠️
                    </span>
                    <span>Configurar Stock Mínimo</span>
                </h3>
                <button type="button" 
                        @click="openMin = false"
                        class="text-slate-400 hover:text-slate-500">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Form -->
            <form wire:submit.prevent="saveStockMinimo" class="p-6 space-y-4">
                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                    Define el stock mínimo para esta presentación. Esto disparará alertas visuales cuando el inventario esté por debajo del límite.
                </p>

                <div class="space-y-1.5">
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Stock Mínimo *</label>
                    <input type="number" 
                           wire:model="editingStockMinimo"
                           class="w-full px-4 py-2.5 text-xs rounded-xl border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                    @error('editingStockMinimo') <span class="text-xs text-rose-500 font-medium block mt-1">{{ $message }}</span> @enderror
                </div>

                <!-- Footer -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-[#1d2745]/30">
                    <button type="button" 
                            @click="openMin = false"
                            class="px-4.5 py-2.5 text-xs font-bold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl transition">
                        Cancelar
                    </button>
                    <button type="submit" 
                            class="px-4.5 py-2.5 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 active:scale-95 transition-all shadow-md shadow-indigo-500/20 rounded-xl">
                        <span>Save Threshold</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDITAR PRECIO INDIVIDUAL -->
    <div x-data="{ openEdit: @entangle('showEditPriceModal') }" 
         x-show="openEdit" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto"
         style="display: none;">
        
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity"
             @click="openEdit = false">
        </div>

        <!-- Content -->
        <div class="relative bg-white dark:bg-[#0c101d] border border-slate-200 dark:border-[#1c243a] rounded-3xl w-full max-w-md overflow-hidden shadow-2xl transition-all z-10">
            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-[#1d2745]/30 bg-slate-50/50 dark:bg-slate-950/20">
                <h3 class="text-sm font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="p-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400">
                        💲
                    </span>
                    <span>Controlar Precio Lote</span>
                </h3>
                <button type="button" 
                        @click="openEdit = false"
                        class="text-slate-400 hover:text-slate-500">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Form -->
            <form wire:submit.prevent="saveIndividualPrice" class="p-6 space-y-4">
                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                    Modificar precios y escalas del lote: <strong class="text-slate-800 dark:text-white">{{ $editingLoteCodigo }}</strong>
                </p>

                <!-- Precio Venta -->
                <div class="space-y-1.5">
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Precio Venta (Público) *</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 font-bold text-xs">
                            S/
                        </div>
                        <input type="number" 
                               step="0.01"
                               wire:model="editingPrecio"
                               class="w-full pl-8 pr-4 py-2.5 text-xs rounded-xl border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                    </div>
                    @error('editingPrecio') <span class="text-xs text-rose-500 font-medium block mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <!-- Precio Mayorista -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Precio Mayorista *</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 font-bold text-xs">
                                S/
                            </div>
                            <input type="number" 
                                   step="0.01"
                                   wire:model="editingPrecioMayorista"
                                   class="w-full pl-8 pr-4 py-2.5 text-xs rounded-xl border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                        </div>
                        @error('editingPrecioMayorista') <span class="text-xs text-rose-500 font-medium block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <!-- Minimo Mayorista -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Mínimo Unidades *</label>
                        <input type="number" 
                               wire:model="editingMinimoMayorista"
                               class="w-full px-4 py-2.5 text-xs rounded-xl border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                        @error('editingMinimoMayorista') <span class="text-xs text-rose-500 font-medium block mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-[#1d2745]/30">
                    <button type="button" 
                            @click="openEdit = false"
                            class="px-4.5 py-2.5 text-xs font-bold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl transition">
                        Cancelar
                    </button>
                    <button type="submit" 
                            class="px-4.5 py-2.5 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 active:scale-95 transition-all shadow-md shadow-indigo-500/20 rounded-xl">
                        <span>✏️ Editar Precio</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDITAR PRECIO GENERAL -->
    <div x-data="{ openGen: @entangle('showGeneralPriceModal') }" 
         x-show="openGen" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto"
         style="display: none;">
        
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity"
             @click="openGen = false">
        </div>

        <!-- Content -->
        <div class="relative bg-white dark:bg-[#0c101d] border border-slate-200 dark:border-[#1c243a] rounded-3xl w-full max-w-md overflow-hidden shadow-2xl transition-all z-10">
            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-[#1d2745]/30 bg-slate-50/50 dark:bg-slate-950/20">
                <h3 class="text-sm font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="p-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400">
                        ⚡
                    </span>
                    <span>Asignar Precio General</span>
                </h3>
                <button type="button" 
                        @click="openGen = false"
                        class="text-slate-400 hover:text-slate-500">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Form -->
            <form wire:submit.prevent="saveGeneralPrice" class="p-6 space-y-4">
                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                    Establece el mismo precio de venta y escala mayorista para **todos los lotes** de la presentación actual de forma masiva.
                </p>

                <!-- Precio Venta -->
                <div class="space-y-1.5">
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Precio Venta General *</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 font-bold text-xs">
                            S/
                        </div>
                        <input type="number" 
                               step="0.01"
                               wire:model="generalPrecio"
                               class="w-full pl-8 pr-4 py-2.5 text-xs rounded-xl border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                    </div>
                    @error('generalPrecio') <span class="text-xs text-rose-500 font-medium block mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <!-- Precio Mayorista -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Precio Mayorista General *</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 font-bold text-xs">
                                S/
                            </div>
                            <input type="number" 
                                   step="0.01"
                                   wire:model="generalPrecioMayorista"
                                   class="w-full pl-8 pr-4 py-2.5 text-xs rounded-xl border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                        </div>
                        @error('generalPrecioMayorista') <span class="text-xs text-rose-500 font-medium block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <!-- Minimo Mayorista -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Mínimo Unidades *</label>
                        <input type="number" 
                               wire:model="generalMinimoMayorista"
                               class="w-full px-4 py-2.5 text-xs rounded-xl border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                        @error('generalMinimoMayorista') <span class="text-xs text-rose-500 font-medium block mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-[#1d2745]/30">
                    <button type="button" 
                            @click="openGen = false"
                            class="px-4.5 py-2.5 text-xs font-bold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl transition">
                        Cancelar
                    </button>
                    <button type="submit" 
                            class="px-4.5 py-2.5 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 active:scale-95 transition-all shadow-md shadow-indigo-500/20 rounded-xl">
                        <span>⚡ Asignar Precio General</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-filament-panels::page>
