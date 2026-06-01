<x-filament-panels::page>
    <div class="productos-root space-y-6 animate-fade-in">
        <!-- Contenido Principal -->
        <div class="space-y-6">
            <!-- Tarjetas de Estadísticas (KPIs) -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Total Productos -->
                <div class="kpi-card kpi-indigo">
                    <div class="flex justify-between items-start">
                        <div class="space-y-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Total Productos</span>
                            <div class="text-3xl font-black text-slate-950 dark:text-white">
                                {{ $this->stats['total'] }}
                            </div>
                        </div>
                        <div class="p-2 bg-indigo-500/10 text-indigo-500 rounded-xl">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Productos Activos -->
                <div class="kpi-card kpi-emerald">
                    <div class="flex justify-between items-start">
                        <div class="space-y-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Activos</span>
                            <div class="text-3xl font-black text-slate-950 dark:text-white">
                                {{ $this->stats['actives'] }}
                            </div>
                        </div>
                        <div class="p-2 bg-emerald-500/10 text-emerald-500 rounded-xl">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Stock Bajo / Alerta -->
                <div class="kpi-card kpi-amber">
                    <div class="flex justify-between items-start">
                        <div class="space-y-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Stock Crítico</span>
                            <div class="text-3xl font-black text-slate-950 dark:text-white">
                                {{ $this->stats['lowStock'] }}
                            </div>
                        </div>
                        <div class="p-2 bg-amber-500/10 text-amber-500 rounded-xl">
                            <svg class="w-6 h-6 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botón Nuevo Producto (Verde Premium) -->
            <div class="flex justify-end pt-2">
                <button type="button" 
                        wire:click="openCreateModal"
                        class="inline-flex items-center gap-2 px-6 py-3.5 text-sm font-extrabold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 active:scale-95 transition-all shadow-lg shadow-emerald-500/20 rounded-2xl">
                    <span class="text-base">➕</span>
                    <span>Nuevo Producto</span>
                </button>
            </div>

            <!-- Tabla, Búsqueda y Filtros -->
            <div class="glass-card overflow-hidden">
                <!-- Barra de Control -->
                <div class="p-5 border-b border-slate-200/50 dark:border-slate-800/40 flex flex-col xl:flex-row xl:items-center justify-between gap-4">
                    <!-- Buscador -->
                    <div class="relative flex-1 max-w-md">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.602 10.602z" />
                            </svg>
                        </div>
                        <input type="text" 
                               wire:model.live="search"
                               placeholder="Buscar productos por nombre, código..."
                               class="w-full pl-10 pr-4 py-2.5 text-sm rounded-xl border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition">
                    </div>

                    <!-- Filtros Select y Pills -->
                    <div class="flex flex-wrap items-center gap-3">
                        <!-- Categoría Select -->
                        <select wire:model.live="categoria_id" 
                                class="rounded-xl border-slate-200 bg-white dark:bg-slate-900 dark:border-slate-800 text-slate-700 dark:text-slate-355 text-xs px-3.5 py-2 focus:ring-2 focus:ring-indigo-500 shadow-sm max-w-[150px]">
                            <option value="all">Todas las Categorías</option>
                            @foreach ($this->categorias as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                            @endforeach
                        </select>

                        <!-- Marca Select -->
                        <select wire:model.live="marca_id" 
                                class="rounded-xl border-slate-200 bg-white dark:bg-slate-900 dark:border-slate-800 text-slate-700 dark:text-slate-355 text-xs px-3.5 py-2 focus:ring-2 focus:ring-indigo-500 shadow-sm max-w-[140px]">
                            <option value="all">Todas las Marcas</option>
                            @foreach ($this->marcas as $mrc)
                                <option value="{{ $mrc->id }}">{{ $mrc->nombre }}</option>
                            @endforeach
                        </select>

                        <!-- Stock Filtro Select -->
                        <select wire:model.live="stock_filtro" 
                                class="rounded-xl border-slate-200 bg-white dark:bg-slate-900 dark:border-slate-800 text-slate-700 dark:text-slate-355 text-xs px-3.5 py-2 focus:ring-2 focus:ring-indigo-500 shadow-sm">
                            <option value="all">Filtro de Stock (Todos)</option>
                            <option value="in">En Stock (Disponible)</option>
                            <option value="low">Stock Bajo (Crítico)</option>
                            <option value="out">Agotados</option>
                        </select>

                        <!-- Filtros de Estado Pills -->
                        <div class="flex items-center gap-1.5 p-1 bg-slate-100 dark:bg-slate-900/80 rounded-xl border dark:border-slate-800/60">
                            <button type="button" 
                                    wire:click="$set('estado', 'active')"
                                    class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all {{ $estado === 'active' ? 'bg-white dark:bg-slate-800 text-slate-950 dark:text-white shadow-sm' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-300' }}">
                                Activos
                            </button>
                            <button type="button" 
                                    wire:click="$set('estado', 'trashed')"
                                    class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all {{ $estado === 'trashed' ? 'bg-white dark:bg-slate-800 text-slate-950 dark:text-white shadow-sm' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-300' }}">
                                Papelera
                            </button>
                        </div>

                        <!-- Alternador Grid / Lista -->
                        <div class="flex items-center gap-1 p-1 bg-slate-100 dark:bg-slate-900/80 rounded-xl border dark:border-slate-800/60">
                            <button type="button" 
                                    wire:click="toggleViewMode('grid')" 
                                    class="p-1.5 rounded-lg transition-all {{ $viewMode === 'grid' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-sm' : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-300' }}"
                                    title="Vista de Tarjetas">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                                </svg>
                            </button>
                            <button type="button" 
                                    wire:click="toggleViewMode('table')" 
                                    class="p-1.5 rounded-lg transition-all {{ $viewMode === 'table' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-sm' : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-300' }}"
                                    title="Vista de Tabla">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 5.25h16.5m-16.5-10.5h16.5" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- CONTENIDO DE PRODUCTOS -->
                <div class="p-6">
                    @if ($viewMode === 'grid')
                        <!-- ================= VISTA DE TARJETAS (GRID) ================= -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                            @forelse ($this->productos as $producto)
                                @php
                                    $isTrashed = $producto->trashed();
                                    
                                    // Extract image
                                    $imgPresentation = $producto->presentaciones
                                        ->filter(fn($p) => filled($p->imagen))
                                        ->sortByDesc(fn($p) => $p->unidadMedida?->abreviatura === 'und')
                                        ->first();
                                    $imagenUrl = $imgPresentation?->imagen_url;

                                    // Extract prices
                                    $precios = $producto->presentaciones->flatMap->productoSucursales->pluck('precio')->filter()->map(fn($p) => (float)$p);
                                    $minPrice = $precios->min();
                                    $maxPrice = $precios->max();

                                    // Calculate stock
                                    $stockTotal = $producto->presentaciones->flatMap->lotePresentaciones->sum('stock');
                                @endphp
                                <div class="relative flex flex-col overflow-hidden rounded-2xl border transition-all duration-300 group hover:shadow-lg hover:-translate-y-1 {{ $isTrashed ? 'bg-rose-50/10 dark:bg-rose-950/5 border-rose-300/40 dark:border-rose-900/30 opacity-90 border-l-4 border-l-rose-500' : 'bg-white dark:bg-slate-900 border-slate-200/80 dark:border-slate-800' }}">
                                    
                                    <!-- Image / Thumbnail Area -->
                                    <div class="aspect-square bg-slate-50 dark:bg-slate-950/45 flex items-center justify-center p-4 relative border-b border-slate-100 dark:border-slate-800/40">
                                        @if ($imagenUrl)
                                            <img src="{{ $imagenUrl }}" alt="{{ $producto->nombre }}" class="h-full w-full object-contain p-1.5 transition-transform duration-500 group-hover:scale-105" loading="lazy">
                                        @else
                                            <div class="flex flex-col items-center gap-1.5 text-slate-350 dark:text-slate-600 group-hover:scale-105 transition duration-300">
                                                <span class="text-5xl">📦</span>
                                            </div>
                                        @endif

                                        <!-- Badge de Stock Crítico o Estado en la imagen -->
                                        <div class="absolute top-2.5 left-2.5 flex flex-col gap-1.5 z-10">
                                            @if ($isTrashed)
                                                <span class="px-2 py-0.5 rounded-md text-[9px] font-extrabold bg-rose-500/20 text-rose-600 dark:text-rose-450 border border-rose-500/30 uppercase tracking-wider">
                                                    Eliminado
                                                </span>
                                            @else
                                                @if ($stockTotal === 0)
                                                    <span class="px-2 py-0.5 rounded-md text-[9px] font-extrabold bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20 uppercase tracking-wider">
                                                        🔴 Agotado
                                                    </span>
                                                @elseif ($stockTotal <= 5)
                                                    <span class="px-2 py-0.5 rounded-md text-[9px] font-extrabold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 uppercase tracking-wider">
                                                        🟠 Stock Bajo
                                                    </span>
                                                @else
                                                    <span class="px-2 py-0.5 rounded-md text-[9px] font-extrabold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 uppercase tracking-wider">
                                                        🟢 Disponible
                                                    </span>
                                                @endif
                                            @endif
                                        </div>

                                        <!-- Badge de Presentaciones Count -->
                                        <div class="absolute bottom-2.5 right-2.5 z-10">
                                            <span class="px-2 py-0.5 rounded-md text-[9px] font-extrabold bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 uppercase tracking-wider">
                                                {{ $producto->presentaciones_count }} vars.
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Content Info Area -->
                                    <div class="p-4 flex-1 flex flex-col justify-between gap-4">
                                        <div class="space-y-2">
                                            <div class="flex flex-wrap gap-1">
                                                @if ($producto->categoria)
                                                    <span class="px-1.5 py-0.5 rounded text-[8px] font-extrabold bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-200/50 dark:border-slate-700/50 uppercase tracking-wider">
                                                        {{ $producto->categoria->nombre }}
                                                    </span>
                                                @endif
                                                @if ($producto->marca)
                                                    <span class="px-1.5 py-0.5 rounded text-[8px] font-extrabold bg-purple-500/5 text-purple-600 dark:text-purple-400 border border-purple-500/10 uppercase tracking-wider">
                                                        {{ $producto->marca->nombre }}
                                                    </span>
                                                @endif
                                            </div>

                                            <h3 class="text-sm font-extrabold text-slate-900 dark:text-white leading-snug group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors line-clamp-2 min-h-[2.5rem]">
                                                {{ $producto->nombre }}
                                            </h3>

                                            <!-- Stock & Precio Rango -->
                                            <div class="grid grid-cols-2 gap-2 pt-1">
                                                <div>
                                                    <span class="block text-[10px] text-slate-400 dark:text-slate-500 uppercase font-bold tracking-wider">Stock Total</span>
                                                    <span class="text-xs font-extrabold text-slate-700 dark:text-slate-300">
                                                        {{ $stockTotal }} unidades
                                                    </span>
                                                </div>
                                                <div>
                                                    <span class="block text-[10px] text-slate-400 dark:text-slate-500 uppercase font-bold tracking-wider">Rango Precios</span>
                                                    <span class="text-xs font-extrabold text-slate-850 dark:text-white font-mono">
                                                        @if ($minPrice !== null && $maxPrice !== null)
                                                            {{ $minPrice === $maxPrice ? 'S/. ' . number_format($minPrice, 2) : 'S/. ' . number_format($minPrice, 2) . ' - ' . number_format($maxPrice, 2) }}
                                                        @else
                                                            S/. —
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>

                                            <!-- Meta info -->
                                            <div class="flex items-center justify-between text-[9px] text-slate-400 pt-1">
                                                <span>Cód: {{ $producto->codigo_interno ?: 'S/C' }}</span>
                                                @if ($isTrashed && $producto->deleted_at)
                                                    <span class="text-rose-600 dark:text-rose-450 font-bold bg-rose-500/5 px-1.5 py-0.5 rounded border border-rose-500/10">
                                                        🗑️ {{ $producto->deleted_at->format('d/m/Y H:i') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Acciones Rápidas del Card -->
                                        <div class="flex flex-col gap-2 pt-3 border-t border-slate-100 dark:border-slate-800/40">
                                            <!-- Ver Presentaciones -->
                                            <button type="button" 
                                                    wire:click="verPresentaciones({{ $producto->id }})"
                                                    class="w-full inline-flex items-center justify-center gap-1.5 px-3.5 py-2 text-xs font-bold text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl shadow active:scale-95 transition-all">
                                                <span class="text-sm">👁</span>
                                                <span>Ver Presentaciones</span>
                                            </button>

                                            <div class="flex gap-2">
                                                @if ($isTrashed)
                                                    <!-- Restaurar -->
                                                    <button type="button" 
                                                            wire:click="restore({{ $producto->id }})"
                                                            class="flex-1 inline-flex items-center justify-center gap-1.5 px-3.5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 rounded-xl shadow-md active:scale-95 transition-all">
                                                        <span class="text-sm">♻️</span>
                                                        <span>Restaurar</span>
                                                    </button>
                                                    
                                                    <!-- Eliminar Definitivo -->
                                                    <button type="button" 
                                                            wire:click="confirmDelete({{ $producto->id }})"
                                                            class="flex-1 inline-flex items-center justify-center gap-1.5 px-3.5 py-2 text-xs font-bold text-white bg-red-700 hover:bg-red-650 rounded-xl shadow-md active:scale-95 transition-all">
                                                        <span class="text-sm">🗑️</span>
                                                        <span>Eliminar Definitivo</span>
                                                    </button>
                                                @else
                                                    <!-- Editar -->
                                                    <button type="button" 
                                                            wire:click="openEditModal({{ $producto->id }})"
                                                            class="flex-1 inline-flex items-center justify-center gap-1.5 px-3.5 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-500 rounded-xl shadow-md active:scale-95 transition-all">
                                                        <span class="text-sm">✏️</span>
                                                        <span>Editar</span>
                                                    </button>

                                                    <!-- Eliminar -->
                                                    <button type="button" 
                                                            wire:click="confirmDelete({{ $producto->id }})"
                                                            class="flex-1 inline-flex items-center justify-center gap-1.5 px-3.5 py-2 text-xs font-bold text-white bg-rose-600 hover:bg-rose-500 rounded-xl shadow-md active:scale-95 transition-all">
                                                        <span class="text-sm">🗑️</span>
                                                        <span>Eliminar</span>
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <!-- Estado Vacío en Grid -->
                                <div class="col-span-full py-16 text-center">
                                    <div class="max-w-md mx-auto space-y-4">
                                        <div class="inline-flex p-4 bg-slate-50 dark:bg-slate-900/60 rounded-2xl border border-slate-200/50 dark:border-slate-800/40 text-slate-400 shadow-sm">
                                            <span class="text-4xl">🔍</span>
                                        </div>
                                        <div class="space-y-1">
                                            <h3 class="text-sm font-bold text-slate-800 dark:text-white">No se encontraron productos</h3>
                                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                                No hay productos registrados que coincidan con la búsqueda o filtros actuales.
                                            </p>
                                        </div>
                                        @if (!empty($search) || $categoria_id !== 'all' || $marca_id !== 'all' || $stock_filtro !== 'all' || $estado !== 'active')
                                            <button type="button" 
                                                    wire:click="$set('search', ''); $set('categoria_id', 'all'); $set('marca_id', 'all'); $set('stock_filtro', 'all'); $set('estado', 'active');"
                                                    class="inline-flex items-center gap-1.5 px-4.5 py-2 text-xs font-bold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 rounded-xl shadow-sm transition dark:bg-slate-900/40 dark:border-slate-800 dark:text-slate-300">
                                                <span>🔄</span>
                                                <span>Limpiar filtros</span>
                                            </button>
                                        @else
                                            <button type="button" 
                                                    wire:click="openCreateModal"
                                                    class="inline-flex items-center gap-1.5 px-5 py-2.5 text-xs font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 rounded-xl shadow-md transition">
                                                <span class="text-sm">➕</span>
                                                <span>Nuevo Producto</span>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforelse
                        </div>

                    @else
                        <!-- ================= VISTA DE TABLA MINIMALISTA ================= -->
                        <div class="overflow-x-auto">
                            <table class="w-full border-collapse text-left text-sm">
                                <thead>
                                    <tr class="bg-slate-50/40 dark:bg-slate-950/20">
                                        <th class="py-4 px-6 font-semibold uppercase tracking-wider text-[11px] text-slate-400 dark:text-slate-500 border-b border-slate-100 dark:border-slate-800/60 bg-slate-50/40 dark:bg-slate-950/20">Imagen</th>
                                        <th class="py-4 px-6 font-semibold uppercase tracking-wider text-[11px] text-slate-400 dark:text-slate-500 border-b border-slate-100 dark:border-slate-800/60 bg-slate-50/40 dark:bg-slate-950/20">
                                            <button type="button" wire:click="sortBy('nombre')" class="inline-flex items-center gap-1 hover:text-slate-650 dark:hover:text-slate-350">
                                                <span>Nombre</span>
                                                @if ($sortField === 'nombre')
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $sortDirection === 'asc' ? 'M4.5 15.75l7.5-7.5 7.5 7.5' : 'M19.5 8.25l-7.5 7.5-7.5-7.5' }}" />
                                                    </svg>
                                                @endif
                                            </button>
                                        </th>
                                        <th class="py-4 px-6 font-semibold uppercase tracking-wider text-[11px] text-slate-400 dark:text-slate-500 border-b border-slate-100 dark:border-slate-800/60 bg-slate-50/40 dark:bg-slate-950/20">Categoría / Marca</th>
                                        <th class="py-4 px-6 font-semibold uppercase tracking-wider text-[11px] text-slate-400 dark:text-slate-500 border-b border-slate-100 dark:border-slate-800/60 bg-slate-50/40 dark:bg-slate-950/20">Cód. Interno</th>
                                        <th class="py-4 px-6 font-semibold uppercase tracking-wider text-[11px] text-slate-400 dark:text-slate-500 border-b border-slate-100 dark:border-slate-800/60 bg-slate-50/40 dark:bg-slate-950/20 text-center">Stock</th>
                                        <th class="py-4 px-6 font-semibold uppercase tracking-wider text-[11px] text-slate-400 dark:text-slate-500 border-b border-slate-100 dark:border-slate-800/60 bg-slate-50/40 dark:bg-slate-950/20">Rango Precios</th>
                                        @if ($estado === 'trashed')
                                            <th class="py-4 px-6 font-semibold uppercase tracking-wider text-[11px] text-slate-400 dark:text-slate-500 border-b border-slate-100 dark:border-slate-800/60 bg-slate-50/40 dark:bg-slate-950/20">Fecha Eliminación</th>
                                        @endif
                                        <th class="py-4 px-6 font-semibold uppercase tracking-wider text-[11px] text-slate-400 dark:text-slate-500 border-b border-slate-100 dark:border-slate-800/60 bg-slate-50/40 dark:bg-slate-950/20">Estado</th>
                                        <th class="py-4 px-6 font-semibold uppercase tracking-wider text-[11px] text-slate-400 dark:text-slate-500 border-b border-slate-100 dark:border-slate-800/60 bg-slate-50/40 dark:bg-slate-950/20 text-right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/40">
                                    @forelse ($this->productos as $producto)
                                        @php
                                            $isTrashed = $producto->trashed();
                                            
                                            // Extract image
                                            $imgPresentation = $producto->presentaciones
                                                ->filter(fn($p) => filled($p->imagen))
                                                ->sortByDesc(fn($p) => $p->unidadMedida?->abreviatura === 'und')
                                                ->first();
                                            $imagenUrl = $imgPresentation?->imagen_url;

                                            // Extract prices
                                            $precios = $producto->presentaciones->flatMap->productoSucursales->pluck('precio')->filter()->map(fn($p) => (float)$p);
                                            $minPrice = $precios->min();
                                            $maxPrice = $precios->max();

                                            // Calculate stock
                                            $stockTotal = $producto->presentaciones->flatMap->lotePresentaciones->sum('stock');
                                        @endphp
                                        <tr class="border-l-4 {{ $isTrashed ? 'bg-rose-50/10 dark:bg-rose-950/5 border-l-rose-500 opacity-85' : 'border-l-transparent hover:bg-slate-50/40 dark:hover:bg-slate-900/30' }} transition duration-150">
                                            <!-- Imagen -->
                                            <td class="py-4 px-6">
                                                <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0 border border-slate-200 dark:border-slate-800 overflow-hidden bg-slate-50 dark:bg-slate-950">
                                                    @if ($imagenUrl)
                                                        <img src="{{ $imagenUrl }}" class="w-full h-full object-contain p-1" loading="lazy">
                                                    @else
                                                        <span class="text-xl">📦</span>
                                                    @endif
                                                </div>
                                            </td>

                                            <!-- Nombre -->
                                            <td class="py-4 px-6">
                                                <div class="font-extrabold text-slate-900 dark:text-white leading-tight">
                                                    {{ $producto->nombre }}
                                                </div>
                                            </td>
                                            
                                            <!-- Categoría / Marca -->
                                            <td class="py-4 px-6">
                                                <div class="flex flex-col gap-1">
                                                    <span class="text-xs text-slate-650 dark:text-slate-350">
                                                        🏷️ {{ $producto->categoria?->nombre ?: 'Sin categoría' }}
                                                    </span>
                                                    <span class="text-[10px] text-slate-400 dark:text-slate-500">
                                                        🏢 {{ $producto->marca?->nombre ?: 'Sin marca' }}
                                                    </span>
                                                </div>
                                            </td>

                                            <!-- Código Interno -->
                                            <td class="py-4 px-6 text-xs font-mono text-slate-600 dark:text-slate-400">
                                                {{ $producto->codigo_interno ?: '—' }}
                                            </td>

                                            <!-- Stock -->
                                            <td class="py-4 px-6 text-center">
                                                @if ($isTrashed)
                                                    <span class="text-xs font-bold text-slate-450">—</span>
                                                @else
                                                    @if ($stockTotal === 0)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20">
                                                            Agotado
                                                        </span>
                                                    @elseif ($stockTotal <= 5)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                                                            {{ $stockTotal }} (Bajo)
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                                            {{ $stockTotal }} und.
                                                        </span>
                                                    @endif
                                                @endif
                                            </td>

                                            <!-- Rango Precios -->
                                            <td class="py-4 px-6 text-xs font-bold text-slate-700 dark:text-slate-300 font-mono">
                                                @if ($minPrice !== null && $maxPrice !== null)
                                                    {{ $minPrice === $maxPrice ? 'S/. ' . number_format($minPrice, 2) : 'S/. ' . number_format($minPrice, 2) . ' - ' . number_format($maxPrice, 2) }}
                                                @else
                                                    S/. —
                                                @endif
                                            </td>

                                            <!-- Fecha Eliminación (Condicional) -->
                                            @if ($estado === 'trashed')
                                                <td class="py-4 px-6 text-xs text-rose-600 dark:text-rose-400 font-bold">
                                                    {{ $producto->deleted_at ? $producto->deleted_at->format('d/m/Y H:i') : '—' }}
                                                </td>
                                            @endif

                                            <!-- Estado -->
                                            <td class="py-4 px-6">
                                                @if ($isTrashed)
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-600 dark:text-rose-450 border border-rose-500/20">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                        Eliminado
                                                    </span>
                                                @else
                                                    @if ($producto->activo)
                                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                            Activo
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-500/10 text-slate-650 dark:text-slate-400 border border-slate-500/20">
                                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                                            Inactivo
                                                        </span>
                                                    @endif
                                                @endif
                                            </td>

                                            <!-- Acciones de Fila -->
                                            <td class="py-4 px-6 text-right">
                                                <div class="flex items-center justify-end gap-3 flex-wrap">
                                                    <!-- Ver Presentaciones -->
                                                    <button type="button" 
                                                            wire:click="verPresentaciones({{ $producto->id }})"
                                                            class="inline-flex items-center gap-1 px-3.5 py-2 text-xs font-bold text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl shadow active:scale-95 transition-all">
                                                        <span class="text-sm">👁</span>
                                                        <span>Ver Presentaciones</span>
                                                    </button>

                                                    @if ($isTrashed)
                                                        <!-- Restaurar -->
                                                        <button type="button" 
                                                                wire:click="restore({{ $producto->id }})"
                                                                class="inline-flex items-center gap-1 px-3.5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 rounded-xl shadow-md shadow-emerald-500/10 active:scale-95 transition-all">
                                                            <span class="text-sm">♻️</span>
                                                            <span>Restaurar</span>
                                                        </button>
                                                        
                                                        <!-- Eliminar Definitivo -->
                                                        <button type="button" 
                                                                wire:click="confirmDelete({{ $producto->id }})"
                                                                class="inline-flex items-center gap-1 px-3.5 py-2 text-xs font-bold text-white bg-red-700 hover:bg-red-650 rounded-xl shadow-md shadow-red-700/10 active:scale-95 transition-all">
                                                            <span class="text-sm">🗑️</span>
                                                            <span>Eliminar Definitivo</span>
                                                        </button>
                                                    @else
                                                        <!-- Editar -->
                                                        <button type="button" 
                                                                wire:click="openEditModal({{ $producto->id }})"
                                                                class="inline-flex items-center gap-1 px-3.5 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-500 rounded-xl shadow-md shadow-blue-500/10 active:scale-95 transition-all">
                                                            <span class="text-sm">✏️</span>
                                                            <span>Editar</span>
                                                        </button>

                                                        <!-- Eliminar -->
                                                        <button type="button" 
                                                                wire:click="confirmDelete({{ $producto->id }})"
                                                                class="inline-flex items-center gap-1 px-3.5 py-2 text-xs font-bold text-white bg-rose-600 hover:bg-rose-500 rounded-xl shadow-md shadow-rose-500/10 active:scale-95 transition-all">
                                                            <span class="text-sm">🗑️</span>
                                                            <span>Eliminar</span>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <!-- Estado Vacío en Tabla -->
                                        <tr>
                                            <td colspan="{{ $estado === 'trashed' ? '9' : '8' }}" class="py-16 px-6 text-center">
                                                <div class="max-w-md mx-auto space-y-4">
                                                    <div class="inline-flex p-4 bg-slate-50 dark:bg-slate-900/60 rounded-2xl border border-slate-200/50 dark:border-slate-800/40 text-slate-400 shadow-sm">
                                                        <span class="text-4xl">🔍</span>
                                                    </div>
                                                    <div class="space-y-1">
                                                        <h3 class="text-sm font-bold text-slate-800 dark:text-white">No se encontraron productos</h3>
                                                        <p class="text-xs text-slate-500 dark:text-slate-400">
                                                            No hay productos registrados que coincidan con la búsqueda o filtros actuales.
                                                        </p>
                                                    </div>
                                                    @if (!empty($search) || $categoria_id !== 'all' || $marca_id !== 'all' || $stock_filtro !== 'all' || $estado !== 'active')
                                                        <button type="button" 
                                                                wire:click="$set('search', ''); $set('categoria_id', 'all'); $set('marca_id', 'all'); $set('stock_filtro', 'all'); $set('estado', 'active');"
                                                                class="inline-flex items-center gap-1.5 px-4.5 py-2 text-xs font-bold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 rounded-xl shadow-sm transition dark:bg-slate-900/40 dark:border-slate-800 dark:text-slate-300">
                                                            <span>🔄</span>
                                                            <span>Limpiar filtros</span>
                                                        </button>
                                                    @else
                                                        <button type="button" 
                                                                wire:click="openCreateModal"
                                                                class="inline-flex items-center gap-1.5 px-5 py-2.5 text-xs font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 rounded-xl shadow-md transition">
                                                            <span class="text-sm">➕</span>
                                                            <span>Nuevo Producto</span>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                <!-- Paginación -->
                @if ($this->productos->hasPages())
                    <div class="px-6 py-4 border-t border-slate-200/50 dark:border-slate-800/40 bg-slate-50/20 dark:bg-slate-950/5">
                        {{ $this->productos->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- PRESENTATIONS SLIDE DRAWER (PANEL LATERAL) -->
    <div x-data="{ open: @entangle('selectedProductForPresentationsId') }"
         x-show="open"
         x-cloak
         class="fixed inset-0 z-50 overflow-hidden"
         style="display: none;">
        <div class="absolute inset-0 overflow-hidden">
            <!-- Backdrop -->
            <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity"
                 x-show="open"
                 x-transition:enter="ease-in-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in-out duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="open = null">
            </div>

            <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                <!-- Panel Content -->
                <div class="pointer-events-auto w-screen max-w-3xl border-l border-slate-200 dark:border-[#1c243a] shadow-2xl bg-white dark:bg-[#0c101d] flex flex-col h-full transform transition-all"
                     x-show="open"
                     x-transition:enter="transform transition ease-in-out duration-300"
                     x-transition:enter-start="translate-x-full"
                     x-transition:enter-end="translate-x-0"
                     x-transition:leave="transform transition ease-in-out duration-200"
                     x-transition:leave-start="translate-x-0"
                     x-transition:leave-end="translate-x-full">
                    
                    <!-- Header -->
                    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-[#1d2745]/30">
                        <h2 class="text-base font-extrabold text-slate-800 dark:text-white flex items-center gap-2">
                            <span class="p-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-500/10">
                                👁
                            </span>
                            <span>Gestión de Presentaciones / Variantes</span>
                        </h2>
                        <button type="button" 
                                @click="open = null"
                                class="p-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-900 text-slate-400 hover:text-slate-650 dark:text-slate-550 dark:hover:text-slate-350 transition">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Scrollable content -->
                    <div class="flex-1 overflow-y-auto p-6 space-y-6">
                        @if ($selectedProductForPresentationsId)
                            @php
                                $selProd = \App\Models\Producto::find($selectedProductForPresentationsId);
                            @endphp
                            @if ($selProd)
                                @livewire('almacen.product-presentations-manager', ['record' => $selProd], key($selProd->id))
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL REGISTRAR / EDITAR PRODUCTO -->
    <div x-data="{ open: @entangle('showModal') }" 
         x-show="open" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto"
         style="display: none;">
        
        <!-- Backdrop/Overlay -->
        <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity"
             @click="open = false">
        </div>

        <!-- Ventana del Modal -->
        <div class="relative bg-white dark:bg-[#0c101d] border border-slate-200 dark:border-[#1c243a] rounded-3xl w-full max-w-2xl overflow-hidden shadow-2xl transition-all animate-fade-in my-8 z-10">
            <!-- Cabecera -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-[#1d2745]/30">
                <h2 class="text-base font-extrabold text-slate-800 dark:text-white flex items-center gap-2">
                    <span class="p-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-500/10">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                        </svg>
                    </span>
                    <span>{{ $productoId ? 'Editar Producto' : 'Nuevo Producto' }}</span>
                </h2>
                <button type="button" 
                        @click="open = false"
                        class="p-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-900 text-slate-400 hover:text-slate-650 dark:text-slate-550 dark:hover:text-slate-350 transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Formulario -->
            <form wire:submit.prevent="save" class="p-6 space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- Nombre -->
                    <div class="space-y-1.5 md:col-span-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Nombre del Producto *</label>
                        <input type="text" 
                               wire:model="nombre" 
                               placeholder="Ej: Coca-Cola Sin Azúcar, Leche de Soya..."
                               class="w-full rounded-xl border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-800 text-slate-800 dark:text-white px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition">
                        @error('nombre') <span class="text-xs text-rose-500 font-medium mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Categoría -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Categoría *</label>
                        <select wire:model="categoriaId" 
                                class="w-full rounded-xl border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-800 text-slate-800 dark:text-white px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition">
                            <option value="">Seleccione Categoría...</option>
                            @foreach ($this->categorias as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                            @endforeach
                        </select>
                        @error('categoriaId') <span class="text-xs text-rose-500 font-medium mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Marca -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Marca *</label>
                        <select wire:model="marcaId" 
                                class="w-full rounded-xl border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-800 text-slate-800 dark:text-white px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition">
                            <option value="">Seleccione Marca...</option>
                            @foreach ($this->marcas as $mrc)
                                <option value="{{ $mrc->id }}">{{ $mrc->nombre }}</option>
                            @endforeach
                        </select>
                        @error('marcaId') <span class="text-xs text-rose-500 font-medium mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Cód. Interno -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Código Interno</label>
                        <input type="text" 
                               wire:model="codigo_interno" 
                               placeholder="Opcional: Dejar vacío para autogenerar"
                               class="w-full rounded-xl border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-800 text-slate-800 dark:text-white px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition">
                        @error('codigo_interno') <span class="text-xs text-rose-500 font-medium mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Afecto IGV y Estado Activo -->
                    <div class="grid grid-cols-2 gap-4">
                        <!-- Afecto IGV -->
                        <div class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-900/40 border border-slate-200/50 dark:border-slate-800/60 rounded-2xl">
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-350">Afecto IGV</span>
                            <button type="button" 
                                    wire:click="$toggle('afecto_igv')"
                                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $afecto_igv ? 'bg-indigo-600' : 'bg-slate-200 dark:bg-slate-700' }}">
                                <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $afecto_igv ? 'translate-x-5' : 'translate-x-0' }}"></span>
                            </button>
                        </div>
                        
                        <!-- Activo -->
                        <div class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-900/40 border border-slate-200/50 dark:border-slate-800/60 rounded-2xl">
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-355">Activo</span>
                            <button type="button" 
                                    wire:click="$toggle('activo')"
                                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $activo ? 'bg-emerald-655 bg-emerald-600' : 'bg-slate-200 dark:bg-slate-700' }}">
                                <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $activo ? 'translate-x-5' : 'translate-x-0' }}"></span>
                            </button>
                        </div>
                    </div>

                    <!-- Descripción -->
                    <div class="space-y-1.5 md:col-span-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Descripción</label>
                        <textarea wire:model="descripcion" 
                                  rows="3" 
                                  placeholder="Opcional: Detalles sobre el producto..."
                                  class="w-full rounded-xl border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-800 text-slate-800 dark:text-white px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition"></textarea>
                        @error('descripcion') <span class="text-xs text-rose-500 font-medium mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Footer Acciones -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-[#1d2745]/30">
                    <button type="button" 
                            @click="open = false"
                            class="px-5 py-2.5 text-sm font-bold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl transition">
                        Cancelar
                    </button>
                    <button type="submit" 
                            class="px-5 py-2.5 text-sm font-bold text-white bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 active:scale-95 transition-all shadow-md shadow-indigo-500/20 rounded-xl">
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL CONFIRMAR ELIMINACIÓN -->
    <div x-data="{ openDelete: @entangle('showDeleteConfirmModal') }" 
         x-show="openDelete" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto"
         style="display: none;">
        
        <!-- Backdrop/Overlay -->
        <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity"
             @click="openDelete = false">
        </div>

        <!-- Ventana del Modal -->
        <div class="relative bg-white dark:bg-[#0c101d] border border-slate-200 dark:border-[#1c243a] rounded-3xl w-full max-w-md overflow-hidden shadow-2xl transition-all animate-fade-in my-8 z-10">
            <!-- Icono y Pregunta -->
            <div class="p-6 text-center space-y-4">
                <div class="inline-flex p-3 bg-rose-500/10 text-rose-500 rounded-full border border-rose-500/20">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="space-y-1.5">
                    <h3 class="text-base font-extrabold text-slate-900 dark:text-white">
                        {{ $estado === 'trashed' ? '¿Eliminar permanentemente?' : '¿Enviar a la papelera?' }}
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                        @if ($estado === 'trashed')
                            Esta acción es irreversible. Se eliminará el producto de forma permanente y ya no se podrá recuperar.
                        @else
                            El producto será desactivado y enviado a la papelera. Podrás restaurarlo en cualquier momento si lo necesitas nuevamente.
                        @endif
                    </p>
                </div>
            </div>

            <!-- Footer Acciones -->
            <div class="flex items-center justify-end gap-3 p-4 bg-slate-50 dark:bg-slate-950/20 border-t border-slate-100 dark:border-[#1d2745]/30">
                <button type="button" 
                        @click="openDelete = false"
                        class="px-4.5 py-2 text-xs font-bold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-700 transition">
                    Cancelar
                </button>
                <button type="button" 
                        wire:click="{{ $estado === 'trashed' ? 'forceDelete(' . $productoToDeleteId . ')' : 'delete' }}"
                        class="px-4.5 py-2 text-xs font-bold text-white bg-rose-600 hover:bg-rose-500 active:scale-95 transition-all shadow-sm shadow-rose-500/20 rounded-lg">
                    Confirmar
                </button>
            </div>
        </div>
    </div>
</x-filament-panels::page>
