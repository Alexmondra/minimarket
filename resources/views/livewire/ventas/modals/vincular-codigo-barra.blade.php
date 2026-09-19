{{-- Modal Vincular Código de Barra a Presentación --}}
@if ($showVincularCodigoModal)
    <div
        wire:click.self="cerrarVincularCodigoModal"
        class="fixed inset-0 flex items-center justify-center bg-slate-950/70 backdrop-blur-sm transition-all duration-200 p-4"
        style="z-index: 99999 !important;"
    >
        <div
            @click.stop
            class="max-w-xl w-full max-h-[90vh] flex flex-col rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 overflow-hidden"
        >
            {{-- HEADER --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-950/50 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-black uppercase tracking-wider text-slate-900 dark:text-white">Asignar Código de Barras</h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">El código escaneado coincide con un producto existente</p>
                    </div>
                </div>
                <button
                    type="button"
                    wire:click="cerrarVincularCodigoModal"
                    class="flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-red-500 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 transition"
                    title="Cerrar"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- BODY --}}
            <div class="p-6 overflow-y-auto space-y-4 flex-1">
                {{-- Banner informativo --}}
                <div class="p-4 rounded-xl bg-amber-500/10 border border-amber-500/20 text-xs text-amber-900 dark:text-amber-200 space-y-1">
                    <p class="font-bold flex items-center gap-1.5">
                        <span>Código escaneado:</span>
                        <span class="font-mono bg-amber-500/20 px-2 py-0.5 rounded text-amber-800 dark:text-amber-100 font-black tracking-wider text-sm">{{ $vincularCodigoBarra }}</span>
                    </p>
                    <p>
                        Este código estaba guardado en el producto <strong class="uppercase text-slate-900 dark:text-white">{{ $vincularProductoNombre }}</strong>. Selecciona a qué presentación corresponde para vincularlo correctamente y venderlo:
                    </p>
                </div>

                {{-- Lista de presentaciones existentes --}}
                <div class="space-y-2.5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400">
                        1. Vincular a una presentación existente:
                    </label>

                    @foreach ($vincularPresentaciones as $pres)
                        <div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/40 hover:border-blue-500/50 dark:hover:border-blue-500/50 transition flex items-center justify-between gap-3">
                            <div class="space-y-1 min-w-0 flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-bold text-sm text-slate-900 dark:text-white uppercase">{{ $pres['tipo_presentacion'] }}</span>
                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-blue-500/10 text-blue-600 dark:text-blue-400 uppercase">x{{ $pres['cantidad'] }} {{ $pres['unidad'] }}</span>
                                    @if ($pres['stock'] > 0)
                                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">Stock: {{ $pres['stock'] }} {{ $pres['unidad'] }}</span>
                                    @else
                                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-rose-500/10 text-rose-600 dark:text-rose-400">Sin stock</span>
                                    @endif
                                </div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-3">
                                    <span>Precio: <strong class="font-mono text-slate-800 dark:text-slate-200">S/ {{ number_format($pres['precio'], 2) }}</strong></span>
                                    <span>• Barra actual: <span class="font-mono text-[10px]">{{ $pres['barras_actuales'] }}</span></span>
                                </div>
                            </div>

                            <button
                                type="button"
                                wire:click="seleccionarPresentacionParaVincular({{ $pres['id'] }})"
                                class="shrink-0 px-3 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-sm hover:shadow transition flex items-center gap-1.5"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
                                </svg>
                                <span>Vincular y Vender</span>
                            </button>
                        </div>
                    @endforeach
                </div>

                {{-- Opción: Crear una nueva presentación para este producto --}}
                <div class="border-t border-slate-200 dark:border-slate-800 pt-4">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400">
                            2. ¿Es otra presentación distinta?
                        </label>
                        <button
                            type="button"
                            wire:click="toggleFormularioNuevaPresentacion"
                            class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1"
                        >
                            <span>{{ $mostrarFormularioNuevaPresentacion ? 'Ocultar formulario' : '+ Crear nueva presentación' }}</span>
                        </button>
                    </div>

                    @if ($mostrarFormularioNuevaPresentacion)
                        <div class="mt-3 p-4 rounded-xl border border-blue-200 dark:border-blue-900/50 bg-blue-50/50 dark:bg-blue-950/20 space-y-3">
                            <p class="text-xs text-blue-800 dark:text-blue-300">
                                Crea una nueva presentación para <strong>{{ $vincularProductoNombre }}</strong> y asígnale el código de barras <strong class="font-mono">{{ $vincularCodigoBarra }}</strong>:
                            </p>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div class="sm:col-span-2">
                                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
                                        Nombre de presentación *
                                    </label>
                                    <input
                                        type="text"
                                        wire:model="vincularNuevaPresentacionNombre"
                                        placeholder="Ej. Bolsaza 110g, Pack x6, etc."
                                        class="w-full text-xs rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    />
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
                                        Contenido (Unidades)
                                    </label>
                                    <input
                                        type="number"
                                        min="1"
                                        wire:model="vincularNuevaPresentacionCantidad"
                                        class="w-full text-xs rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    />
                                </div>
                            </div>
                            <div class="flex justify-end pt-1">
                                <button
                                    type="button"
                                    wire:click="crearYVincularNuevaPresentacion"
                                    class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm transition flex items-center gap-1.5"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                    </svg>
                                    <span>Crear Presentación y Vincular</span>
                                </button>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Opción: Crear un producto completamente nuevo --}}
                <div class="border-t border-slate-200 dark:border-slate-800 pt-4">
                    <div class="p-3.5 rounded-xl border border-purple-200 dark:border-purple-900/40 bg-purple-50/40 dark:bg-purple-950/20 flex items-center justify-between gap-3">
                        <div class="space-y-0.5 min-w-0 flex-1">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-purple-900 dark:text-purple-300">
                                3. ¿Es un producto totalmente diferente?
                            </h4>
                            <p class="text-[11px] text-slate-600 dark:text-slate-400">
                                Desvincula este código del producto anterior y abre el registro rápido para crearlo como un nuevo producto.
                            </p>
                        </div>

                        <button
                            type="button"
                            wire:click="crearNuevoProductoDesdeVincularModal"
                            class="shrink-0 px-3.5 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold shadow-sm hover:shadow transition flex items-center gap-1.5"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            <span>Crear Nuevo Producto</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- FOOTER --}}
            <div class="px-6 py-3 border-t border-slate-200 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-950/50 flex items-center justify-between gap-3 shrink-0">
                <button
                    type="button"
                    wire:click="desvincularCodigoSinAsignar"
                    class="text-xs font-bold text-amber-600 hover:text-amber-700 dark:text-amber-400 hover:underline transition"
                    title="Si este código no pertenece a ninguna presentación de este producto"
                >
                    No pertenece a este producto (Liberar código)
                </button>

                <button
                    type="button"
                    wire:click="cerrarVincularCodigoModal"
                    class="px-4 py-2 text-xs font-bold rounded-xl border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                >
                    Cancelar
                </button>
            </div>
        </div>
    </div>
@endif
