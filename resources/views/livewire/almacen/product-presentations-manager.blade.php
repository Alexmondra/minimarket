<div class="space-y-8 animate-fade-in">
    <!-- 1. HEADER CARD (DATOS DEL PRODUCTO) -->
    <div class="relative overflow-hidden rounded-[24px] bg-gradient-to-r from-indigo-50/90 via-slate-50/70 to-white/40 dark:from-[#171336] dark:via-[#0d101e] dark:to-[#0a0c14] p-6 md:p-8 shadow-xl border border-slate-200/80 dark:border-[#1d2745]/60 text-slate-800 dark:text-white">
        <!-- Background ambient glow blobs -->
        <div class="absolute right-0 top-0 -mr-20 -mt-20 h-80 w-80 rounded-full bg-indigo-500/[0.03] dark:bg-indigo-500/5 blur-3xl"></div>
        <div class="absolute left-0 bottom-0 -ml-20 -mb-20 h-80 w-80 rounded-full bg-purple-500/[0.03] dark:bg-purple-500/5 blur-3xl"></div>

        <div class="relative flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div class="flex flex-col sm:flex-row items-center gap-6 w-full">
                <!-- Icono del Producto -->
                <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-md">
                    <svg class="h-10 w-10 text-[#a855f7] stroke-1.25" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                    </svg>
                </div>

                <!-- Detalles e Identificación -->
                <div class="text-center sm:text-left space-y-2 flex-1 min-w-0">
                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                        <!-- Categoría Badge -->
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20">
                            {{ $record->categoria?->nombre ?? 'Sin Categoría' }}
                        </span>
                        <!-- Marca Badge -->
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20">
                            {{ $record->marca?->nombre ?? 'Sin Marca' }}
                        </span>
                        <!-- Afecto IGV Badge -->
                        @if ($record->afecto_igv)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                Afecto IGV
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-500/10 text-slate-600 dark:text-slate-400 border border-slate-500/20">
                                Exonerado IGV
                            </span>
                        @endif
                        <!-- Activo Badge -->
                        @if ($record->activo)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 dark:bg-emerald-400"></span>
                                Activo
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20">
                                <span class="h-1.5 w-1.5 rounded-full bg-rose-500 dark:bg-rose-400"></span>
                                Inactivo
                            </span>
                        @endif
                    </div>

                    <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-slate-950 dark:text-white truncate">
                        {{ $record->nombre }}
                    </h1>
                    <p class="text-sm font-semibold text-slate-500 dark:text-slate-400 flex items-center justify-center sm:justify-start gap-1">
                        <span class="text-purple-600 dark:text-[#a855f7] font-bold">Cód. Interno</span> 
                        <span class="text-slate-700 dark:text-slate-300 ml-1">{{ $record->codigo_interno ?: '—' }}</span>
                    </p>
                    @if ($record->descripcion)
                        <p class="text-xs text-slate-500 dark:text-slate-400 max-w-xl truncate">
                            {{ $record->descripcion }}
                        </p>
                    @endif
                </div>
            </div>

            <!-- Botón Editar Datos del Producto -->
            <div class="w-full md:w-auto shrink-0 flex justify-center sm:justify-end">
                <button type="button" 
                        wire:click="abrirEditarProducto" 
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-bold text-slate-700 dark:text-slate-200 bg-white/90 dark:bg-slate-900/40 hover:bg-slate-100 dark:hover:bg-slate-900/60 active:bg-slate-200 dark:active:bg-slate-950 border border-slate-200 dark:border-slate-700/60 rounded-xl transition shadow-sm">
                    <svg class="h-4 w-4 text-slate-500 dark:text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.83 20.082a4.5 4.5 0 0 1-2.012 1.257l-3.858 1.05a.75.75 0 0 1-.922-.922l1.05-3.858a4.5 4.5 0 0 1 1.257-2.012L17.8 7.893z" />
                    </svg>
                    <span>Editar Producto</span>
                </button>
            </div>
        </div>
    </div>

    <!-- 2. PRESENTACIONES DEL PRODUCTO (GRID & CARDS) -->
    <div class="space-y-4">
        <div class="flex items-center gap-2 border-b border-slate-200/50 dark:border-[#1d2745]/30 pb-3">
            <h2 class="text-lg font-bold text-slate-800 dark:text-white">Presentaciones registradas</h2>
            <span class="inline-flex items-center justify-center h-6 px-2.5 rounded-full text-xs font-extrabold bg-indigo-500/10 text-indigo-600 dark:text-indigo-400">
                {{ $presentaciones->count() }}
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach ($presentaciones as $p)
                <div class="group relative flex flex-col bg-white dark:bg-[#0d111f] rounded-2xl border border-slate-200/80 dark:border-[#1d2745]/60 shadow-md hover:shadow-xl hover:border-indigo-500/50 dark:hover:border-indigo-400/50 transition-all duration-300 hover:-translate-y-1 overflow-hidden cursor-pointer"
                     wire:click="abrirEditar({{ $p->id }})">
                    
                    <!-- Imagen de la presentación -->
                    <div class="aspect-video w-full overflow-hidden bg-slate-100/80 dark:bg-slate-900/60 relative border-b border-slate-200/50 dark:border-[#1d2745]/40 flex items-center justify-center">
                        @if ($p->imagen)
                            <img src="{{ $p->imagen_url }}" alt="{{ $p->tipo_presentacion }}" class="h-full w-full object-contain p-2 group-hover:scale-105 transition duration-500">
                        @else
                            <div class="flex flex-col items-center gap-1.5 text-slate-400 dark:text-slate-600">
                                <svg class="h-10 w-10 stroke-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                                </svg>
                                <span class="text-[10px] font-bold uppercase tracking-wider">Sin Imagen</span>
                            </div>
                        @endif

                        <!-- Botón de Borrado Rápido -->
                        <button type="button" 
                                wire:click.stop="eliminarPresentacion({{ $p->id }})" 
                                onclick="confirm('¿Estás seguro de eliminar esta presentación? Se borrarán también sus códigos de barra.') || event.stopImmediatePropagation()"
                                class="absolute top-2 right-2 p-1.5 rounded-lg bg-white/90 dark:bg-slate-950/80 text-rose-600 dark:text-rose-400 border border-slate-200 dark:border-[#1d2745]/60 hover:bg-rose-50 dark:hover:bg-rose-950/30 opacity-0 group-hover:opacity-100 transition duration-300 shadow-sm z-10">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>
                        </button>

                        <!-- Badge de Pesable -->
                        @if ($p->es_pesable)
                            <span class="absolute bottom-2 left-2 px-2 py-0.5 rounded-full text-[9px] font-bold bg-amber-500/10 dark:bg-amber-400/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 uppercase tracking-wider">
                                Pesable
                            </span>
                        @endif
                    </div>

                    <!-- Detalles de la presentación -->
                    <div class="p-4 flex-1 flex flex-col justify-between space-y-3">
                        <div class="space-y-1">
                            <h3 class="text-sm font-extrabold text-slate-800 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                {{ $p->tipo_presentacion }}
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 font-bold flex items-center gap-1">
                                <span>Cantidad:</span>
                                <span class="text-slate-800 dark:text-slate-200">{{ $p->cantidad }} {{ $p->unidadMedida?->abreviatura }}</span>
                            </p>

                            <!-- Indicador de Presentación Base -->
                            @if ($p->presentacionBase)
                                <div class="mt-1 flex items-center gap-1 text-[10px] text-slate-500 dark:text-slate-400 italic">
                                    <svg class="h-3 w-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12h15m0 0l-6.75-6.75M19.5 12l-6.75 6.75" />
                                    </svg>
                                    <span class="truncate">Base: {{ $p->presentacionBase->tipo_presentacion }} ({{ $p->presentacionBase->cantidad }} {{ $p->presentacionBase->unidadMedida?->abreviatura }})</span>
                                </div>
                            @endif
                        </div>

                        <!-- Códigos de Barra Listado -->
                        <div class="pt-2 border-t border-slate-100 dark:border-[#1d2745]/30">
                            @if ($p->barras->count() > 0)
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($p->barras->take(2) as $b)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[9px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-mono">
                                            {{ $b->codigo_barra }}
                                        </span>
                                    @endforeach
                                    @if ($p->barras->count() > 2)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[9px] font-semibold bg-indigo-500/10 text-indigo-600 dark:text-indigo-400">
                                            +{{ $p->barras->count() - 2 }}
                                        </span>
                                    @endif
                                </div>
                            @else
                                <span class="text-[10px] text-slate-400 dark:text-slate-600 italic">Sin códigos de barra</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- TARJETA DE AGREGAR (+) -->
            <div class="group flex flex-col items-center justify-center min-h-[190px] border-2 border-dashed border-slate-300 dark:border-[#1d2745]/60 bg-slate-50/50 dark:bg-slate-900/10 hover:bg-slate-50 dark:hover:bg-slate-900/20 hover:border-indigo-500 dark:hover:border-indigo-400 rounded-2xl p-6 transition-all duration-300 hover:scale-105 cursor-pointer shadow-sm hover:shadow-md"
                 wire:click="abrirCrear">
                <div class="flex flex-col items-center gap-2 text-slate-400 dark:text-slate-600 group-hover:text-indigo-500 dark:group-hover:text-indigo-400 transition-colors">
                    <div class="p-3 bg-white dark:bg-slate-900/80 rounded-full border border-slate-200 dark:border-[#1d2745]/60 shadow-sm group-hover:scale-110 transition duration-300">
                        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                </div>
                <span class="text-xs font-bold uppercase tracking-wider mt-1">Agregar Presentación</span>
            </div>
        </div>
    </div>
</div>

    <!-- MODAL EDITAR PRODUCTO -->
    <div x-data="{ openProduct: @entangle('showProductModal') }" 
         x-show="openProduct" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto"
         style="display: none;">
        
        <!-- Backdrop/Overlay -->
        <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity"
             @click="openProduct = false"
             wire:click="cerrarProductModal">
        </div>

        <div class="relative bg-white dark:bg-[#0c101d] border border-slate-200 dark:border-[#1c243a] rounded-3xl w-full max-w-2xl overflow-hidden shadow-2xl transition-all animate-fade-in my-8 z-10">
            <!-- Encabezado del Modal -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-[#1d2745]/30">
                <h2 class="text-lg font-extrabold text-slate-800 dark:text-white flex items-center gap-2">
                    <span class="p-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-500/10">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                        </svg>
                    </span>
                    <span>Editar Datos del Producto</span>
                </h2>
                <button type="button" 
                        @click="openProduct = false"
                        wire:click="cerrarProductModal" 
                        class="p-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-900 text-slate-400 hover:text-slate-600 dark:text-slate-500 dark:hover:text-slate-300 transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Formulario Producto -->
            <form wire:submit.prevent="guardarProducto" x-on:submit.stop="" class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nombre -->
                    <div class="space-y-1.5 md:col-span-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Nombre del Producto *</label>
                        <input type="text" 
                               wire:model="product_nombre" 
                               class="w-full rounded-xl border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-800 text-slate-800 dark:text-white px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition">
                        @error('product_nombre') <span class="text-xs text-rose-500 font-medium">{{ $message }}</span> @enderror
                    </div>

                    <!-- Categoría -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Categoría *</label>
                        <select wire:model="product_categoria_id" 
                                class="w-full rounded-xl border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-800 text-slate-800 dark:text-white px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition">
                            <option value="">Seleccione Categoría...</option>
                            @foreach ($this->categorias as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                            @endforeach
                        </select>
                        @error('product_categoria_id') <span class="text-xs text-rose-500 font-medium">{{ $message }}</span> @enderror
                    </div>

                    <!-- Marca -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Marca *</label>
                        <select wire:model="product_marca_id" 
                                class="w-full rounded-xl border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-800 text-slate-800 dark:text-white px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition">
                            <option value="">Seleccione Marca...</option>
                            @foreach ($this->marcas as $mrc)
                                <option value="{{ $mrc->id }}">{{ $mrc->nombre }}</option>
                            @endforeach
                        </select>
                        @error('product_marca_id') <span class="text-xs text-rose-500 font-medium">{{ $message }}</span> @enderror
                    </div>

                    <!-- Cód. Interno -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Código Interno</label>
                        <input type="text" 
                               wire:model="product_codigo_interno" 
                               class="w-full rounded-xl border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-800 text-slate-800 dark:text-white px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition">
                        @error('product_codigo_interno') <span class="text-xs text-rose-500 font-medium">{{ $message }}</span> @enderror
                    </div>

                    <!-- Toggles: Afecto IGV / Activo -->
                    <div class="grid grid-cols-2 gap-4">
                        <!-- Afecto IGV -->
                        <div class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-900/20 border border-slate-100 dark:border-[#1d2745]/30 rounded-2xl">
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Afecto IGV</span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" wire:model="product_afecto_igv" class="sr-only peer">
                                <div class="w-9 h-5 bg-slate-200 dark:bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                            </label>
                        </div>
                        
                        <!-- Activo -->
                        <div class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-900/20 border border-slate-100 dark:border-[#1d2745]/30 rounded-2xl">
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Activo</span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" wire:model="product_activo" class="sr-only peer">
                                <div class="w-9 h-5 bg-slate-200 dark:bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                            </label>
                        </div>
                    </div>

                    <!-- Descripción -->
                    <div class="space-y-1.5 md:col-span-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Descripción</label>
                        <textarea wire:model="product_descripcion" 
                                  rows="3" 
                                  class="w-full rounded-xl border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-800 text-slate-800 dark:text-white px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition"></textarea>
                        @error('product_descripcion') <span class="text-xs text-rose-500 font-medium">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Botones del Modal -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-[#1d2745]/30">
                    <button type="button" 
                            @click="openProduct = false"
                            wire:click="cerrarProductModal" 
                            class="px-5 py-2.5 text-sm font-bold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl transition">
                        Cancelar
                    </button>
                    
                    <button type="submit" 
                            class="px-5 py-2.5 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 rounded-xl shadow-md transition duration-200">
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL DE PRESENTACIÓN -->
    <div x-data="{ open: @entangle('showModal') }" 
         x-show="open" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto"
         style="display: none;">
        
        <!-- Backdrop/Overlay -->
        <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity"
             @click="open = false"
             wire:click="cerrarModal">
        </div>

        <div class="relative bg-white dark:bg-[#0c101d] border border-slate-200 dark:border-[#1c243a] rounded-3xl w-full max-w-2xl overflow-hidden shadow-2xl transition-all animate-fade-in my-8 z-10">
            
            <!-- Encabezado del Modal -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-[#1d2745]/30">
                <h2 class="text-lg font-extrabold text-slate-800 dark:text-white flex items-center gap-2">
                    <span class="p-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-500/10">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                        </svg>
                    </span>
                    <span>{{ $editingPresentationId ? 'Editar Presentación' : 'Nueva Presentación' }}</span>
                </h2>
                <button type="button" 
                        @click="open = false"
                        wire:click="cerrarModal" 
                        class="p-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-900 text-slate-400 hover:text-slate-600 dark:text-slate-500 dark:hover:text-slate-300 transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Formulario -->
            <form wire:submit.prevent="guardar" x-on:submit.stop="" class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <!-- COLUMNA IZQUIERDA -->
                    <div class="space-y-5">
                        
                        <!-- Subida de Imagen -->
                        <div class="space-y-2">
                            <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Imagen</label>
                            <div class="flex flex-col items-center justify-center border border-dashed border-slate-300 dark:border-[#1d2745]/60 bg-slate-50/50 dark:bg-slate-900/10 rounded-2xl p-4 text-center relative hover:bg-slate-50 dark:hover:bg-slate-900/20 transition group">
                                @if ($imagen)
                                    <img src="{{ $imagen->temporaryUrl() }}" class="h-28 w-28 object-contain mb-2 rounded border border-slate-200 dark:border-[#1d2745]/40 bg-white">
                                @elseif ($existingImagen)
                                    <img src="{{ url('/storage/' . $existingImagen) }}" class="h-28 w-28 object-contain mb-2 rounded border border-slate-200 dark:border-[#1d2745]/40 bg-white">
                                @else
                                    <svg class="h-10 w-10 text-slate-400 dark:text-slate-600 mb-2 stroke-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375 0 1 1-.75 0 .375 0 0 1 .75 0Z" />
                                    </svg>
                                @endif

                                <input type="file" wire:model="imagen" class="absolute inset-0 opacity-0 cursor-pointer">
                                <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400 group-hover:underline">Subir Archivo</span>
                                <span class="text-[10px] text-slate-400 mt-0.5">PNG, JPG hasta 2MB</span>
                            </div>
                            @error('imagen') <span class="text-xs text-rose-500 font-medium">{{ $message }}</span> @enderror
                        </div>

                        <!-- Tipo de Presentación -->
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Tipo de Presentación *</label>
                            <input type="text" 
                                   wire:model="tipo_presentacion" 
                                   list="sugerencias-tipos"
                                   placeholder="Ej: Unidad, Caja, Six-pack..." 
                                   class="w-full rounded-xl border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-800 text-slate-800 dark:text-white px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition">
                            <datalist id="sugerencias-tipos">
                                @foreach ($this->tiposSugeridos as $sug)
                                    <option value="{{ $sug }}">
                                @endforeach
                            </datalist>
                            @error('tipo_presentacion') <span class="text-xs text-rose-500 font-medium">{{ $message }}</span> @enderror
                        </div>

                        <!-- Es pesable -->
                        <div class="flex items-center justify-between p-3.5 bg-slate-50 dark:bg-slate-900/20 border border-slate-100 dark:border-[#1d2745]/30 rounded-2xl">
                            <div class="flex flex-col">
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-300">¿Es pesable?</span>
                                <span class="text-[10px] text-slate-400 dark:text-slate-500">Active si el producto se vende al peso</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" wire:model="es_pesable" class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-200 dark:bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500"></div>
                            </label>
                        </div>

                    </div>

                    <!-- COLUMNA DERECHA -->
                    <div class="space-y-5">
                        
                        <!-- Cantidad -->
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Cantidad por empaque *</label>
                            <input type="number" 
                                   wire:model.live="cantidad" 
                                   min="1" 
                                   class="w-full rounded-xl border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-800 text-slate-800 dark:text-white px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition">
                            @error('cantidad') <span class="text-xs text-rose-500 font-medium">{{ $message }}</span> @enderror
                        </div>

                        <!-- Unidad de Medida -->
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Unidad de Medida *</label>
                            <select wire:model="unidad_medida_id" 
                                    class="w-full rounded-xl border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-800 text-slate-800 dark:text-white px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition">
                                <option value="">Seleccione Unidad...</option>
                                @foreach ($this->unidadesMedida as $um)
                                    <option value="{{ $um->id }}">{{ $um->nombre }} ({{ $um->abreviatura }})</option>
                                @endforeach
                            </select>
                            @error('unidad_medida_id') <span class="text-xs text-rose-500 font-medium">{{ $message }}</span> @enderror
                        </div>

                        <!-- Presentación Base (CONDICIONAL) -->
                        @if ($cantidad > 1)
                            <div class="space-y-1.5 p-4 bg-indigo-50/30 dark:bg-indigo-950/10 border border-indigo-100/50 dark:border-indigo-500/10 rounded-2xl animate-fade-in">
                                <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Presentación Base *</label>
                                
                                <!-- Input buscador -->
                                <input type="text" 
                                       wire:model.live="searchBaseTerm" 
                                       placeholder="Buscar presentación..." 
                                       class="w-full rounded-xl border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-800 text-slate-800 dark:text-white px-3 py-1.5 text-xs focus:ring-1 focus:ring-indigo-500 shadow-sm mb-2">

                                <select wire:model="presentacion_base_id" 
                                        class="w-full rounded-xl border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-800 text-slate-800 dark:text-white px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition">
                                    <option value="">Seleccionar Base...</option>
                                    @foreach ($this->presentacionesDisponibles as $pdb)
                                        <option value="{{ $pdb->id }}">{{ $pdb->tipo_presentacion }} x {{ $pdb->cantidad }} {{ $pdb->unidadMedida?->abreviatura }}</option>
                                    @endforeach
                                </select>
                                @error('presentacion_base_id') <span class="text-xs text-rose-500 font-medium">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        <!-- Códigos de Barra -->
                        <div class="space-y-2">
                            <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Códigos de Barra</label>
                            
                            <!-- Lista de códigos actuales -->
                            @if (count($barras) > 0)
                                <div class="flex flex-wrap gap-1.5 p-3 bg-slate-50 dark:bg-slate-900/20 border border-slate-100 dark:border-[#1d2745]/30 rounded-2xl max-h-[80px] overflow-y-auto">
                                    @foreach ($barras as $idx => $code)
                                        <span class="inline-flex items-center gap-1 pl-2.5 pr-1.5 py-1 rounded-lg text-xs font-bold bg-slate-200 dark:bg-slate-800 text-slate-800 dark:text-slate-300 font-mono shadow-sm">
                                            <span>{{ $code }}</span>
                                            <button type="button" 
                                                    wire:click="removerCodigoBarra({{ $idx }})" 
                                                    class="p-0.5 rounded-md hover:bg-slate-300 dark:hover:bg-slate-700 text-slate-500 dark:text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 transition">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <div class="p-3 text-center border border-dashed border-slate-200 dark:border-slate-800 rounded-2xl text-[10px] text-slate-400 dark:text-slate-600 italic bg-slate-50/20">
                                    Escanee o ingrese un código para comenzar
                                </div>
                            @endif

                            <!-- Entrada de Código de barra -->
                            <div class="flex gap-2">
                                <input type="text" 
                                       wire:model="nuevo_codigo_barra" 
                                       wire:keydown.enter.prevent="agregarCodigoBarra"
                                       placeholder="Escriba o escanee código de barra..." 
                                       class="flex-1 rounded-xl border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-800 text-slate-800 dark:text-white px-3 py-2 text-xs focus:ring-2 focus:ring-indigo-500 shadow-sm transition">
                                
                                <button type="button" 
                                        wire:click="agregarCodigoBarra" 
                                        class="inline-flex items-center justify-center p-2 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white rounded-xl shadow transition duration-200 shrink-0">
                                    <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                    </svg>
                                </button>
                            </div>
                            @error('nuevo_codigo_barra') <span class="text-xs text-rose-500 font-medium mt-1 block">{{ $message }}</span> @enderror
                        </div>

                    </div>
                </div>

                <!-- Botones del Modal -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-[#1d2745]/30">
                    <button type="button" 
                            @click="open = false"
                            wire:click="cerrarModal" 
                            class="px-5 py-2.5 text-sm font-bold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl transition">
                        Cancelar
                    </button>
                    
                    <button type="submit" 
                            class="px-5 py-2.5 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 rounded-xl shadow-md transition duration-200">
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
