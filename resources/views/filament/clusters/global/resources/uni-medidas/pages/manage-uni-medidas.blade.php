<x-filament-panels::page>
    <div class="unimedidas-root space-y-6 animate-fade-in">
        <!-- Barra de Búsqueda, Acciones y Filtros (Unified SaaS Toolbar) -->
        <div class="glass-card p-4 flex flex-col xl:flex-row xl:items-center justify-between gap-4">
            <!-- Izquierda: Buscador -->
            <div class="relative flex-1 max-w-md">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.602 10.602z" />
                    </svg>
                </div>
                <input type="text" 
                       wire:model.live="search"
                       placeholder="Buscar unidades por nombre o abreviatura..."
                       class="w-full pl-10 pr-4 py-2.5 text-sm rounded-xl border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition">
            </div>

            <!-- Derecha: Filtros, Botón Crear y Selector de Vista -->
            <div class="flex flex-wrap items-center gap-3">
                <!-- Filtros de Estado -->
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
                            wire:click="$set('estado', 'inactive')"
                            class="px-4 py-1.5 rounded-lg text-xs font-bold transition-all {{ $estado === 'inactive' ? 'bg-white dark:bg-slate-800 text-slate-950 dark:text-white shadow-sm' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-300' }}">
                        Inactivos
                    </button>
                    <button type="button" 
                            wire:click="$set('estado', 'trashed')"
                            class="px-4 py-1.5 rounded-lg text-xs font-bold transition-all {{ $estado === 'trashed' ? 'bg-white dark:bg-slate-800 text-slate-950 dark:text-white shadow-sm' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-300' }}">
                        Papelera
                    </button>
                </div>

                <!-- Botón Registrar Unidad -->
                <button type="button" 
                        wire:click="openCreateModal"
                        class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 active:scale-95 transition-all shadow-md shadow-emerald-500/20 rounded-xl whitespace-nowrap">
                    <svg class="w-4 h-4 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span>Nueva Unidad</span>
                </button>

                <!-- Selector de Vista (Cards / Lista) -->
                <div class="flex items-center p-1 bg-slate-100 dark:bg-slate-900/80 rounded-xl border dark:border-slate-800/60 shadow-inner">
                    <button type="button" 
                            wire:click="$set('viewMode', 'cards')"
                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $viewMode === 'cards' ? 'bg-white dark:bg-slate-800 text-slate-950 dark:text-white shadow-sm' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-300' }}"
                            title="Vista Tarjetas">
                        <svg class="w-4 h-4 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                        <span class="hidden sm:inline">Tarjetas</span>
                    </button>
                    <button type="button" 
                            wire:click="$set('viewMode', 'list')"
                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $viewMode === 'list' ? 'bg-white dark:bg-slate-800 text-slate-950 dark:text-white shadow-sm' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-300' }}"
                            title="Vista Lista">
                        <svg class="w-4 h-4 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <span class="hidden sm:inline">Lista</span>
                    </button>
                </div>
            </div>
        </div>

        @if ($viewMode === 'cards')
            <!-- VISTA TARJETAS (CARDS) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @forelse ($this->uniMedidas as $uni)
                    <div class="glass-card hover:-translate-y-1 transition duration-300 p-5 flex flex-col justify-between h-48 group">
                        <div class="space-y-4">
                            <!-- Cabecera de la Card: Abreviatura + Estado -->
                            <div class="flex justify-between items-start">
                                <span class="inline-flex items-center justify-center font-black text-sm px-3.5 py-1.5 rounded-xl uppercase bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/15 group-hover:scale-105 transition-transform">
                                    {{ $uni->abreviatura }}
                                </span>
                                <div>
                                    @if ($uni->trashed())
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20">
                                            <span class="w-1.2 h-1.2 rounded-full bg-rose-500 alert-pulse"></span>
                                            Eliminado
                                        </span>
                                    @elseif ($uni->activo)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                            <span class="w-1.2 h-1.2 rounded-full bg-emerald-500"></span>
                                            Activo
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                                            <span class="w-1.2 h-1.2 rounded-full bg-amber-500"></span>
                                            Inactivo
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Info: Nombre y descripción rápida -->
                            <div class="space-y-1">
                                <h3 class="font-extrabold text-slate-800 dark:text-white text-base group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                    {{ $uni->nombre }}
                                </h3>
                                <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400 font-medium">
                                    <svg class="w-4.5 h-4.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                    <span>{{ $uni->presentaciones_count }} presentaciones asociadas</span>
                                </div>
                            </div>
                        </div>

                        <!-- Acciones Inferiores -->
                        <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100 dark:border-slate-800/40">
                            @if ($uni->trashed())
                                <button type="button" 
                                        wire:click="restore({{ $uni->id }})"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 rounded-xl shadow-sm hover:shadow active:scale-95 transition-all">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
                                    </svg>
                                    <span>Restaurar</span>
                                </button>
                                
                                <button type="button" 
                                        wire:click="confirmDelete({{ $uni->id }})"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-white bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-500 hover:to-red-500 rounded-xl shadow-sm hover:shadow active:scale-95 transition-all">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.9 12.1A2 2 0 0116.1 21H7.9a2 2 0 01-2-1.9L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    <span>Eliminar</span>
                                </button>
                            @else
                                <button type="button" 
                                        wire:click="openEditModal({{ $uni->id }})"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-white bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-500 hover:to-blue-500 rounded-xl shadow-sm hover:shadow active:scale-95 transition-all">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                    </svg>
                                    <span>Editar</span>
                                </button>

                                <button type="button" 
                                        wire:click="confirmDelete({{ $uni->id }})"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700/60 rounded-xl active:scale-95 transition-all">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.9 12.1A2 2 0 0116.1 21H7.9a2 2 0 01-2-1.9L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    <span>Eliminar</span>
                                </button>
                            @endif
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
                                <h3 class="text-sm font-bold text-slate-800 dark:text-white">No se encontraron unidades de medida</h3>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
        @else
            <!-- VISTA LISTA / TABLA -->
            <div class="glass-card overflow-hidden">
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
                                <th class="py-3.5 px-5 font-bold uppercase tracking-wider text-[10px] text-slate-400 dark:text-slate-500">
                                    <button type="button" wire:click="sortBy('abreviatura')" class="inline-flex items-center gap-1 hover:text-slate-600 dark:hover:text-slate-300">
                                        <span>Abreviatura</span>
                                        @if ($sortField === 'abreviatura')
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $sortDirection === 'asc' ? 'M4.5 15.75l7.5-7.5 7.5 7.5' : 'M19.5 8.25l-7.5 7.5-7.5-7.5' }}" />
                                            </svg>
                                        @endif
                                    </button>
                                </th>
                                <th class="py-3.5 px-5 font-bold uppercase tracking-wider text-[10px] text-slate-400 dark:text-slate-500 text-center">Presentaciones</th>
                                <th class="py-3.5 px-5 font-bold uppercase tracking-wider text-[10px] text-slate-400 dark:text-slate-500">Estado</th>
                                <th class="py-3.5 px-5 font-bold uppercase tracking-wider text-[10px] text-slate-400 dark:text-slate-500 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/40 text-slate-700 dark:text-slate-300">
                            @forelse ($this->uniMedidas as $uni)
                                <tr class="hover:bg-slate-50/40 dark:hover:bg-slate-900/30 transition duration-150">
                                    <!-- Nombre -->
                                    <td class="py-4 px-5">
                                        <div class="font-bold text-slate-900 dark:text-white">
                                            {{ $uni->nombre }}
                                        </div>
                                    </td>
                                    
                                    <!-- Abreviatura -->
                                    <td class="py-4 px-5">
                                        <span class="inline-flex items-center justify-center font-bold text-xs px-2.5 py-1 rounded-lg uppercase bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/15">
                                            {{ $uni->abreviatura }}
                                        </span>
                                    </td>

                                    <!-- Presentaciones -->
                                    <td class="py-4 px-5 text-center font-bold text-slate-600 dark:text-slate-400">
                                        {{ $uni->presentaciones_count }}
                                    </td>

                                    <!-- Estado -->
                                    <td class="py-4 px-5">
                                        @if ($uni->trashed())
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500 alert-pulse"></span>
                                                Eliminado
                                            </span>
                                        @elseif ($uni->activo)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                Activo
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                Inactivo
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Acciones -->
                                    <td class="py-4 px-5 text-right">
                                        <div class="flex items-center justify-end gap-3 flex-wrap">
                                            @if ($uni->trashed())
                                                <button type="button" 
                                                        wire:click="restore({{ $uni->id }})"
                                                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 rounded-xl shadow-sm hover:shadow active:scale-95 transition-all">
                                                    <span>Restaurar</span>
                                                </button>
                                                
                                                <button type="button" 
                                                        wire:click="confirmDelete({{ $uni->id }})"
                                                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-bold text-white bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-500 hover:to-red-500 rounded-xl shadow-sm hover:shadow active:scale-95 transition-all">
                                                    <span>Eliminar</span>
                                                </button>
                                            @else
                                                <button type="button" 
                                                        wire:click="openEditModal({{ $uni->id }})"
                                                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-bold text-white bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-500 hover:to-blue-500 rounded-xl shadow-sm hover:shadow active:scale-95 transition-all">
                                                    <span>Editar</span>
                                                </button>

                                                <button type="button" 
                                                        wire:click="confirmDelete({{ $uni->id }})"
                                                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-bold text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700/60 rounded-xl active:scale-95 transition-all">
                                                    <span>Eliminar</span>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-12 px-5 text-center text-xs font-semibold text-slate-500 dark:text-slate-400">
                                        No se encontraron unidades de medida.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Paginación -->
        @if ($this->uniMedidas->hasPages())
            <div class="px-5 py-4 bg-white/40 dark:bg-slate-900/20 border border-slate-200/50 dark:border-slate-800/40 rounded-2xl">
                {{ $this->uniMedidas->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL CREAR / EDITAR UNIDAD -->
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
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </span>
                    <span>{{ $uniMedidaId ? 'Editar Unidad de Medida' : 'Nueva Unidad de Medida' }}</span>
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
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Nombre *</label>
                    <input type="text" 
                           wire:model="nombre" 
                           placeholder="Ej: Kilogramo, Unidad, Litro..."
                           class="w-full rounded-xl border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-800 text-slate-800 dark:text-white px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition">
                    @error('nombre') <span class="text-xs text-rose-500 font-medium mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Abreviatura -->
                <div class="space-y-1.5">
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Abreviatura *</label>
                    <input type="text" 
                           wire:model="abreviatura" 
                           placeholder="Ej: kg, und, L, paq..."
                           class="w-full rounded-xl border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-800 text-slate-800 dark:text-white px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition">
                    @error('abreviatura') <span class="text-xs text-rose-500 font-medium mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Activo Toggle -->
                <div class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-900/40 rounded-2xl border dark:border-slate-800/60">
                    <div class="space-y-0.5">
                        <span class="text-xs font-bold text-slate-800 dark:text-white uppercase tracking-wider">Estado Activo</span>
                        <p class="text-[10px] text-slate-500 dark:text-slate-400">Desactiva esta unidad para que no se pueda usar en nuevas presentaciones</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" wire:model="activo" class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 dark:bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                    </label>
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
                            Esta acción es irreversible. Se eliminará la unidad de medida de forma permanente y ya no se podrá recuperar.
                        @else
                            La unidad será desactivada y enviada a la papelera. Podrás restaurarla en cualquier momento si la necesitas nuevamente.
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
                        wire:click="{{ $estado === 'trashed' ? 'forceDelete(' . $uniMedidaToDeleteId . ')' : 'delete' }}"
                        class="px-4.5 py-2 text-xs font-bold text-white bg-rose-600 hover:bg-rose-500 active:scale-95 transition-all shadow-sm shadow-rose-500/20 rounded-lg">
                    Confirmar
                </button>
            </div>
        </div>
    </div>

    <!-- Estilos Locales -->
    <style>
        .unimedidas-root {
            --m-border: rgba(148, 163, 184, 0.16);
            --m-text: #e2e8f0;
            --m-muted: #94a3b8;
        }
    </style>
</x-filament-panels::page>
