<div class="space-y-6 animate-fade-in">

    {{-- Premium Header + Stepper --}}
    <div class="relative overflow-hidden rounded-2xl border border-slate-200/70 dark:border-slate-800/40 bg-gradient-to-br from-white to-slate-50/50 dark:from-slate-900/50 dark:to-slate-950/10 shadow-sm">
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary-400 via-primary-500 to-primary-400"></div>
        <div class="p-5 sm:p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary-100 dark:bg-primary-500/15 text-primary-600 dark:text-primary-400 ring-1 ring-primary-300/50 dark:ring-primary-600/30">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-lg font-extrabold text-slate-900 dark:text-white tracking-tight">Registrar Compra</h1>
                        <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Ingresa los datos de la compra y los productos recibidos</p>
                    </div>
                </div>

                <div class="flex items-center gap-4 p-2.5 bg-slate-50/70 dark:bg-slate-900/60 rounded-xl border border-slate-200/60 dark:border-slate-800/40">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-xs font-extrabold transition-all duration-300 {{ $paso === 1 ? 'bg-primary-500 text-white shadow-md shadow-primary-500/20 ring-2 ring-primary-300/50' : ($paso > 1 ? 'bg-emerald-500 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-400') }}">
                            @if($paso > 1)
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            @else
                                <span>1</span>
                            @endif
                        </span>
                        <span class="text-xs font-bold {{ $paso === 1 ? 'text-primary-700 dark:text-primary-400' : ($paso > 1 ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400') }}">Cabecera</span>
                    </div>
                    <div class="w-16 h-0.5 rounded-full bg-gradient-to-r from-primary-400 to-primary-600 dark:from-primary-500 dark:to-primary-700"></div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-xs font-extrabold transition-all duration-300 {{ $paso === 2 ? 'bg-primary-500 text-white shadow-md shadow-primary-500/20 ring-2 ring-primary-300/50' : 'bg-slate-200 dark:bg-slate-700 text-slate-400' }}">
                            <span>2</span>
                        </span>
                        <span class="text-xs font-bold {{ $paso === 2 ? 'text-primary-700 dark:text-primary-400' : 'text-slate-400' }}">Detalle y Resumen</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($paso === 1)
        {{-- PASO 1: CABECERA --}}
        <div class="relative overflow-hidden rounded-2xl border border-slate-200/70 dark:border-slate-800/40 bg-gradient-to-br from-white to-slate-50/30 dark:from-slate-900/50 dark:to-slate-950/10 shadow-sm">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary-400 via-primary-500 to-primary-400"></div>
            <div class="p-5 sm:p-6">
                <div class="flex items-center gap-3 mb-6">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary-100 dark:bg-primary-500/15 text-primary-600 dark:text-primary-400 ring-1 ring-primary-300/50">
                        <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-extrabold text-slate-800 dark:text-white">Datos de la Compra</h2>
                        <p class="text-[10px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Información general de la transacción</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    {{-- Proveedor --}}
                    <div class="md:col-span-2">
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">🚛 Proveedor <span class="text-rose-500">*</span></label>
                        <div class="flex items-center gap-2">
                            <div class="relative flex-1">
                                <input type="text"
                                    wire:model.live.debounce.300ms="searchProveedor"
                                    @focus="$wire.set('showProveedorDropdown', true)"
                                    @keydown.arrow-down.prevent="const first = $el.closest('.relative').querySelector('.dropdown-item'); if (first) first.focus();"
                                    @keydown.enter.prevent="const results = $el.closest('.relative').querySelectorAll('.dropdown-item'); if (results.length === 1) { results[0].click(); } else if (results.length > 0) { results[0].focus(); }"
                                    @keydown.escape="$wire.set('showProveedorDropdown', false)"
                                    placeholder="Buscar proveedor por nombre, RUC..."
                                    class="w-full px-4 py-2.5 rounded-xl border text-sm transition-all duration-200 shadow-sm border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-700/80 text-slate-800 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 dark:focus:ring-primary-500/10 dark:focus:border-primary-400">

                                @if($showProveedorDropdown && count($proveedoresResultados) > 0)
                                    <div class="absolute z-50 mt-1.5 w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl shadow-slate-900/10 max-h-56 overflow-y-auto p-1.5">
                                        @foreach($proveedoresResultados as $index => $prov)
                                            <button type="button" tabindex="0" wire:key="prov-btn-{{ $prov['id'] }}"
                                                wire:click="seleccionarProveedor({{ $prov['id'] }}, '{{ $prov['nombre'] }}')"
                                                @keydown.arrow-down.prevent="const next = $el.nextElementSibling; if (next && next.classList.contains('dropdown-item')) next.focus();"
                                                @keydown.arrow-up.prevent="const prev = $el.previousElementSibling; if (prev && prev.classList.contains('dropdown-item')) { prev.focus(); } else { const input = $el.closest('.relative').querySelector('input'); if (input) input.focus(); }"
                                                @keydown.enter.prevent="$el.click()"
                                                @keydown.escape="const input = $el.closest('.relative').querySelector('input'); if (input) { input.focus(); $wire.set('showProveedorDropdown', false); }"
                                                class="group dropdown-item w-full text-left rounded-lg px-3 py-2.5 text-xs transition-all duration-150 hover:bg-primary-50 hover:text-primary-900 focus:bg-primary-50 focus:text-primary-900 focus:outline-none focus:ring-2 focus:ring-primary-300 dark:hover:bg-primary-500/10 dark:focus:bg-primary-500/10">
                                                <div class="flex items-center gap-2.5">
                                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-500 group-hover:bg-primary-100 group-hover:text-primary-600 dark:group-hover:bg-primary-500/20 dark:group-hover:text-primary-400 transition-colors">
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                                                        </svg>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <span class="font-semibold text-slate-900 dark:text-slate-100 group-hover:text-primary-700 dark:group-hover:text-primary-300">{{ $prov['nombre'] }}</span>
                                                        <span class="block text-[10px] text-slate-400 dark:text-slate-500 group-hover:text-primary-400">{{ $prov['numero_documento'] }}</span>
                                                    </div>
                                                </div>
                                            </button>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <button type="button" wire:click="abrirRegistrarProveedorModal"
                                class="inline-flex items-center justify-center p-2.5 rounded-xl bg-primary-500 hover:bg-primary-600 text-white transition-all shadow-sm h-[38px] w-[38px]"
                                title="Agregar Proveedor">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </button>
                        </div>
                        @error('proveedorId') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Sucursal --}}
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">📍 Sucursal <span class="text-rose-500">*</span></label>
                        @if($this->sucursalBloqueada)
                            <div class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 dark:bg-slate-900/60 dark:border-slate-700/80 text-sm font-semibold text-slate-700 dark:text-slate-200">
                                {{ $this->sucursalActivaNombre ?? 'Sucursal activa' }}
                            </div>
                        @else
                            <select wire:model="sucursalId"
                                class="w-full px-4 py-2.5 rounded-xl border text-sm transition-all duration-200 shadow-sm border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-700/80 text-slate-800 dark:text-white focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 dark:focus:ring-primary-500/10 dark:focus:border-primary-400">
                                <option value="">Seleccionar sucursal</option>
                                @foreach($this->sucursales as $suc)
                                    <option value="{{ $suc->id }}">{{ $suc->nombre_sucursal }}</option>
                                @endforeach
                            </select>
                        @endif
                        @error('sucursalId') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Tipo Comprobante --}}
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">📄 Tipo Comprobante <span class="text-rose-500">*</span></label>
                        <select wire:model="tipoComprobante"
                            class="w-full px-4 py-2.5 rounded-xl border text-sm transition-all duration-200 shadow-sm border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-700/80 text-slate-800 dark:text-white focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 dark:focus:ring-primary-500/10 dark:focus:border-primary-400">
                            <option value="factura">Factura</option>
                            <option value="boleta">Boleta</option>
                            <option value="nota_credito">Nota de Crédito</option>
                            <option value="nota_debito">Nota de Débito</option>
                        </select>
                    </div>

                    {{-- N° Factura Proveedor --}}
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">🧾 N° Factura Proveedor</label>
                        <input type="text" wire:model="numeroFactura" placeholder="Opcional"
                            class="w-full px-4 py-2.5 rounded-xl border text-sm transition-all duration-200 shadow-sm border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-700/80 text-slate-800 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 dark:focus:ring-primary-500/10 dark:focus:border-primary-400">
                    </div>

                    {{-- Fecha Recepción --}}
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">📅 Fecha Recepción <span class="text-rose-500">*</span></label>
                        <input type="date" wire:model="fechaRecepcion"
                            class="w-full px-4 py-2.5 rounded-xl border text-sm transition-all duration-200 shadow-sm border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-700/80 text-slate-800 dark:text-white focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 dark:focus:ring-primary-500/10 dark:focus:border-primary-400">
                        @error('fechaRecepcion') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Archivo Comprobante --}}
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">📎 Comprobante</label>
                        <div class="relative rounded-xl border-2 border-dashed border-slate-300 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/30 hover:bg-slate-100/50 dark:hover:bg-slate-900/50 transition-all duration-200 p-4">
                            <input type="file" wire:model="archivoComprobante" accept=".pdf,.jpg,.jpeg,.png,.gif"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                            <div class="flex flex-col items-center gap-1.5 text-center">
                                <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                                <span class="text-[10px] font-semibold text-slate-500 dark:text-slate-400">PDF, JPG, PNG — Máx 10MB</span>
                            </div>
                        </div>
                        @error('archivoComprobante') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Observaciones --}}
                    <div class="md:col-span-2">
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">📝 Observaciones</label>
                        <textarea wire:model="observaciones" rows="2" placeholder="Opcional — notas adicionales sobre la compra..."
                            class="w-full px-4 py-2.5 rounded-xl border text-sm transition-all duration-200 shadow-sm border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-700/80 text-slate-800 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 dark:focus:ring-primary-500/10 dark:focus:border-primary-400"></textarea>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="px-5 sm:px-6 py-4 border-t border-slate-100 dark:border-slate-800/40 bg-slate-50/30 dark:bg-slate-950/10 flex justify-end">
                <button type="button" wire:click="guardarCabecera" wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-extrabold text-white bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-400 hover:to-primary-500 rounded-xl shadow-md shadow-primary-500/15 active:scale-95 transition-all disabled:opacity-50">
                    <span wire:loading.remove.delay.200ms wire:target="guardarCabecera">
                        Guardar y continuar
                        <svg class="w-4 h-4 inline -mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </span>
                    <span wire:loading.delay.200ms wire:target="guardarCabecera" class="inline-flex items-center gap-1.5">
                        <svg class="animate-spin h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Guardando...
                    </span>
                </button>
            </div>
        </div>

    @elseif ($paso === 2)
        {{-- PASO 2: DETALLE Y RESUMEN --}}

        {{-- Botón Volver --}}
        <button wire:click="irPaso1"
            class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Volver a cabecera
        </button>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-4">
            {{-- Columna izquierda: Detalle (2/3) --}}
            <div class="lg:col-span-2 space-y-5">
                @livewire('compras.components.detalle-compra', ['compraId' => $compraId, 'sucursalId' => $sucursalId], key('detalle-' . $compraId))
            </div>

            {{-- Columna derecha: Resumen (1/3) --}}
            <div class="space-y-5">
                <div class="relative overflow-hidden rounded-2xl border border-slate-200/70 dark:border-slate-800/40 bg-gradient-to-br from-white to-slate-50/50 dark:from-slate-900/50 dark:to-slate-950/10 shadow-sm">
                    <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary-400 via-primary-500 to-primary-400"></div>
                    <div class="p-5">
                        <div class="flex items-center gap-2.5 mb-5">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary-100 dark:bg-primary-500/15 text-primary-600 dark:text-primary-400">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5h16.5M5.25 7.5h13.5M5.25 15h13.5M5.25 12h13.5"/>
                                </svg>
                            </div>
                            <h3 class="text-xs font-extrabold text-slate-800 dark:text-white uppercase tracking-wider">Resumen</h3>
                        </div>

                        <div class="space-y-3">
                            <div class="flex justify-between items-center py-1.5">
                                <span class="text-xs text-slate-500 dark:text-slate-400">📦 Total unidades</span>
                                <span class="text-sm font-bold text-slate-800 dark:text-slate-200">{{ number_format($totalUnidades, 0) }}</span>
                            </div>
                            <div class="flex justify-between items-center py-1.5">
                                <span class="text-xs text-slate-500 dark:text-slate-400">💰 Subtotal lotes</span>
                                <span class="text-sm font-bold text-slate-800 dark:text-slate-200">S/ {{ number_format($subtotalCompra, 2) }}</span>
                            </div>
                            <div class="flex justify-between items-center py-1.5">
                                <span class="text-xs text-slate-500 dark:text-slate-400">🏷️ Productos diferentes</span>
                                <span class="text-sm font-bold text-slate-800 dark:text-slate-200">{{ $cantidadProductos }}</span>
                            </div>
                            <div class="my-3 border-t border-slate-200 dark:border-slate-700/60"></div>
                            <div class="flex justify-between items-center py-1">
                                <span class="text-sm font-extrabold text-slate-700 dark:text-slate-300">Total compra</span>
                                <span class="text-lg font-black text-primary-600 dark:text-primary-400">S/ {{ number_format($totalFinal, 2) }}</span>
                            </div>
                        </div>

                        @if(count($detalles) > 0)
                            <button type="button" wire:click="finalizarCompra"
                                wire:confirm="¿Estás seguro de finalizar la compra? Los detalles se guardarán definitivamente."
                                class="mt-5 w-full inline-flex items-center justify-center gap-2 px-5 py-3 text-sm font-extrabold text-white bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-400 hover:to-primary-500 rounded-xl shadow-lg shadow-primary-500/20 active:scale-[0.98] transition-all">
                                ✅ Finalizar Compra
                            </button>
                        @else
                            <div class="mt-5 p-4 rounded-xl bg-slate-50 dark:bg-slate-900/40 border border-slate-200 dark:border-slate-800/40 text-center">
                                <p class="text-xs font-semibold text-slate-400 dark:text-slate-500">Agrega productos al detalle para finalizar</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Modales --}}
        <div class="relative z-[9999]">
            @livewire('compras.components.modal-crear-producto')
        </div>
    @endif

    {{-- Modal Nuevo Proveedor --}}
    @if($showRegistrarProveedorModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
            wire:click.self="$set('showRegistrarProveedorModal', false)">
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 max-w-lg w-full max-h-[90vh] overflow-y-auto animate-fade-in"
                wire:click.self.stop>
                {{-- Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-800/40">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary-100 dark:bg-primary-500/15 text-primary-600 dark:text-primary-400">
                            <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-extrabold text-slate-900 dark:text-white">Nuevo Proveedor</h3>
                            <p class="text-[10px] text-slate-500 dark:text-slate-400 font-medium">Registra un proveedor rápido</p>
                        </div>
                    </div>
                    <button type="button" wire:click="$set('showRegistrarProveedorModal', false)"
                        class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Body --}}
                <div class="p-6 space-y-5">
                    <div>
                        <h4 class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-3">📋 Datos de Identificación</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Tipo Doc. <span class="text-rose-500">*</span></label>
                                <select wire:model="nuevoProveedorTipoDocumento"
                                    class="w-full px-3 py-2 rounded-xl border text-sm transition-all duration-200 shadow-sm border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-700/80 text-slate-800 dark:text-white focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                                    <option value="RUC">RUC</option>
                                    <option value="DNI">DNI</option>
                                    <option value="CE">CE</option>
                                    <option value="OTRO">OTRO</option>
                                </select>
                                @error('nuevoProveedorTipoDocumento') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">N° Documento <span class="text-rose-500">*</span></label>
                                <div class="flex gap-2">
                                    <input type="text" wire:model="nuevoProveedorDocumento" placeholder="Documento"
                                        class="flex-1 px-3 py-2 rounded-xl border text-sm transition-all duration-200 shadow-sm border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-700/80 text-slate-800 dark:text-white placeholder-slate-400 font-mono focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                                    @if($nuevoProveedorTipoDocumento === 'DNI' || $nuevoProveedorTipoDocumento === 'RUC')
                                        <button type="button" wire:click="buscarNuevoProveedor" wire:loading.attr="disabled"
                                            class="px-3 py-2 rounded-xl bg-primary-500 hover:bg-primary-600 text-white text-xs font-bold transition-all disabled:opacity-50 shadow-sm flex items-center justify-center">
                                            <svg wire:loading.remove.delay.200ms wire:target="buscarNuevoProveedor" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                            <svg wire:loading.delay.200ms wire:target="buscarNuevoProveedor" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        </button>
                                    @endif
                                </div>
                                @error('nuevoProveedorDocumento') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">🏢 Nombre / Razón Social <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model="nuevoProveedorNombre" placeholder="Nombre del proveedor"
                            class="w-full px-4 py-2 rounded-xl border text-sm transition-all duration-200 shadow-sm border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-700/80 text-slate-800 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                        @error('nuevoProveedorNombre') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Razón Social</label>
                            <input type="text" wire:model="nuevoProveedorRazonSocial" placeholder="Opcional"
                                class="w-full px-3 py-2 rounded-xl border text-sm transition-all duration-200 shadow-sm border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-700/80 text-slate-800 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                            @error('nuevoProveedorRazonSocial') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">🏷️ Rubro</label>
                            <input type="text" wire:model="nuevoProveedorRubro" placeholder="Ej: Abarrotes"
                                class="w-full px-3 py-2 rounded-xl border text-sm transition-all duration-200 shadow-sm border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-700/80 text-slate-800 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                            @error('nuevoProveedorRubro') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <h4 class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-3">📞 Contacto</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Teléfono</label>
                                <input type="text" wire:model="nuevoProveedorTelefono" placeholder="Teléfono"
                                    class="w-full px-3 py-2 rounded-xl border text-sm transition-all duration-200 shadow-sm border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-700/80 text-slate-800 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Email</label>
                                <input type="email" wire:model="nuevoProveedorEmail" placeholder="Email"
                                    class="w-full px-3 py-2 rounded-xl border text-sm transition-all duration-200 shadow-sm border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-700/80 text-slate-800 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Contacto Principal</label>
                                <input type="text" wire:model="nuevoProveedorContactoPrincipal" placeholder="Nombre"
                                    class="w-full px-3 py-2 rounded-xl border text-sm transition-all duration-200 shadow-sm border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-700/80 text-slate-800 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Tel. Contacto</label>
                                <input type="text" wire:model="nuevoProveedorTelefonoContacto" placeholder="Teléfono"
                                    class="w-full px-3 py-2 rounded-xl border text-sm transition-all duration-200 shadow-sm border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-700/80 text-slate-800 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">📍 Dirección</label>
                        <input type="text" wire:model="nuevoProveedorDireccion" placeholder="Dirección fiscal"
                            class="w-full px-4 py-2 rounded-xl border text-sm transition-all duration-200 shadow-sm border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-700/80 text-slate-800 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">📝 Observaciones</label>
                        <textarea wire:model="nuevoProveedorObservaciones" rows="1" placeholder="Opcional"
                            class="w-full px-4 py-2 rounded-xl border text-sm transition-all duration-200 shadow-sm border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-700/80 text-slate-800 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500"></textarea>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-slate-100 dark:border-slate-800/40 bg-slate-50/30 dark:bg-slate-950/10 rounded-b-2xl">
                    <button type="button" wire:click="$set('showRegistrarProveedorModal', false)"
                        class="px-5 py-2 text-xs font-bold text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl transition-all">
                        Cancelar
                    </button>
                    <button type="button" wire:click="registrarProveedorManual" wire:loading.attr="disabled"
                        class="px-5 py-2 text-xs font-extrabold text-white bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-400 hover:to-primary-500 rounded-xl shadow-md shadow-primary-500/15 disabled:opacity-50 transition-all">
                        <span wire:loading.remove.delay.200ms wire:target="registrarProveedorManual">🚛 Registrar y Seleccionar</span>
                        <span wire:loading.delay.200ms wire:target="registrarProveedorManual" class="inline-flex items-center gap-1.5">
                            <svg class="animate-spin h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Registrando...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
