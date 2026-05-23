<div>
    {{-- Modal de Ajuste de Stock --}}
    <div x-data="{ open: @entangle('showModal') }"
         x-show="open"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">
        {{-- Overlay --}}
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
             @click="open = false"
             x-show="open">
        </div>

        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div x-show="open"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full sm:mx-0 sm:h-10 sm:w-10"
                             style="background-color: {{ $tipoAjuste === 'entrada' ? '#dcfce7' : '#fee2e2' }};">
                            @if ($tipoAjuste === 'entrada')
                                <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                            @else
                                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                            @endif
                        </div>
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                            <h3 class="text-base font-semibold leading-6 text-gray-900">
                                {{ $tipoAjuste === 'entrada' ? 'Ajuste de Entrada' : 'Ajuste de Salida' }}
                            </h3>
                            <div class="mt-4 space-y-4">
                                {{-- Sucursal --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Sucursal</label>
                                    <select wire:model.live="sucursalId"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="">Seleccionar sucursal...</option>
                                        @foreach ($this->sucursales as $sucursal)
                                            <option value="{{ $sucursal->id }}">{{ $sucursal->nombre_sucursal }}</option>
                                        @endforeach
                                    </select>
                                    @error('sucursalId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                {{-- Búsqueda de Producto --}}
                                <div class="relative">
                                    <label class="block text-sm font-medium text-gray-700">Producto</label>
                                    <input type="text"
                                           wire:model.live="searchProducto"
                                           placeholder="Buscar producto..."
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    @error('productoId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror

                                    @if ($showProductoDropdown && count($productosResultados) > 0)
                                        <div class="absolute z-10 mt-1 w-full bg-white shadow-lg rounded-md border border-gray-200 max-h-48 overflow-y-auto">
                                            @foreach ($productosResultados as $producto)
                                                <button type="button"
                                                        wire:click="seleccionarProducto({{ $producto['id'] }}, @js($producto['nombre']))"
                                                        class="w-full text-left px-4 py-2 text-sm hover:bg-indigo-50 focus:bg-indigo-50">
                                                    {{ $producto['nombre'] }}
                                                    @if (!empty($producto['codigo_interno']))
                                                        <span class="text-gray-400 text-xs">({{ $producto['codigo_interno'] }})</span>
                                                    @endif
                                                </button>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                {{-- Presentación --}}
                                @if (count($presentaciones) > 0)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Presentación</label>
                                        <select wire:model.live="presentacionId"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            <option value="">Seleccionar presentación...</option>
                                            @foreach ($presentaciones as $pres)
                                                <option value="{{ $pres['id'] }}">
                                                    {{ $pres['tipo_presentacion'] ?? 'Presentación' }}
                                                    @if (isset($pres['unidad_medida']['abreviatura']))
                                                        ({{ $pres['unidad_medida']['abreviatura'] }})
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('presentacionId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                @endif

                                @if (count($lotesDisponibles) > 0)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Lote</label>
                                        <select wire:model.live="lotePresentacionId"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            <option value="">Seleccionar lote...</option>
                                            @foreach ($lotesDisponibles as $lote)
                                                <option value="{{ $lote['id'] }}">
                                                    {{ $lote['codigo_lote'] }} · stock {{ $lote['stock'] }}
                                                    @if ($lote['vencimiento'])
                                                        · vence {{ $lote['vencimiento'] }}
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('lotePresentacionId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                @endif

                                {{-- Cantidad --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">
                                        Cantidad a {{ $tipoAjuste === 'entrada' ? 'ingresar' : 'retirar' }}
                                    </label>
                                    <input type="number"
                                           wire:model.live="cantidad"
                                           min="1"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    @error('cantidad') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                {{-- Motivo --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Motivo del ajuste</label>
                                    <textarea wire:model.live="motivo"
                                              rows="2"
                                              placeholder="Describa el motivo del ajuste..."
                                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                                    @error('motivo') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                    <button type="button"
                            wire:click="guardar"
                            wire:loading.attr="disabled"
                            class="inline-flex w-full justify-center rounded-md px-3 py-2 text-sm font-semibold text-white shadow-sm sm:ml-3 sm:w-auto"
                            style="background-color: {{ $tipoAjuste === 'entrada' ? '#16a34a' : '#dc2626' }};">
                        <span wire:loading.remove wire:target="guardar">
                            {{ $tipoAjuste === 'entrada' ? 'Registrar Entrada' : 'Registrar Salida' }}
                        </span>
                        <span wire:loading wire:target="guardar">Guardando...</span>
                    </button>
                    <button type="button"
                            wire:click="cerrarModal"
                            class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
