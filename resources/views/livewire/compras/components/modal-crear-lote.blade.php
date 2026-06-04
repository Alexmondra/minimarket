<div>
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-lg w-full mx-4 max-h-[85vh] overflow-y-auto">
                {{-- Header --}}
                <div class="flex justify-between items-center px-5 py-3 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white">Crear Nuevo Lote</h3>
                    <button wire:click="cerrar" class="text-gray-400 hover:text-gray-600 text-lg leading-none">&times;</button>
                </div>

                {{-- Body --}}
                <div class="p-5 space-y-4">
                    {{-- Producto --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Producto <span class="text-red-400">*</span></label>
                        <div class="relative">
                            <input type="text" 
                                   wire:model.live.debounce.300ms="searchProducto" 
                                   placeholder="Buscar producto..."
                                   class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-xs text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                            @if($showProductoDropdown && count($productosResultados) > 0)
                                <div class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-md shadow-lg max-h-40 overflow-y-auto">
                                    @foreach($productosResultados as $prod)
                                        <button type="button" 
                                                wire:click="seleccionarProducto({{ $prod['id'] }}, @js($prod['nombre']))"
                                                class="w-full text-left px-3 py-2 text-xs hover:bg-gray-50 dark:hover:bg-gray-600 border-b border-gray-100 dark:border-gray-600 last:border-0">
                                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $prod['nombre'] }}</span>
                                            <span class="text-gray-400 ml-2">({{ $prod['codigo'] }})</span>
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        @error('productoId') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>

                    {{-- Presentación --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Presentación <span class="text-red-400">*</span></label>
                        @if($productoId && $productoNombre)
                            @php
                                $producto = \App\Models\Producto::find($productoId);
                                $presentaciones = $producto?->presentaciones()->with('unidadMedida')->get() ?? collect();
                            @endphp
                            <select wire:change="actualizarPresentacion($event.target.value)"
                                    class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-xs text-gray-900 dark:text-gray-100 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                                <option value="">Seleccionar presentación</option>
                                @foreach($presentaciones as $pres)
                                    <option value="{{ $pres->id }}">
                                        {{ $pres->tipo_presentacion }} x {{ $pres->cantidad }} {{ $pres->unidadMedida?->abreviatura ?? 'und' }}
                                    </option>
                                @endforeach
                            </select>
                        @else
                            <p class="text-xs text-gray-400">Selecciona un producto primero</p>
                        @endif
                        @error('productoPresentacionId') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>

                    {{-- Código lote --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Código de Lote <span class="text-red-400">*</span></label>
                        <input type="text" wire:model="codigoLote"
                               placeholder="Ej: LOTE-2026-001"
                               class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-xs text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                        @error('codigoLote') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>

                    {{-- Fechas --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha Fabricación</label>
                            <input type="date" wire:model="fechaFabricacion"
                                   class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-xs text-gray-900 dark:text-gray-100 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha Vencimiento</label>
                            <input type="date" wire:model="fechaVencimiento"
                                   class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-xs text-gray-900 dark:text-gray-100 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                            @error('fechaVencimiento') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Ubicación --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Ubicación</label>
                        <input type="text" wire:model="ubicacion"
                               placeholder="Ej: Estante A1"
                               class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-xs text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                    </div>

                    {{-- Precio compra --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Precio de Compra (S/)</label>
                        <input type="number" step="0.01" wire:model="precioCompra"
                               class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-xs text-gray-900 dark:text-gray-100 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                    </div>

                    {{-- Observaciones --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Observaciones</label>
                        <textarea wire:model="observaciones" rows="2"
                                  class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-xs text-gray-900 dark:text-gray-100 focus:border-primary-500 focus:ring-1 focus:ring-primary-500"></textarea>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex justify-end gap-2 px-5 py-3 border-t border-gray-100 dark:border-gray-700">
                    <button type="button" wire:click="cerrar"
                            class="px-4 py-1.5 text-xs text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md transition-colors">
                        Cancelar
                    </button>
                    <button type="button" wire:click="crearLote" wire:loading.attr="disabled"
                            class="px-4 py-1.5 text-xs bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-md disabled:opacity-50 transition-colors">
                        <span wire:loading.remove.delay.200ms wire:target="crearLote">Crear Lote</span>
                        <span wire:loading.delay.200ms wire:target="crearLote">Creando...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
