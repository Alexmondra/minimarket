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
    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">
        Proveedor <span class="text-red-400">*</span>
    </label>

    <div class="flex items-center gap-2">

        <div class="relative flex-1">

            <input
                type="text"
                wire:model.live.debounce.300ms="searchProveedor"

                @focus="$wire.set('showProveedorDropdown', true)"

                @keydown.arrow-down.prevent="
                    const first = $el.closest('.relative').querySelector('.dropdown-item');
                    if (first) first.focus();
                "

                @keydown.enter.prevent="
                    const results = $el.closest('.relative').querySelectorAll('.dropdown-item');

                    if (results.length === 1) {
                        results[0].click();
                    } else if (results.length > 0) {
                        results[0].focus();
                    }
                "

                @keydown.escape="$wire.set('showProveedorDropdown', false)"

                placeholder="Buscar proveedor por nombre, RUC..."

                class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-xs text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-500"
            >

            @if($showProveedorDropdown && count($proveedoresResultados) > 0)

                <div class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg shadow-xl max-h-48 overflow-y-auto p-1">

                    @foreach($proveedoresResultados as $index => $prov)

                        <button
                            type="button"
                            tabindex="0"

                            wire:key="prov-btn-{{ $prov['id'] }}"

                            wire:click="seleccionarProveedor(
                                {{ $prov['id'] }},
                                '{{ $prov['nombre'] }}'
                            )"

                            @keydown.arrow-down.prevent="
                                const next = $el.nextElementSibling;

                                if (
                                    next &&
                                    next.classList.contains('dropdown-item')
                                ) {
                                    next.focus();
                                }
                            "

                            @keydown.arrow-up.prevent="
                                const prev = $el.previousElementSibling;

                                if (
                                    prev &&
                                    prev.classList.contains('dropdown-item')
                                ) {
                                    prev.focus();
                                } else {
                                    const input = $el.closest('.relative').querySelector('input');

                                    if (input) input.focus();
                                }
                            "

                            @keydown.enter.prevent="$el.click()"

                            @keydown.escape="
                                const input = $el.closest('.relative').querySelector('input');

                                if (input) {
                                    input.focus();
                                    $wire.set('showProveedorDropdown', false);
                                }
                            "

                            class="group dropdown-item w-full text-left rounded-lg px-3 py-2 text-xs
                                   transition-all duration-150

                                   hover:bg-blue-500
                                   hover:text-white

                                   focus:bg-blue-600
                                   focus:text-white
                                   focus:outline-none
                                   focus:ring-2
                                   focus:ring-blue-300
                                   focus:shadow-lg
                                   focus:shadow-blue-500/30
                                   focus:scale-[1.01]"
                        >

                            <div class="flex flex-col">

                                <span
                                    class="font-medium text-gray-900 dark:text-gray-100
                                           group-hover:text-white
                                           group-focus:text-white
                                           group-focus:font-semibold"
                                >
                                    {{ $prov['nombre'] }}
                                </span>

                                <span
                                    class="text-[11px] text-gray-400
                                           group-hover:text-blue-100
                                           group-focus:text-blue-100"
                                >
                                    {{ $prov['numero_documento'] }}
                                </span>

                            </div>

                        </button>

                    @endforeach

                </div>

            @endif

        </div>

        <button
            type="button"
            wire:click="abrirRegistrarProveedorModal"
            class="inline-flex items-center justify-center p-2 rounded-md bg-primary-600 hover:bg-primary-700 text-white transition-colors h-[34px] w-[34px]"
            title="Agregar Proveedor"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 4v16m8-8H4"
                />
            </svg>
        </button>

    </div>

    @error('proveedorId')
        <p class="mt-1 text-xs text-red-400">
            {{ $message }}
        </p>
    @enderror
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

    {{-- Modal para registrar nuevo proveedor --}}
    @if($showRegistrarProveedorModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-lg w-full mx-4 max-h-[85vh] overflow-y-auto">
                {{-- Header --}}
                <div class="flex justify-between items-center px-5 py-3 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white">Registrar Nuevo Proveedor</h3>
                    <button type="button" wire:click="$set('showRegistrarProveedorModal', false)" class="text-gray-400 hover:text-gray-600 text-lg leading-none">&times;</button>
                </div>

                {{-- Body --}}
                <div class="p-5 space-y-4">
                    <h4 class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Datos de Identificación</h4>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Tipo de Documento --}}
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Tipo de Documento <span class="text-red-400">*</span></label>
                            <select wire:model="nuevoProveedorTipoDocumento"
                                    class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-xs text-gray-900 dark:text-gray-100 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                                <option value="RUC">RUC</option>
                                <option value="DNI">DNI</option>
                                <option value="CE">CE</option>
                                <option value="OTRO">OTRO</option>
                            </select>
                            @error('nuevoProveedorTipoDocumento') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>

                        {{-- Nro de Documento --}}
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Número de Documento <span class="text-red-400">*</span></label>
                            <div class="flex gap-2">
                                <input type="text" wire:model="nuevoProveedorDocumento"
                                       placeholder="Documento"
                                       class="flex-1 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-xs text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                                @if($nuevoProveedorTipoDocumento === 'DNI' || $nuevoProveedorTipoDocumento === 'RUC')
                                    <button type="button" wire:click="buscarNuevoProveedor" wire:loading.attr="disabled"
                                            class="px-3 py-2 bg-primary-600 hover:bg-primary-700 text-white text-xs font-medium rounded-md disabled:opacity-50 transition-colors flex items-center justify-center">
                                        <svg wire:loading.remove wire:target="buscarNuevoProveedor" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                        <svg wire:loading wire:target="buscarNuevoProveedor" class="animate-spin h-3.5 w-3.5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    </button>
                                @endif
                            </div>
                            @error('nuevoProveedorDocumento') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Nombre Comercial --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Nombre Comercial / Nombre <span class="text-red-400">*</span></label>
                        <input type="text" wire:model="nuevoProveedorNombre"
                               placeholder="Nombre del proveedor o comercial"
                               class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-xs text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                        @error('nuevoProveedorNombre') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>

                    {{-- Razón Social --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Razón Social</label>
                        <input type="text" wire:model="nuevoProveedorRazonSocial"
                               placeholder="Razón Social (Opcional)"
                               class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-xs text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                        @error('nuevoProveedorRazonSocial') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <h4 class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide pt-2">Información de Contacto</h4>

                    {{-- Dirección --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Dirección</label>
                        <input type="text" wire:model="nuevoProveedorDireccion"
                               placeholder="Dirección fiscal o comercial"
                               class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-xs text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                        @error('nuevoProveedorDireccion') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Teléfono --}}
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Teléfono</label>
                            <input type="text" wire:model="nuevoProveedorTelefono"
                                   placeholder="Teléfono"
                                   class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-xs text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                            @error('nuevoProveedorTelefono') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>

                        {{-- Email --}}
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                            <input type="email" wire:model="nuevoProveedorEmail"
                                   placeholder="Email"
                                   class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-xs text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                            @error('nuevoProveedorEmail') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Contacto Principal --}}
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Contacto Principal</label>
                            <input type="text" wire:model="nuevoProveedorContactoPrincipal"
                                   placeholder="Nombre del contacto"
                                   class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-xs text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                            @error('nuevoProveedorContactoPrincipal') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>

                        {{-- Teléfono Contacto --}}
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Teléfono Contacto</label>
                            <input type="text" wire:model="nuevoProveedorTelefonoContacto"
                                   placeholder="Teléfono del contacto"
                                   class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-xs text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                            @error('nuevoProveedorTelefonoContacto') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Rubro --}}
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Rubro</label>
                            <input type="text" wire:model="nuevoProveedorRubro"
                                   placeholder="Ej: Abarrotes, Bebidas"
                                   class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-xs text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                            @error('nuevoProveedorRubro') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>

                        {{-- Observaciones --}}
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Observaciones</label>
                            <textarea wire:model="nuevoProveedorObservaciones" rows="1"
                                      placeholder="Observaciones"
                                      class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-xs text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:border-primary-500 focus:ring-1 focus:ring-primary-500"></textarea>
                            @error('nuevoProveedorObservaciones') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex justify-end gap-2 px-5 py-3 border-t border-gray-100 dark:border-gray-700">
                    <button type="button" wire:click="$set('showRegistrarProveedorModal', false)"
                            class="px-4 py-1.5 text-xs text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md transition-colors">
                        Cancelar
                    </button>
                    <button type="button" wire:click="registrarProveedorManual" wire:loading.attr="disabled"
                            class="px-4 py-1.5 text-xs bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-md disabled:opacity-50 transition-colors">
                        <span wire:loading.remove wire:target="registrarProveedorManual">Registrar y Seleccionar</span>
                        <span wire:loading wire:target="registrarProveedorManual">Registrando...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
