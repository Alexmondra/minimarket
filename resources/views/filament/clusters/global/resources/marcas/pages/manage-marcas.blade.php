<x-filament-panels::page>
    <div class="marcas-root space-y-6 animate-fade-in">
        <!-- Contenido Principal -->
        <div class="space-y-6">
                <!-- Tarjetas de Estadísticas (KPIs) -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <!-- Total Marcas -->
                    <div class="kpi-card kpi-indigo">
                        <div class="flex justify-between items-start">
                            <div class="space-y-2">
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Total Marcas</span>
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

                    <!-- Marcas Activas -->
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

                <!-- Botón Registrar Marca (Verde Premium) -->
                <div class="flex justify-end pt-2">
                    <button type="button" 
                            wire:click="openCreateModal"
                            class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 active:scale-95 transition-all shadow-md shadow-emerald-500/20 rounded-xl">
                        <svg class="w-4 h-4 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        <span>Registrar Marca</span>
                    </button>
                </div>

                <!-- Tabla, Búsqueda y Filtros -->
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
                                   placeholder="Buscar marcas por nombre o descripción..."
                                   class="w-full pl-10 pr-4 py-2.5 text-sm rounded-xl border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition">
                        </div>

                        <!-- Filtros Visuales (Pills) -->
                        <div class="flex items-center gap-1.5 p-1 bg-slate-100 dark:bg-slate-900/80 rounded-xl self-start md:self-auto border dark:border-slate-800/60">
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
                    </div>

                    <!-- Listado de la Tabla -->
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse text-left text-sm">
                            <thead>
                                <tr class="bg-slate-50/40 dark:bg-slate-950/20">
                                    <th class="py-3.5 px-5 font-bold uppercase tracking-wider text-[10px] text-slate-400 dark:text-slate-500">
                                        <button type="button" wire:click="sortBy('nombre')" class="inline-flex items-center gap-1 hover:text-slate-600 dark:hover:text-slate-300">
                                            <span>Nombre</span>
                                            @if ($sortField === 'nombre')
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $sortDirection === 'asc' ? 'M4.5 15.75l7.5-7.5 7.5 7.5' : 'M19.5 8.25l-7.5 7.5-7.5-7.5' }}" />
                                                </svg>
                                            @endif
                                        </button>
                                    </th>
                                    <th class="py-3.5 px-5 font-bold uppercase tracking-wider text-[10px] text-slate-400 dark:text-slate-500">Descripción</th>
                                    <th class="py-3.5 px-5 font-bold uppercase tracking-wider text-[10px] text-slate-400 dark:text-slate-500 text-center">Productos</th>
                                    @if ($estado === 'trashed')
                                        <th class="py-3.5 px-5 font-bold uppercase tracking-wider text-[10px] text-slate-400 dark:text-slate-500">Fecha Eliminación</th>
                                    @endif
                                    <th class="py-3.5 px-5 font-bold uppercase tracking-wider text-[10px] text-slate-400 dark:text-slate-500">Estado</th>
                                    <th class="py-3.5 px-5 font-bold uppercase tracking-wider text-[10px] text-slate-400 dark:text-slate-500 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/40">
                                @forelse ($this->marcas as $marca)
                                    <tr class="hover:bg-slate-50/40 dark:hover:bg-slate-900/30 transition duration-150">
                                        <!-- Nombre -->
                                        <td class="py-4 px-5">
                                            <div class="font-bold text-slate-900 dark:text-white">
                                                {{ $marca->nombre }}
                                            </div>
                                        </td>
                                        
                                        <!-- Descripción -->
                                        <td class="py-4 px-5 max-w-xs truncate">
                                            <span class="text-xs text-slate-500 dark:text-slate-400">
                                                {{ $marca->descripcion ?: '—' }}
                                            </span>
                                        </td>

                                        <!-- Cantidad Productos -->
                                        <td class="py-4 px-5 text-center">
                                            <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20">
                                                {{ $marca->productos_count }}
                                            </span>
                                        </td>

                                        <!-- Fecha Eliminación (Condicional) -->
                                        @if ($estado === 'trashed')
                                            <td class="py-4 px-5 text-xs text-rose-600 dark:text-rose-400 font-semibold">
                                                {{ $marca->deleted_at ? $marca->deleted_at->format('d/m/Y H:i') : '—' }}
                                            </td>
                                        @endif

                                        <!-- Estado -->
                                        <td class="py-4 px-5">
                                            @if ($marca->trashed())
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                    Eliminado
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                    Activo
                                                </span>
                                            @endif
                                        </td>

                                        <!-- Acciones -->
                                        <td class="py-4 px-5 text-right">
                                            <div class="flex items-center justify-end gap-3 flex-wrap">
                                                @if ($marca->trashed())
                                                    <!-- Restaurar -->
                                                    <button type="button" 
                                                            wire:click="restore({{ $marca->id }})"
                                                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 rounded-xl shadow-sm shadow-emerald-500/10 hover:shadow-md active:scale-95 transition-all duration-200">
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7.5L12 3 4 7.5M20 7.5v9L12 21M20 7.5l-8 4.5M4 7.5v9L12 21M4 7.5l8 4.5" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8a3.5 3.5 0 103 2M15 8h-3v3" />
                                                        </svg>
                                                        <span>♻️ Restaurar</span>
                                                    </button>
                                                    
                                                    <!-- Forzar Borrado -->
                                                    <button type="button" 
                                                            wire:click="confirmDelete({{ $marca->id }})"
                                                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-bold text-white bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-500 hover:to-red-500 rounded-xl shadow-sm shadow-rose-500/10 hover:shadow-md active:scale-95 transition-all duration-200">
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7.5L12 3 4 7.5M20 7.5v9L12 21M20 7.5l-8 4.5M4 7.5v9L12 21M4 7.5l8 4.5" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.5 9.5l5 5m0-5l-5 5" />
                                                        </svg>
                                                        <span>🗑️ Eliminar Definitivo</span>
                                                    </button>
                                                @else
                                                    <!-- Editar -->
                                                    <button type="button" 
                                                            wire:click="openEditModal({{ $marca->id }})"
                                                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-bold text-white bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-500 hover:to-blue-500 rounded-xl shadow-sm shadow-indigo-500/10 hover:shadow-md active:scale-95 transition-all duration-200">
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12.5 12.5l3-3" />
                                                        </svg>
                                                        <span>✏️ Editar</span>
                                                    </button>

                                                    <!-- Borrar (Soft Delete) -->
                                                    <button type="button" 
                                                            wire:click="confirmDelete({{ $marca->id }})"
                                                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-bold text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl border border-slate-200 dark:border-slate-700/60 shadow-sm active:scale-95 transition-all duration-200">
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7.5L12 3 4 7.5M20 7.5v9L12 21M20 7.5l-8 4.5M4 7.5v9L12 21M4 7.5l8 4.5" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.5 12h5" />
                                                        </svg>
                                                        <span>🗑️ Eliminar</span>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <!-- Estado Vacío -->
                                    <tr>
                                        <td colspan="6" class="py-12 px-5 text-center">
                                            <div class="max-w-md mx-auto space-y-4">
                                                <div class="inline-flex p-4 bg-slate-50 dark:bg-slate-900/60 rounded-full border border-slate-200/50 dark:border-slate-800/40 text-slate-400">
                                                    <svg class="w-12 h-12 stroke-[1.25]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.602 10.602z" />
                                                    </svg>
                                                </div>
                                                <div class="space-y-1">
                                                    <h3 class="text-sm font-bold text-slate-800 dark:text-white">No se encontraron marcas</h3>
                                                    <p class="text-xs text-slate-500 dark:text-slate-400">
                                                        No hay marcas registradas que coincidan con la búsqueda o el filtro actual.
                                                    </p>
                                                </div>
                                                @if (!empty($search) || $estado !== 'all')
                                                    <button type="button" 
                                                            wire:click="$set('search', ''); $set('estado', 'all');"
                                                            class="inline-flex items-center gap-1 px-4 py-2 text-xs font-bold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 rounded-lg shadow-sm transition dark:bg-slate-900/40 dark:border-slate-800 dark:text-slate-300">
                                                        Limpiar filtros
                                                    </button>
                                                @else
                                                    <button type="button" 
                                                            wire:click="openCreateModal"
                                                            class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 rounded-lg shadow-sm transition">
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                                        </svg>
                                                        <span>Registrar Marca</span>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    @if ($this->marcas->hasPages())
                        <div class="px-5 py-4 border-t border-slate-200/50 dark:border-slate-800/40 bg-slate-50/20 dark:bg-slate-950/5">
                            {{ $this->marcas->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

    <!-- MODAL REGISTRAR / EDITAR MARCA -->
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
                    <span>{{ $marcaId ? 'Editar Marca' : 'Nueva Marca' }}</span>
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
            <form wire:submit.prevent="save" class="p-6 space-y-4">
                <!-- Nombre -->
                <div class="space-y-1.5">
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Nombre de la Marca *</label>
                    <input type="text" 
                           wire:model="nombre" 
                           placeholder="Ej: Coca-Cola, Gloria, Nestle..."
                           class="w-full rounded-xl border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-800 text-slate-800 dark:text-white px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition">
                    @error('nombre') <span class="text-xs text-rose-500 font-medium mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Descripción -->
                <div class="space-y-1.5">
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Descripción</label>
                    <textarea wire:model="descripcion" 
                              rows="3" 
                              placeholder="Opcional: Detalles breves sobre la procedencia o productos de la marca..."
                              class="w-full rounded-xl border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-800 text-slate-800 dark:text-white px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition"></textarea>
                    @error('descripcion') <span class="text-xs text-rose-500 font-medium mt-1 block">{{ $message }}</span> @enderror
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
                            Esta acción es irreversible. Se eliminará la marca de forma permanente y ya no se podrá recuperar.
                        @else
                            La marca será desactivada y enviada a la papelera. Podrás restaurarla en cualquier momento si la necesitas nuevamente.
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
                        wire:click="{{ $estado === 'trashed' ? 'forceDelete(' . $marcaToDeleteId . ')' : 'delete' }}"
                        class="px-4.5 py-2 text-xs font-bold text-white bg-rose-600 hover:bg-rose-500 active:scale-95 transition-all shadow-sm shadow-rose-500/20 rounded-lg">
                    Confirmar
                </button>
            </div>
        </div>
    </div>

    <!-- Estilos Locales de Alta Fidelidad -->
    <style>
        .marcas-root {
            --m-border: rgba(148, 163, 184, 0.16);
            --m-text: #e2e8f0;
            --m-muted: #94a3b8;
        }

        /* Botón de navegación lateral */
        .nav-btn {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.65rem 0.85rem;
            border-radius: 14px;
            background: transparent;
            color: #64748b;
            border: 1px solid transparent;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .dark .nav-btn {
            color: #94a3b8;
        }

        .nav-btn:hover {
            background: rgba(255, 255, 255, 0.8);
            border-color: rgba(226, 232, 240, 0.8);
            color: #1e293b;
            box-shadow: 0 4px 12px -2px rgba(0, 0, 0, 0.03);
        }

        .dark .nav-btn:hover {
            background: rgba(255, 255, 255, 0.04);
            border-color: rgba(255, 255, 255, 0.05);
            color: #f8fafc;
            box-shadow: none;
        }

        /* Botón de navegación Activo */
        .nav-btn-active {
            background: linear-gradient(90deg, rgba(99, 102, 241, 0.08), rgba(99, 102, 241, 0.02)) !important;
            border-color: rgba(99, 102, 241, 0.15) !important;
            border-left: 4px solid #6366f1 !important;
            color: #4f46e5 !important;
            border-top-left-radius: 0px !important;
            border-bottom-left-radius: 0px !important;
            box-shadow: 0 4px 12px -2px rgba(99, 102, 241, 0.04) !important;
        }

        .dark .nav-btn-active {
            background: linear-gradient(90deg, rgba(129, 140, 248, 0.1), rgba(129, 140, 248, 0.02)) !important;
            border-color: rgba(129, 140, 248, 0.15) !important;
            border-left: 4px solid #818cf8 !important;
            color: #c7d2fe !important;
            box-shadow: none !important;
        }

        /* Envoltorio del icono de navegación */
        .nav-icon-wrapper {
            width: 32px;
            height: 32px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: transform 0.2s ease;
        }

        .nav-label {
            font-weight: 700;
        }
    </style>
</x-filament-panels::page>
