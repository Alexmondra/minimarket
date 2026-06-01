<x-filament-panels::page>
    <div class="presentaciones-root space-y-6 animate-fade-in">
        <!-- Tarjetas de Estadísticas (KPIs) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Total Tipos -->
            <div class="kpi-card kpi-indigo">
                <div class="flex justify-between items-start">
                    <div class="space-y-1.5">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Variantes de Presentación</span>
                        <div class="text-2xl font-black text-slate-950 dark:text-white">
                            {{ $this->stats['total_types'] }}
                        </div>
                    </div>
                    <div class="p-2 bg-indigo-500/10 text-indigo-500 rounded-xl">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Presentaciones asignadas -->
            <div class="kpi-card kpi-emerald">
                <div class="flex justify-between items-start">
                    <div class="space-y-1.5">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Presentaciones en Productos</span>
                        <div class="text-2xl font-black text-slate-950 dark:text-white">
                            {{ $this->stats['total_presentations'] }}
                        </div>
                    </div>
                    <div class="p-2 bg-emerald-500/10 text-emerald-500 rounded-xl">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7.5L12 3 4 7.5M20 7.5v9L12 21M20 7.5l-8 4.5M4 7.5v9L12 21M4 7.5l8 4.5" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Barra de Búsqueda, Acciones y Filtros -->
        <div class="glass-card p-4 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <!-- Izquierda: Buscador + Botón Nueva Presentación -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 flex-1">
                <!-- Buscador -->
                <div class="relative flex-1 max-w-md">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.602 10.602z" />
                        </svg>
                    </div>
                    <input type="text" 
                           wire:model.live="search"
                           placeholder="Buscar presentaciones..."
                           class="w-full pl-10 pr-4 py-2.5 text-sm rounded-xl border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition">
                </div>

                <!-- Botón Nueva Presentación -->
                <button type="button" 
                        wire:click="openCreateModal"
                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 active:scale-95 transition-all shadow-md shadow-emerald-500/20 rounded-xl whitespace-nowrap">
                    <svg class="w-4 h-4 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span>Nueva Presentación</span>
                </button>
            </div>

            <!-- Derecha: Filtros de Estado -->
            <div class="flex flex-wrap items-center justify-between w-full lg:w-auto gap-3">
                <span class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider hidden sm:inline">Filtrar por Productos:</span>
                <div class="flex items-center gap-1.5 p-1 bg-slate-100 dark:bg-slate-900/80 rounded-xl border dark:border-slate-800/60">
                    <button type="button" 
                            wire:click="$set('estado', 'all')"
                            class="px-4 py-1.5 rounded-lg text-xs font-bold transition-all {{ $estado === 'all' ? 'bg-white dark:bg-slate-800 text-slate-950 dark:text-white shadow-sm' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-300' }}">
                        Todas
                    </button>
                    <button type="button" 
                            wire:click="$set('estado', 'con_productos')"
                            class="px-4 py-1.5 rounded-lg text-xs font-bold transition-all {{ $estado === 'con_productos' ? 'bg-white dark:bg-slate-800 text-slate-950 dark:text-white shadow-sm' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-300' }}">
                        Con Productos
                    </button>
                </div>
            </div>
        </div>

        <!-- Grid de Presentaciones -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @forelse ($this->presentaciones as $pres)
                <div class="glass-card hover:-translate-y-1 transition duration-300 p-5 flex flex-col justify-between h-64 group">
                    <div class="space-y-4">
                        <!-- Cabecera: Mini Preview de Imagen / Icono por defecto + Badge de Productos -->
                        <div class="flex justify-between items-start gap-3">
                            <div class="relative w-14 h-14 rounded-2xl overflow-hidden bg-slate-100 dark:bg-slate-800 border dark:border-slate-700/60 flex items-center justify-center shrink-0 shadow-inner">
                                @if ($pres->imagen_ejemplo)
                                    <img src="{{ url('storage/' . $pres->imagen_ejemplo) }}" class="w-full h-full object-cover" />
                                @else
                                    <div class="absolute inset-0 bg-gradient-to-tr from-indigo-500/10 to-violet-500/5 dark:from-indigo-500/20 dark:to-violet-500/10"></div>
                                    <svg class="w-7 h-7 text-indigo-500/70" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25" />
                                    </svg>
                                @endif
                            </div>
                            
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-xl text-[10px] font-extrabold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 shadow-sm">
                                {{ $pres->total_productos }} {{ $pres->total_productos === 1 ? 'producto' : 'productos' }}
                            </span>
                        </div>

                        <!-- Detalle: Nombre, unidad, equivalencia -->
                        <div class="space-y-1">
                            <h3 class="font-extrabold text-slate-800 dark:text-white text-base group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors truncate">
                                {{ $pres->tipo_presentacion }}
                            </h3>
                            <div class="space-y-0.5 text-xs text-slate-500 dark:text-slate-400 font-medium">
                                <p class="flex items-center gap-1.5">
                                    <span class="text-slate-400">Unidad:</span>
                                    <span class="font-semibold text-slate-700 dark:text-slate-300">
                                        {{ $pres->unidadMedida?->nombre ?? 'Sin definir' }}
                                    </span>
                                </p>
                                <p class="flex items-center gap-1.5">
                                    <span class="text-slate-400">Equivalencia:</span>
                                    <span class="font-semibold text-slate-700 dark:text-slate-300">
                                        {{ (float)$pres->cantidad }} {{ $pres->unidadMedida?->abreviatura }}
                                    </span>
                                </p>
                            </div>
                        </div>
                                        <!-- Acciones Inferiores -->
                    <div class="flex flex-wrap items-center justify-between sm:justify-end gap-1.5 pt-4 border-t border-slate-100 dark:border-slate-800/40">
                        <button type="button" 
                                wire:click="openProductsModal('{{ $pres->tipo_presentacion }}')"
                                class="inline-flex items-center gap-1 px-2.5 sm:px-3 py-1 sm:py-1.5 text-[10px] sm:text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:bg-indigo-500/10 rounded-xl transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>Ver</span>
                        </button>
                        
                        <button type="button" 
                                wire:click="openRenameModal('{{ $pres->tipo_presentacion }}')"
                                class="inline-flex items-center gap-1 px-2.5 sm:px-3 py-1 sm:py-1.5 text-[10px] sm:text-xs font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                            </svg>
                            <span>Editar</span>
                        </button>

                        <button type="button" 
                                wire:click="confirmDelete('{{ $pres->tipo_presentacion }}')"
                                class="inline-flex items-center gap-1 px-2.5 sm:px-3 py-1 sm:py-1.5 text-[10px] sm:text-xs font-bold text-rose-600 dark:text-rose-400 hover:bg-rose-500/10 rounded-xl transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.9 12.1A2 2 0 0116.1 21H7.9a2 2 0 01-2-1.9L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            <span>Eliminar</span>
                        </button>
                    </div>
                </div>
            @empty
                <!-- Estado Vacío -->
                <div class="col-span-full py-16 text-center">
                    <div class="max-w-md mx-auto space-y-4">
                        <div class="inline-flex p-4 bg-slate-50 dark:bg-slate-900/60 rounded-full border border-slate-200/50 dark:border-slate-800/40 text-slate-400">
                            <svg class="w-12 h-12 stroke-[1.25]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.602 10.602z" />
                            </svg>
                        </div>
                        <div class="space-y-1">
                            <h3 class="text-sm font-bold text-slate-800 dark:text-white">No se encontraron presentaciones</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                Intente buscar con otra palabra clave o limpie los filtros activos.
                            </p>
                        </div>
                        <button type="button" 
                                wire:click="$set('search', ''); $set('estado', 'all');"
                                class="inline-flex items-center gap-1 px-4 py-2 text-xs font-bold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 rounded-lg shadow-sm transition dark:bg-slate-900/40 dark:border-slate-800 dark:text-slate-300">
                            Limpiar filtros
                        </button>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Paginación -->
        @if ($this->presentaciones->hasPages())
            <div class="px-5 py-4 bg-white/40 dark:bg-slate-900/20 border border-slate-200/50 dark:border-slate-800/40 rounded-2xl">
                {{ $this->presentaciones->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL CREAR PRESENTACIÓN GLOBAL -->
    <div x-data="{ open: @entangle('showModal') }" 
         x-show="open" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto"
         style="display: none;">
        
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity"
             @click="open = false">
        </div>

        <!-- Contenido Modal -->
        <div class="relative bg-white dark:bg-[#0c101d] border border-slate-200 dark:border-[#1c243a] rounded-3xl w-full max-w-lg overflow-hidden shadow-2xl transition-all animate-fade-in my-8 z-10">
            <!-- Cabecera -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-[#1d2745]/30">
                <h2 class="text-base font-extrabold text-slate-800 dark:text-white flex items-center gap-2">
                    <span class="p-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-500/10">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                    </span>
                    <span>Asociar Nueva Presentación</span>
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
            <form wire:submit.prevent="save" class="p-6 space-y-4 max-h-[80vh] overflow-y-auto scrollbar-thin">
                <!-- Buscador Autocomplete de Producto -->
                <div class="space-y-1.5 relative">
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Buscar Producto *</label>
                    <div class="relative">
                        <input type="text" 
                               wire:model.live="searchProductTerm" 
                               placeholder="Escriba el nombre o código interno del producto..."
                               class="w-full rounded-xl border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-800 text-slate-800 dark:text-white px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition">
                        @if (filled($producto_id))
                            <span class="absolute inset-y-0 right-0 flex items-center pr-3">
                                <svg class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </span>
                        @endif
                    </div>
                    @error('producto_id') <span class="text-xs text-rose-500 font-medium mt-1 block">{{ $message }}</span> @enderror

                    <!-- Resultados autocomplete -->
                    @if (count($productSearchResults) > 0)
                        <div class="absolute z-50 left-0 right-0 mt-1 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl max-h-48 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach ($productSearchResults as $result)
                                <button type="button" 
                                        wire:click="selectProduct({{ $result['id'] }}, '{{ addslashes($result['nombre']) }}')"
                                        class="w-full text-left px-4 py-2.5 hover:bg-slate-50 dark:hover:bg-slate-900 text-xs font-semibold text-slate-800 dark:text-slate-300 transition-colors">
                                    {{ $result['nombre'] }} <span class="text-slate-400">({{ $result['codigo_interno'] ?? 'Sin código' }})</span>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Nombre de la Presentación -->
                <div class="space-y-1.5">
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Nombre de Presentación (Ej: Pack x6, Bolsa 1kg) *</label>
                    <input type="text" 
                           wire:model="tipo_presentacion" 
                           placeholder="Escriba el tipo de presentación..."
                           class="w-full rounded-xl border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-800 text-slate-800 dark:text-white px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition">
                    @error('tipo_presentacion') <span class="text-xs text-rose-500 font-medium mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <!-- Cantidad (Equivalencia) -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Equivalencia *</label>
                        <input type="number" 
                               wire:model="cantidad" 
                               min="1"
                               class="w-full rounded-xl border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-800 text-slate-800 dark:text-white px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition">
                        @error('cantidad') <span class="text-xs text-rose-500 font-medium mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Unidad de Medida -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Unidad de Medida *</label>
                        <select wire:model="unidad_medida_id"
                                class="w-full rounded-xl border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-800 text-slate-800 dark:text-white px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition">
                            <option value="">Seleccione...</option>
                            @foreach ($this->unidadesMedida as $u)
                                <option value="{{ $u->id }}">{{ $u->nombre }} ({{ $u->abreviatura }})</option>
                            @endforeach
                        </select>
                        @error('unidad_medida_id') <span class="text-xs text-rose-500 font-medium mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Es Pesable Toggle -->
                <div class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-900/40 rounded-2xl border dark:border-slate-800/60">
                    <div class="space-y-0.5">
                        <span class="text-xs font-bold text-slate-800 dark:text-white uppercase tracking-wider">Es pesable (Granel)</span>
                        <p class="text-[10px] text-slate-500 dark:text-slate-400">Habilite si se vende al peso utilizando balanzas en la caja</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" wire:model="es_pesable" class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 dark:bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                    </label>
                </div>

                <!-- Carga de Imagen -->
                <div class="space-y-1.5">
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Imagen de la Presentación</label>
                    @if ($imagen)
                        <div class="mb-2">
                            <img src="{{ $imagen->temporaryUrl() }}" class="h-20 w-20 object-contain rounded-xl border dark:border-slate-800 bg-white dark:bg-slate-900/60 p-1">
                        </div>
                    @endif
                    <input type="file" 
                           wire:model="imagen"
                           class="w-full text-xs text-slate-500 dark:text-slate-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 dark:file:bg-indigo-950/40 dark:file:text-indigo-400 hover:file:bg-indigo-100 dark:hover:file:bg-indigo-950/60 transition">
                    @error('imagen') <span class="text-xs text-rose-500 font-medium mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Códigos de barra interactivos -->
                <div class="space-y-1.5">
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Códigos de Barras</label>
                    <div class="flex gap-2">
                        <input type="text" 
                               wire:model="nuevo_codigo_barra" 
                               wire:keydown.enter.prevent="agregarCodigoBarra"
                               placeholder="Escanee o digite código y presione Enter..."
                               class="w-full rounded-xl border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-800 text-slate-800 dark:text-white px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition">
                        <button type="button" 
                                wire:click="agregarCodigoBarra"
                                class="px-4 py-2 bg-indigo-500 hover:bg-indigo-600 active:scale-95 text-white font-bold rounded-xl transition text-sm">
                            +
                        </button>
                    </div>
                    @error('nuevo_codigo_barra') <span class="text-xs text-rose-500 font-medium mt-1 block">{{ $message }}</span> @enderror

                    <!-- Tags listados -->
                    <div class="flex flex-wrap gap-1.5 pt-2">
                        @foreach ($barras as $index => $code)
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 border dark:border-slate-700/60">
                                <span>{{ $code }}</span>
                                <button type="button" 
                                        wire:click="removerCodigoBarra({{ $index }})"
                                        class="text-rose-500 hover:text-rose-600 transition font-black ml-1">
                                    &times;
                                </button>
                            </span>
                        @endforeach
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

    <!-- MODAL RENOMBRAR PRESENTACIÓN -->
    <div x-data="{ openRename: @entangle('showRenameModal') }" 
         x-show="openRename" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto"
         style="display: none;">
        
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity"
             @click="openRename = false">
        </div>

        <!-- Contenido Modal -->
        <div class="relative bg-white dark:bg-[#0c101d] border border-slate-200 dark:border-[#1c243a] rounded-3xl w-full max-w-md overflow-hidden shadow-2xl transition-all animate-fade-in my-8 z-10">
            <!-- Cabecera -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-[#1d2745]/30">
                <h2 class="text-base font-extrabold text-slate-800 dark:text-white flex items-center gap-2">
                    <span class="p-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-500/10">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                        </svg>
                    </span>
                    <span>Renombrar Variant</span>
                </h2>
                <button type="button" 
                        @click="openRename = false"
                        class="p-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-900 text-slate-400 hover:text-slate-600 dark:text-slate-500 dark:hover:text-slate-300 transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Formulario -->
            <form wire:submit.prevent="rename" class="p-6 space-y-4">
                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                    Esto cambiará el nombre de presentación de todos los productos asociados con: <strong class="text-slate-800 dark:text-white">{{ $old_tipo_presentacion }}</strong>.
                </p>

                <!-- Nuevo Nombre -->
                <div class="space-y-1.5">
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Nuevo Nombre *</label>
                    <input type="text" 
                           wire:model="new_tipo_presentacion" 
                           placeholder="Ej: Lata 400ml..."
                           class="w-full rounded-xl border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-800 text-slate-800 dark:text-white px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition">
                    @error('new_tipo_presentacion') <span class="text-xs text-rose-500 font-medium mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Footer Acciones -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-[#1d2745]/30">
                    <button type="button" 
                            @click="openRename = false"
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

    <!-- MODAL CONFIRMAR ELIMINACIÓN GLOBAL -->
    <div x-data="{ openDelete: @entangle('showDeleteConfirmModal') }" 
         x-show="openDelete" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto"
         style="display: none;">
        
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity"
             @click="openDelete = false">
        </div>

        <!-- Ventana Modal -->
        <div class="relative bg-white dark:bg-[#0c101d] border border-slate-200 dark:border-[#1c243a] rounded-3xl w-full max-w-md overflow-hidden shadow-2xl transition-all animate-fade-in my-8 z-10">
            <!-- Icono y Advertencia -->
            <div class="p-6 text-center space-y-4">
                <div class="inline-flex p-3 bg-rose-500/10 text-rose-500 rounded-full border border-rose-500/20">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="space-y-1.5">
                    <h3 class="text-base font-extrabold text-slate-900 dark:text-white">
                        ¿Eliminar variant globalmente?
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                        Esta acción eliminará la presentación <strong class="text-rose-600 dark:text-rose-400">{{ $tipo_presentacion_to_delete }}</strong> de todos los <strong class="text-slate-800 dark:text-white">{{ $affected_products_count }} productos</strong> asociados. Se perderán las imágenes asociadas y los códigos de barra.
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
                        wire:click="delete"
                        class="px-4.5 py-2 text-xs font-bold text-white bg-rose-600 hover:bg-rose-500 active:scale-95 transition-all shadow-sm shadow-rose-500/20 rounded-lg">
                    Confirmar
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL VER PRODUCTOS ASOCIADOS (PREMIUM REDESIGNED) -->
    <div x-data="{ openProducts: @entangle('showProductsModal') }" 
         x-show="openProducts" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto"
         style="display: none;">
        
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity"
             @click="openProducts = false">
        </div>

        <!-- Contenido Modal -->
        <div class="relative bg-white dark:bg-[#0c101d] border border-slate-200 dark:border-[#1c243a] rounded-3xl w-full max-w-2xl overflow-hidden shadow-2xl transition-all animate-fade-in my-8 z-10">
            <!-- Cabecera -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-[#1d2745]/30">
                <h2 class="text-base font-extrabold text-slate-800 dark:text-white flex items-center gap-2">
                    <span class="p-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-500/10">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                        </svg>
                    </span>
                    <span>Productos con Variant: {{ $selectedPresentacionTipo }}</span>
                </h2>
                <button type="button" 
                        @click="openProducts = false"
                        class="p-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-900 text-slate-400 hover:text-slate-600 dark:text-slate-500 dark:hover:text-slate-300 transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Tabla Premium -->
            <div class="p-6">
                @if (empty($productsList))
                    <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 text-center py-6">
                        No hay productos con esta presentación actualmente.
                    </p>
                @else
                    <div class="overflow-x-auto rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm max-h-[50vh] overflow-y-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-slate-50/40 dark:bg-slate-950/20 text-slate-400 dark:text-slate-500 font-bold uppercase tracking-wider border-b dark:border-slate-800">
                                    <th scope="col" class="px-5 py-3.5">Producto</th>
                                    <th scope="col" class="px-5 py-3.5">Código</th>
                                    <th scope="col" class="px-5 py-3.5">Categoría</th>
                                    <th scope="col" class="px-5 py-3.5">Marca</th>
                                    <th scope="col" class="px-5 py-3.5 text-right">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/40 text-slate-700 dark:text-slate-300">
                                @foreach ($productsList as $p)
                                    <tr class="hover:bg-slate-50/40 dark:hover:bg-slate-900/30 transition duration-150">
                                        <td class="px-5 py-4 font-bold text-slate-900 dark:text-white max-w-[180px] truncate" title="{{ $p->nombre }}">
                                            {{ $p->nombre }}
                                        </td>
                                        <td class="px-5 py-4 font-semibold">
                                            {{ $p->codigo_interno ?? '—' }}
                                        </td>
                                        <td class="px-5 py-4 text-slate-500 max-w-[120px] truncate" title="{{ $p->categoria->nombre ?? '—' }}">
                                            {{ $p->categoria->nombre ?? '—' }}
                                        </td>
                                        <td class="px-5 py-4 text-slate-500 max-w-[120px] truncate" title="{{ $p->marca->nombre ?? '—' }}">
                                            {{ $p->marca->nombre ?? '—' }}
                                        </td>
                                        <td class="px-5 py-4 text-right">
                                            <a href="{{ $this->getProductEditUrl($p->id) }}" 
                                               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-[10px] font-bold text-white bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-500 hover:to-blue-500 rounded-xl shadow-sm hover:shadow active:scale-95 transition-all">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                                </svg>
                                                <span>Editar</span>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <!-- Footer -->
            <div class="flex items-center justify-end gap-3 p-4 bg-slate-50 dark:bg-slate-950/20 border-t border-slate-100 dark:border-[#1d2745]/30">
                <button type="button" 
                        @click="openProducts = false"
                        class="px-5 py-2.5 text-xs font-bold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl border dark:border-slate-700 transition">
                    Cerrar
                </button>
            </div>
        </div>
    </div>

    <!-- Estilos Locales -->
    <style>
        .presentaciones-root {
            --m-border: rgba(148, 163, 184, 0.16);
            --m-text: #e2e8f0;
            --m-muted: #94a3b8;
        }
    </style>
</x-filament-panels::page>
