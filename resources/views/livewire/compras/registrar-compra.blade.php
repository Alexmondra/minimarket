<div class="space-y-6">
    <!-- Título y Stepper -->
    <div>
        <h1 class="text-xl font-semibold text-gray-900 dark:text-white">
            Registrar Compra
        </h1>
        <div class="mt-4 flex items-center gap-3">
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-semibold {{ $paso === 1 ? 'bg-primary-600 text-white shadow-sm' : ($paso > 1 ? 'bg-success-500 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-400') }}">
                    @if($paso > 1)
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    @else
                        1
                    @endif
                </span>
                <span class="text-xs {{ $paso === 1 ? 'text-primary-600 font-medium' : ($paso > 1 ? 'text-gray-400' : 'text-gray-500') }}">Cabecera</span>
            </div>
            <div class="w-12 h-px bg-gray-300 dark:bg-gray-600"></div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-semibold {{ $paso === 2 ? 'bg-primary-600 text-white shadow-sm' : 'bg-gray-200 dark:bg-gray-700 text-gray-400' }}">
                    2
                </span>
                <span class="text-xs {{ $paso === 2 ? 'text-primary-600 font-medium' : 'text-gray-500' }}">Detalle y Resumen</span>
            </div>
        </div>
    </div>

    @if ($paso === 1)
        {{-- PASO 1: CABECERA --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                <h2 class="text-sm font-medium text-gray-900 dark:text-white">Datos de la Compra</h2>
            </div>
            <div class="p-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Proveedor -->
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">Proveedor <span class="text-red-400">*</span></label>
                        <div class="relative">
                            <input type="text" wire:model.live.debounce.300ms="searchProveedor" 
                                   placeholder="Buscar proveedor por nombre, RUC..."
                                   class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-xs text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                            @if($showProveedorDropdown && count($proveedoresResultados) > 0)
                                <div class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-md shadow-lg max-h-48 overflow-y-auto">
                                    @foreach($proveedoresResultados as $prov)
                                        <button type="button" 
                                                wire:click="seleccionarProveedor({{ $prov['id'] }}, '{{ $prov['nombre'] }}')"
                                                class="w-full text-left px-3 py-2 text-xs hover:bg-gray-50 dark:hover:bg-gray-600 border-b border-gray-100 dark:border-gray-600 last:border-0">
                                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $prov['nombre'] }}</span>
                                            <span class="text-gray-400 ml-2">{{ $prov['numero_documento'] }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        @error('proveedorId') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <!-- Sucursal -->
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">Sucursal <span class="text-red-400">*</span></label>
                        @if($this->sucursalBloqueada)
                            <div class="w-full rounded-md border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3 py-2 text-xs font-medium text-gray-700 dark:text-gray-200">
                                {{ $this->sucursalActivaNombre ?? 'Sucursal activa' }}
                            </div>
                        @else
                            <select wire:model="sucursalId"
                                    class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-xs text-gray-900 dark:text-gray-100 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                                <option value="">Seleccionar sucursal</option>
                                @foreach($this->sucursales as $suc)
                                    <option value="{{ $suc->id }}">{{ $suc->nombre_sucursal }}</option>
                                @endforeach
                            </select>
                        @endif
                        @error('sucursalId') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <!-- Tipo Comprobante -->
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">Tipo Comprobante <span class="text-red-400">*</span></label>
                        <select wire:model="tipoComprobante"
                                class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-xs text-gray-900 dark:text-gray-100 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                            <option value="factura">Factura</option>
                            <option value="boleta">Boleta</option>
                            <option value="nota_credito">Nota de Crédito</option>
                            <option value="nota_debito">Nota de Débito</option>
                        </select>
                    </div>

                    <!-- N° Factura Proveedor -->
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">N° Factura Proveedor</label>
                        <input type="text" wire:model="numeroFactura"
                               placeholder="Opcional"
                               class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-xs text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                    </div>

                    <!-- Fecha Recepción -->
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">Fecha Recepción <span class="text-red-400">*</span></label>
                        <input type="date" wire:model="fechaRecepcion"
                               class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-xs text-gray-900 dark:text-gray-100 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                        @error('fechaRecepcion') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <!-- Archivo Comprobante -->
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Comprobante <span class="text-gray-400 font-normal">(PDF o Imagen)</span>
                        </label>
                        <input type="file" 
                               wire:model="archivoComprobante"
                               accept=".pdf,.jpg,.jpeg,.png,.gif"
                               class="block w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 dark:file:bg-primary-900/30 dark:file:text-primary-300 file:cursor-pointer cursor-pointer transition-all">
                        <div class="mt-1.5 flex items-center gap-2">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                            <span class="text-[10px] text-gray-400 dark:text-gray-500">PDF, JPG, PNG, GIF — Máx 10MB</span>
                        </div>
                        @error('archivoComprobante') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <!-- Observaciones -->
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">Observaciones</label>
                        <textarea wire:model="observaciones" rows="2"
                                  placeholder="Opcional"
                                  class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-xs text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:border-primary-500 focus:ring-1 focus:ring-primary-500"></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="button" 
                    wire:click="guardarCabecera"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 px-5 py-2 bg-primary-600 hover:bg-primary-700 text-white text-xs font-medium rounded-md transition-colors disabled:opacity-50">
                <span wire:loading.remove wire:target="guardarCabecera">Guardar y continuar</span>
                <span wire:loading wire:target="guardarCabecera">
                    <svg class="animate-spin h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    Guardando...
                </span>
            </button>
        </div>

    @elseif ($paso === 2)
        {{-- PASO 2: DETALLE Y RESUMEN --}}

        <!-- Botón Volver -->
        <div>
            <button wire:click="irPaso1" class="inline-flex items-center gap-1 text-xs text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Volver a cabecera
            </button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            {{-- Columna izquierda: Formulario de detalle (2/3) --}}
            <div class="lg:col-span-2 space-y-5">
                @livewire('compras.components.detalle-compra', ['compraId' => $compraId, 'sucursalId' => $sucursalId], key('detalle-' . $compraId))
            </div>

            {{-- Columna derecha: Resumen (1/3) --}}
            <div class="space-y-4">
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                    <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                        <h3 class="text-xs font-semibold text-gray-900 dark:text-white uppercase tracking-wide">Resumen</h3>
                    </div>
                    <div class="p-4">
                        <div class="space-y-2 text-xs">
                            <div class="flex justify-between">
                                <span class="text-gray-400">Total recibido</span>
                                <span class="font-medium text-gray-900 dark:text-gray-100">{{ number_format($totalUnidades, 0) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-400">Total declarado en lotes</span>
                                <span class="font-medium text-gray-900 dark:text-gray-100">S/ {{ number_format($subtotalCompra, 2) }}</span>
                            </div>
                            @if($impuestoPorcentaje > 0)
                                <div class="flex justify-between">
                                    <span class="text-gray-400">IGV ({{ $impuestoPorcentaje }}%)</span>
                                    <span class="font-medium text-gray-900 dark:text-gray-100">S/ {{ number_format($totalImpuesto, 2) }}</span>
                                </div>
                            @endif
                            <hr class="border-gray-100 dark:border-gray-700">
                            <div class="flex justify-between text-sm font-semibold">
                                <span class="text-gray-700 dark:text-gray-300">Total compra</span>
                                <span class="text-primary-600">S/ {{ number_format($totalFinal, 2) }}</span>
                            </div>
                        </div>

                        @if(count($detalles) > 0)
                            <button type="button"
                                    wire:click="finalizarCompra"
                                    wire:confirm="¿Estás seguro de finalizar la compra? Los detalles se guardarán definitivamente."
                                    class="mt-4 w-full inline-flex items-center justify-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-xs font-medium rounded-md transition-colors">
                                Finalizar Compra
                            </button>
                        @else
                            <p class="mt-4 text-xs text-gray-300 dark:text-gray-500 text-center">Agrega detalles para poder finalizar</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Modales con z-index alto para estar sobre el layout de Filament --}}
        <div class="relative z-[9999]">
            @livewire('compras.components.modal-crear-producto')
        </div>
    @endif
</div>
