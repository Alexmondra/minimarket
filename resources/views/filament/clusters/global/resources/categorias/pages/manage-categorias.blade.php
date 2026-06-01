<x-filament-panels::page>
    <div class="categorias-root space-y-6 animate-fade-in">
        <!-- Contenido Principal -->
        <div class="space-y-6">
            <!-- Tarjetas de Estadísticas (KPIs) -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Total Categorías -->
                <div class="kpi-card kpi-indigo">
                    <div class="flex justify-between items-start">
                        <div class="space-y-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Total Categorías</span>
                            <div class="text-3xl font-black text-slate-950 dark:text-white">
                                {{ $this->stats['total'] }}
                            </div>
                        </div>
                        <div class="p-2 bg-indigo-500/10 text-indigo-500 rounded-xl">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Categorías Activas -->
                <div class="kpi-card kpi-emerald">
                    <div class="flex justify-between items-start">
                        <div class="space-y-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Activas</span>
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

                <!-- En Papelera -->
                <div class="kpi-card kpi-rose">
                    <div class="flex justify-between items-start">
                        <div class="space-y-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Papelera</span>
                            <div class="text-3xl font-black text-slate-950 dark:text-white">
                                {{ $this->stats['trashed'] }}
                            </div>
                        </div>
                        <div class="p-2 bg-rose-500/10 text-rose-500 rounded-xl">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.9 12.1A2 2 0 0116.1 21H7.9a2 2 0 01-2-1.9L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botón Nueva Categoría (Verde Premium) -->
            <div class="flex justify-end pt-2">
                <button type="button" 
                        wire:click="openCreateModal"
                        class="inline-flex items-center gap-2 px-6 py-3.5 text-sm font-extrabold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 active:scale-95 transition-all shadow-lg shadow-emerald-500/20 rounded-2xl">
                    <span class="text-base">➕</span>
                    <span>Nueva Categoría</span>
                </button>
            </div>

            <!-- Panel de Control Principal -->
            <div class="glass-card overflow-hidden">
                
                <!-- Barra de Control -->
                <div class="p-5 border-b border-slate-200/50 dark:border-slate-800/40 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <!-- Buscador -->
                    <div class="relative flex-1 max-w-md">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.602 10.602z" />
                            </svg>
                        </div>
                        <input type="text" 
                               wire:model.live="search"
                               placeholder="Buscar categorías por nombre o descripción..."
                               class="w-full pl-10 pr-4 py-2.5 text-sm rounded-xl border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition">
                    </div>

                    <!-- Filtros Visuales (Pills) y Switch de Vista (Grid/Table) -->
                    <div class="flex flex-wrap items-center gap-3">
                        <!-- Filtros Pills -->
                        <div class="flex items-center gap-1.5 p-1 bg-slate-100 dark:bg-slate-900/80 rounded-xl border dark:border-slate-800/60">
                            <button type="button" 
                                    wire:click="$set('estado', 'all')"
                                    class="px-4 py-1.5 rounded-lg text-xs font-bold transition-all {{ $estado === 'all' ? 'bg-white dark:bg-slate-800 text-slate-950 dark:text-white shadow-sm' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-300' }}">
                                Todos
                            </button>
                            <button type="button" 
                                    wire:click="$set('estado', 'active')"
                                    class="px-4 py-1.5 rounded-lg text-xs font-bold transition-all {{ $estado === 'active' ? 'bg-white dark:bg-slate-800 text-slate-950 dark:text-white shadow-sm' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-300' }}">
                                Activos
                            </button>
                            <button type="button" 
                                    wire:click="$set('estado', 'trashed')"
                                    class="px-4 py-1.5 rounded-lg text-xs font-bold transition-all {{ $estado === 'trashed' ? 'bg-white dark:bg-slate-800 text-slate-950 dark:text-white shadow-sm' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-300' }}">
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

                <!-- CONTENIDO FILTRADO -->
                <div class="p-6">
                    @if ($viewMode === 'grid')
                        <!-- ================= VISTA DE TARJETAS (GRID) ================= -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                            @forelse ($this->categorias as $categoria)
                                @php
                                    $visuals = $this->getCategoryVisuals($categoria->nombre);
                                    $isTrashed = $categoria->trashed();
                                @endphp
                                <div class="relative overflow-hidden rounded-2xl border transition-all duration-300 group hover:shadow-lg hover:-translate-y-1 {{ $isTrashed ? 'bg-rose-50/10 dark:bg-rose-950/5 border-rose-300/40 dark:border-rose-900/30 opacity-90 border-l-4 border-l-rose-500' : 'bg-white dark:bg-slate-900 border-slate-200/80 dark:border-slate-800' }}">
                                    
                                    <!-- Card Content -->
                                    <div class="p-5 space-y-4">
                                        <!-- Header del Card -->
                                        <div class="flex justify-between items-start">
                                            <!-- Emoji e Icono Base -->
                                            <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-2xl border shadow-sm {{ $visuals['icon_bg'] }} {{ $visuals['border'] }}">
                                                {{ $visuals['emoji'] }}
                                            </div>
                                            <!-- Stats Badge -->
                                            <div class="flex items-center gap-1.5">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20" title="Productos asociados">
                                                    📦 {{ $categoria->productos_count }} prod.
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Detalles de la Categoría -->
                                        <div class="space-y-1">
                                            <h3 class="text-base font-extrabold text-slate-900 dark:text-white leading-snug">
                                                {{ $categoria->nombre }}
                                            </h3>
                                            <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-3 min-h-[3rem] leading-relaxed">
                                                {{ $categoria->descripcion ?: 'Sin descripción detallada registrada.' }}
                                            </p>
                                        </div>

                                        <!-- Meta de Papelera y Estado -->
                                        <div class="flex items-center justify-between pt-2">
                                            <div>
                                                @if ($isTrashed)
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                        Eliminado
                                                    </span>
                                                @else
                                                    @if ($categoria->estado)
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                            Activo
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-500/10 text-slate-600 dark:text-slate-400 border border-slate-500/20">
                                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                                            Inactivo
                                                        </span>
                                                    @endif
                                                @endif
                                            </div>
                                            @if ($isTrashed && $categoria->deleted_at)
                                                <span class="text-[10px] text-rose-600 dark:text-rose-400 font-bold bg-rose-500/5 px-2 py-0.5 rounded border border-rose-500/10">
                                                    🗑️ {{ $categoria->deleted_at->format('d/m/Y H:i') }}
                                                </span>
                                            @endif
                                        </div>

                                        <!-- Acciones Rápidas del Card -->
                                        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800/40">
                                            @if ($isTrashed)
                                                <!-- Restaurar -->
                                                <button type="button" 
                                                        wire:click="restore({{ $categoria->id }})"
                                                        class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 rounded-xl shadow-md shadow-emerald-500/10 active:scale-95 transition-all">
                                                    <span class="text-sm">♻️</span>
                                                    <span>Restaurar</span>
                                                </button>
                                                
                                                <!-- Eliminar Definitivo -->
                                                <button type="button" 
                                                        wire:click="confirmDelete({{ $categoria->id }})"
                                                        class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold text-white bg-red-700 hover:bg-red-600 rounded-xl shadow-md shadow-red-700/10 active:scale-95 transition-all">
                                                    <span class="text-sm">🗑️</span>
                                                    <span>Eliminar Definitivo</span>
                                                </button>
                                            @else
                                                <!-- Editar -->
                                                <button type="button" 
                                                        wire:click="openEditModal({{ $categoria->id }})"
                                                        class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-500 rounded-xl shadow-md shadow-blue-500/10 active:scale-95 transition-all">
                                                    <span class="text-sm">✏️</span>
                                                    <span>Editar</span>
                                                </button>

                                                <!-- Eliminar -->
                                                <button type="button" 
                                                        wire:click="confirmDelete({{ $categoria->id }})"
                                                        class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold text-white bg-rose-600 hover:bg-rose-500 rounded-xl shadow-md shadow-rose-500/10 active:scale-95 transition-all">
                                                    <span class="text-sm">🗑️</span>
                                                    <span>Eliminar</span>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <!-- Estado Vacío en Grid -->
                                <div class="col-span-full py-12 text-center">
                                    <div class="max-w-md mx-auto space-y-4">
                                        <div class="inline-flex p-4 bg-slate-50 dark:bg-slate-900/60 rounded-2xl border border-slate-200/50 dark:border-slate-800/40 text-slate-400 shadow-sm">
                                            <span class="text-4xl">🔍</span>
                                        </div>
                                        <div class="space-y-1">
                                            <h3 class="text-sm font-bold text-slate-800 dark:text-white">No se encontraron categorías</h3>
                                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                                No hay categorías que coincidan con la búsqueda o el filtro actual.
                                            </p>
                                        </div>
                                        @if (!empty($search) || $estado !== 'all')
                                            <button type="button" 
                                                    wire:click="$set('search', ''); $set('estado', 'all');"
                                                    class="inline-flex items-center gap-1.5 px-4.5 py-2 text-xs font-bold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 rounded-xl shadow-sm transition dark:bg-slate-900/40 dark:border-slate-800 dark:text-slate-300">
                                                <span>🔄</span>
                                                <span>Limpiar filtros</span>
                                            </button>
                                        @else
                                            <button type="button" 
                                                    wire:click="openCreateModal"
                                                    class="inline-flex items-center gap-1.5 px-5 py-2.5 text-xs font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 rounded-xl shadow-md transition">
                                                <span class="text-sm">➕</span>
                                                <span>Nueva Categoría</span>
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
                                        <th class="py-4 px-6 font-semibold uppercase tracking-wider text-[11px] text-slate-400 dark:text-slate-500 border-b border-slate-100 dark:border-slate-800/60 bg-slate-50/40 dark:bg-slate-950/20">
                                            <button type="button" wire:click="sortBy('nombre')" class="inline-flex items-center gap-1 hover:text-slate-600 dark:hover:text-slate-350">
                                                <span>Nombre</span>
                                                @if ($sortField === 'nombre')
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $sortDirection === 'asc' ? 'M4.5 15.75l7.5-7.5 7.5 7.5' : 'M19.5 8.25l-7.5 7.5-7.5-7.5' }}" />
                                                    </svg>
                                                @endif
                                            </button>
                                        </th>
                                        <th class="py-4 px-6 font-semibold uppercase tracking-wider text-[11px] text-slate-400 dark:text-slate-500 border-b border-slate-100 dark:border-slate-800/60 bg-slate-50/40 dark:bg-slate-950/20">Descripción</th>
                                        <th class="py-4 px-6 font-semibold uppercase tracking-wider text-[11px] text-slate-400 dark:text-slate-500 border-b border-slate-100 dark:border-slate-800/60 bg-slate-50/40 dark:bg-slate-950/20 text-center">Productos</th>
                                        @if ($estado === 'trashed')
                                            <th class="py-4 px-6 font-semibold uppercase tracking-wider text-[11px] text-slate-400 dark:text-slate-500 border-b border-slate-100 dark:border-slate-800/60 bg-slate-50/40 dark:bg-slate-950/20">Fecha Eliminación</th>
                                        @endif
                                        <th class="py-4 px-6 font-semibold uppercase tracking-wider text-[11px] text-slate-400 dark:text-slate-500 border-b border-slate-100 dark:border-slate-800/60 bg-slate-50/40 dark:bg-slate-950/20">Estado</th>
                                        <th class="py-4 px-6 font-semibold uppercase tracking-wider text-[11px] text-slate-400 dark:text-slate-500 border-b border-slate-100 dark:border-slate-800/60 bg-slate-50/40 dark:bg-slate-950/20 text-right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/40">
                                    @forelse ($this->categorias as $categoria)
                                        @php
                                            $visuals = $this->getCategoryVisuals($categoria->nombre);
                                            $isTrashed = $categoria->trashed();
                                        @endphp
                                        <tr class="border-l-4 {{ $isTrashed ? 'bg-rose-50/10 dark:bg-rose-950/5 border-l-rose-500 opacity-85' : 'border-l-transparent hover:bg-slate-50/40 dark:hover:bg-slate-900/30' }} transition duration-150">
                                            <!-- Nombre con Avatar/Emoji -->
                                            <td class="py-4 px-6">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center text-lg shrink-0 border {{ $visuals['icon_bg'] }} {{ $visuals['border'] }}">
                                                        {{ $visuals['emoji'] }}
                                                    </div>
                                                    <div class="font-bold text-slate-900 dark:text-white">
                                                        {{ $categoria->nombre }}
                                                    </div>
                                                </div>
                                            </td>
                                            
                                            <!-- Descripción -->
                                            <td class="py-4 px-6 max-w-xs truncate">
                                                <span class="text-xs text-slate-500 dark:text-slate-400">
                                                    {{ $categoria->descripcion ?: '—' }}
                                                </span>
                                            </td>

                                            <!-- Cantidad Productos -->
                                            <td class="py-4 px-6 text-center">
                                                <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-500/10 text-indigo-650 dark:text-indigo-400 border border-indigo-500/20">
                                                    {{ $categoria->productos_count }}
                                                </span>
                                            </td>

                                            <!-- Fecha Eliminación (Condicional) -->
                                            @if ($estado === 'trashed')
                                                <td class="py-4 px-6 text-xs text-rose-600 dark:text-rose-400 font-bold">
                                                    {{ $categoria->deleted_at ? $categoria->deleted_at->format('d/m/Y H:i') : '—' }}
                                                </td>
                                            @endif

                                            <!-- Estado -->
                                            <td class="py-4 px-6">
                                                @if ($isTrashed)
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                        Eliminado
                                                    </span>
                                                @else
                                                    @if ($categoria->estado)
                                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                            Activo
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-500/10 text-slate-650 dark:text-slate-400 border border-slate-500/20">
                                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-450"></span>
                                                            Inactivo
                                                        </span>
                                                    @endif
                                                @endif
                                            </td>

                                            <!-- Acciones de Fila -->
                                            <td class="py-4 px-6 text-right">
                                                <div class="flex items-center justify-end gap-3 flex-wrap">
                                                    @if ($isTrashed)
                                                        <!-- Restaurar -->
                                                        <button type="button" 
                                                                wire:click="restore({{ $categoria->id }})"
                                                                class="inline-flex items-center gap-1.5 px-4.5 py-2.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 rounded-xl shadow-md shadow-emerald-500/10 active:scale-95 transition-all">
                                                            <span class="text-sm">♻️</span>
                                                            <span>Restaurar</span>
                                                        </button>
                                                        
                                                        <!-- Eliminar Definitivo -->
                                                        <button type="button" 
                                                                wire:click="confirmDelete({{ $categoria->id }})"
                                                                class="inline-flex items-center gap-1.5 px-4.5 py-2.5 text-xs font-bold text-white bg-red-700 hover:bg-red-600 rounded-xl shadow-md shadow-red-700/10 active:scale-95 transition-all">
                                                            <span class="text-sm">🗑️</span>
                                                            <span>Eliminar Definitivo</span>
                                                        </button>
                                                    @else
                                                        <!-- Editar -->
                                                        <button type="button" 
                                                                wire:click="openEditModal({{ $categoria->id }})"
                                                                class="inline-flex items-center gap-1.5 px-4.5 py-2.5 text-xs font-bold text-white bg-blue-600 hover:bg-blue-500 rounded-xl shadow-md shadow-blue-500/10 active:scale-95 transition-all">
                                                            <span class="text-sm">✏️</span>
                                                            <span>Editar</span>
                                                        </button>

                                                        <!-- Eliminar -->
                                                        <button type="button" 
                                                                wire:click="confirmDelete({{ $categoria->id }})"
                                                                class="inline-flex items-center gap-1.5 px-4.5 py-2.5 text-xs font-bold text-white bg-rose-600 hover:bg-rose-500 rounded-xl shadow-md shadow-rose-500/10 active:scale-95 transition-all">
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
                                            <td colspan="{{ $estado === 'trashed' ? '6' : '5' }}" class="py-16 px-6 text-center">
                                                <div class="max-w-md mx-auto space-y-4">
                                                    <div class="inline-flex p-4 bg-slate-50 dark:bg-slate-900/60 rounded-2xl border border-slate-200/50 dark:border-slate-800/40 text-slate-400 shadow-sm">
                                                        <span class="text-4xl">🔍</span>
                                                    </div>
                                                    <div class="space-y-1">
                                                        <h3 class="text-sm font-bold text-slate-800 dark:text-white">No se encontraron categorías</h3>
                                                        <p class="text-xs text-slate-500 dark:text-slate-400">
                                                            No hay categorías registradas que coincidan con la búsqueda o el filtro actual.
                                                        </p>
                                                    </div>
                                                    @if (!empty($search) || $estado !== 'all')
                                                        <button type="button" 
                                                                wire:click="$set('search', ''); $set('estado', 'all');"
                                                                class="inline-flex items-center gap-1.5 px-4.5 py-2 text-xs font-bold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 rounded-xl shadow-sm transition dark:bg-slate-900/40 dark:border-slate-800 dark:text-slate-300">
                                                            <span>🔄</span>
                                                            <span>Limpiar filtros</span>
                                                        </button>
                                                    @else
                                                        <button type="button" 
                                                                wire:click="openCreateModal"
                                                                class="inline-flex items-center gap-1.5 px-5 py-2.5 text-xs font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 rounded-xl shadow-md transition">
                                                            <span class="text-sm">➕</span>
                                                            <span>Nueva Categoría</span>
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
                @if ($this->categorias->hasPages())
                    <div class="px-6 py-4 border-t border-slate-200/50 dark:border-slate-800/40 bg-slate-50/20 dark:bg-slate-950/5">
                        {{ $this->categorias->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- MODAL REGISTRAR / EDITAR CATEGORÍA -->
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
        <div class="relative bg-white dark:bg-[#0c101d] border border-slate-200 dark:border-[#1c243a] rounded-3xl w-full max-w-lg overflow-hidden shadow-2xl transition-all animate-fade-in my-8 z-10">
            <!-- Cabecera -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-[#1d2745]/30">
                <h2 class="text-base font-extrabold text-slate-800 dark:text-white flex items-center gap-2">
                    <span class="p-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-500/10">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0z" />
                        </svg>
                    </span>
                    <span>{{ $categoriaId ? 'Editar Categoría' : 'Nueva Categoría' }}</span>
                </h2>
                <button type="button" 
                        @click="open = false"
                        class="p-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-900 text-slate-400 hover:text-slate-600 dark:text-slate-500 dark:hover:text-slate-300 transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Formulario -->
            <form wire:submit.prevent="save" class="p-6 space-y-5">
                <!-- Nombre -->
                <div class="space-y-1.5">
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Nombre de la Categoría *</label>
                    <input type="text" 
                           wire:model="nombre" 
                           placeholder="Ej: Bebidas, Lácteos, Snacks, Limpieza..."
                           class="w-full rounded-xl border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-800 text-slate-800 dark:text-white px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition">
                    @error('nombre') <span class="text-xs text-rose-500 font-medium mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Descripción -->
                <div class="space-y-1.5">
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Descripción</label>
                    <textarea wire:model="descripcion" 
                              rows="3" 
                              placeholder="Opcional: Detalles sobre los productos agrupados en esta categoría..."
                              class="w-full rounded-xl border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-800 text-slate-800 dark:text-white px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition"></textarea>
                    @error('descripcion') <span class="text-xs text-rose-500 font-medium mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Toggle Estado (solo al editar o si se desea visibilizar) -->
                <div class="flex items-center justify-between p-3.5 bg-slate-50 dark:bg-slate-900/40 border border-slate-200/50 dark:border-slate-800/65 rounded-2xl">
                    <div class="space-y-0.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-350">Categoría Activa</label>
                        <p class="text-[10px] text-slate-400 dark:text-slate-500">Determina si la categoría está disponible para clasificar productos.</p>
                    </div>
                    <button type="button" 
                            wire:click="$toggle('estado_campo')"
                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $estado_campo ? 'bg-emerald-600' : 'bg-slate-200 dark:bg-slate-700' }}">
                        <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $estado_campo ? 'translate-x-5' : 'translate-x-0' }}"></span>
                    </button>
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
                            Esta acción es irreversible. Se eliminará la categoría de forma permanente y ya no se podrá recuperar.
                        @else
                            La categoría será desactivada y enviada a la papelera. Podrás restaurarla en cualquier momento si la necesitas nuevamente.
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
                        wire:click="{{ $estado === 'trashed' ? 'forceDelete(' . $categoriaToDeleteId . ')' : 'delete' }}"
                        class="px-4.5 py-2 text-xs font-bold text-white bg-rose-600 hover:bg-rose-500 active:scale-95 transition-all shadow-sm shadow-rose-500/20 rounded-lg">
                    Confirmar
                </button>
            </div>
        </div>
    </div>
</x-filament-panels::page>
