<div>
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-lg w-full mx-4 max-h-[85vh] overflow-y-auto">
                {{-- Header --}}
                <div class="flex justify-between items-center px-5 py-3 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white">Crear Nuevo Producto</h3>
                    <button wire:click="cerrar" class="text-gray-400 hover:text-gray-600 text-lg leading-none">&times;</button>
                </div>

                {{-- Body --}}
                <div class="p-5 space-y-4">
                    <h4 class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Datos del Producto</h4>

                    {{-- Nombre --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Nombre <span class="text-red-400">*</span></label>
                        <input type="text" wire:model="nombre"
                               placeholder="Nombre del producto"
                               class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-xs text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                        @error('nombre') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>

                    {{-- Categoría --}}
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <label class="text-xs font-medium text-gray-700 dark:text-gray-300">Categoría</label>
                            <button type="button" wire:click="toggleCrearCategoria" class="text-xs text-primary-600 hover:text-primary-700">
                                @if($showCrearCategoria) Usar existente @else + Nueva @endif
                            </button>
                        </div>
                        @if($showCrearCategoria)
                            <input type="text" wire:model="nuevaCategoria"
                                   placeholder="Nombre de la nueva categoría"
                                   class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-xs text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                        @else
                            <select wire:model="categoriaId"
                                    class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-xs text-gray-900 dark:text-gray-100 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                                <option value="">Sin categoría</option>
                                @foreach($this->categorias as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                                @endforeach
                            </select>
                        @endif
                    </div>

                    {{-- Marca --}}
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <label class="text-xs font-medium text-gray-700 dark:text-gray-300">Marca</label>
                            <button type="button" wire:click="toggleCrearMarca" class="text-xs text-primary-600 hover:text-primary-700">
                                @if($showCrearMarca) Usar existente @else + Nueva @endif
                            </button>
                        </div>
                        @if($showCrearMarca)
                            <input type="text" wire:model="nuevaMarca"
                                   placeholder="Nombre de la nueva marca"
                                   class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-xs text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                        @else
                            <select wire:model="marcaId"
                                    class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-xs text-gray-900 dark:text-gray-100 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                                <option value="">Sin marca</option>
                                @foreach($this->marcas as $marca)
                                    <option value="{{ $marca->id }}">{{ $marca->nombre }}</option>
                                @endforeach
                            </select>
                        @endif
                    </div>

                    {{-- Código interno --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Código Interno</label>
                        <input type="text" wire:model="codigoInterno"
                               placeholder="Auto-generado si se deja vacío"
                               class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-xs text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                    </div>

                    {{-- Afecto IGV --}}
                    <div class="flex items-center gap-2">
                        <input type="checkbox" wire:model="afectoIgv" id="afectoIgv"
                               class="rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500">
                        <label for="afectoIgv" class="text-xs text-gray-700 dark:text-gray-300">Afecto a IGV</label>
                    </div>

                    <hr class="border-gray-200 dark:border-gray-600">

                    <h4 class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Presentación Básica</h4>

                    {{-- Unidad de medida --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Unidad de Medida <span class="text-red-400">*</span></label>
                        <select wire:model="unidadMedidaId"
                                class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-xs text-gray-900 dark:text-gray-100 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                            <option value="">Seleccionar</option>
                            @foreach($this->unidadesMedida as $um)
                                <option value="{{ $um->id }}">{{ $um->nombre }} ({{ $um->abreviatura }})</option>
                            @endforeach
                        </select>
                        @error('unidadMedidaId') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>

                    {{-- Tipo presentación --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Tipo de Presentación <span class="text-red-400">*</span></label>
                        <input type="text" wire:model="tipoPresentacion"
                               placeholder="Ej: Caja, Bolsa, Unidad, Pack"
                               class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-xs text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                        @error('tipoPresentacion') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>

                    {{-- Cantidad por empaque --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Cantidad por Empaque</label>
                        <input type="number" wire:model="cantidadPorEmpaque" min="1"
                               class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-xs text-gray-900 dark:text-gray-100 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                        <p class="text-xs text-gray-400 mt-1">Ej: 12 si es una caja de 12 unidades. 1 si es unidad suelta.</p>
                    </div>

                    {{-- Código barra --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Código de Barra</label>
                        <input type="text" wire:model="codigoBarra"
                               placeholder="Opcional"
                               class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-xs text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex justify-end gap-2 px-5 py-3 border-t border-gray-100 dark:border-gray-700">
                    <button type="button" wire:click="cerrar"
                            class="px-4 py-1.5 text-xs text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md transition-colors">
                        Cancelar
                    </button>
                    <button type="button" wire:click="crearProducto" wire:loading.attr="disabled"
                            class="px-4 py-1.5 text-xs bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-md disabled:opacity-50 transition-colors">
                        <span wire:loading.remove.delay.200ms wire:target="crearProducto">Crear Producto</span>
                        <span wire:loading.delay.200ms wire:target="crearProducto">Creando...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
