<div class="space-y-5">
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
            <h3 class="text-xs font-semibold text-gray-900 dark:text-white uppercase tracking-wide">Agregar lote recibido</h3>
        </div>

        <div class="p-4 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="relative">

                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                        Producto
                    </label>

                    <div class="flex items-center gap-1.5">
                        <div class="relative flex-1">
                            <input
                                type="text"
                                wire:model.live.debounce.300ms="searchProducto"
                                @focus="$wire.set('showProductoDropdown', true)"
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
                                @keydown.escape="$wire.set('showProductoDropdown', false)"
                                placeholder="Buscar medicamento o producto..."
                                class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-xs text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-500"
                            >
                            @if($showProductoDropdown && count($productosResultados) > 0)
                                <div class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg shadow-xl max-h-56 overflow-y-auto p-1">
                                    @foreach($productosResultados as $index => $resultado)
                                        <button
                                            type="button"
                                            tabindex="0"
                                            wire:key="prod-btn-{{ $resultado['id'] }}"
                                            wire:click="seleccionarPresentacion({{ $resultado['id'] }})"
                                            @keydown.arrow-down.prevent="
                                                const next = $el.nextElementSibling;
                                                if (next && next.classList.contains('dropdown-item')) {
                                                    next.focus();
                                                }
                                            "
                                            @keydown.arrow-up.prevent="
                                                const prev = $el.previousElementSibling;
                                                if (prev && prev.classList.contains('dropdown-item')) {
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
                                                    $wire.set('showProductoDropdown', false);
                                                }
                                            "
                                            class="group dropdown-item w-full text-left rounded-lg px-3 py-2 text-xs
                                                transition-all duration-150
                                                hover:bg-blue-500 hover:text-white
                                                focus:bg-blue-600 focus:text-white
                                                focus:outline-none focus:ring-2 focus:ring-blue-300 focus:shadow-lg focus:shadow-blue-500/30 focus:scale-[1.01]"
                                        >
                                            <div class="flex flex-col">
                                                <span class="font-medium text-gray-900 dark:text-gray-100 group-hover:text-white group-focus:text-white group-focus:font-semibold">
                                                    {{ $resultado['label'] }}
                                                </span>
                                                @if($resultado['codigo'])
                                                    <span class="text-[11px] text-gray-400 group-hover:text-blue-100 group-focus:text-blue-100">
                                                        {{ $resultado['codigo'] }}
                                                    </span>
                                                @endif
                                            </div>
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <button
                            type="button"
                            wire:click="abrirCrearPresentacionModal()"
                            class="inline-flex items-center justify-center p-2 rounded-md bg-primary-600 hover:bg-primary-700 text-white transition-colors h-[34px] w-[34px] flex-shrink-0"
                            title="Registrar nuevo producto o presentación"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                        </button>
                    </div>

                    @if(strlen($searchProducto) >= 2 && count($productosResultados) === 0)
                        <div class="mt-1.5 flex items-center justify-between p-2.5 rounded-md bg-amber-50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-900 text-xs">
                            <span class="text-amber-800 dark:text-amber-300 font-medium">Producto no encontrado</span>
                            <button
                                type="button"
                                wire:click="abrirCrearPresentacionModal()"
                                class="text-primary-600 dark:text-primary-400 font-semibold hover:underline flex items-center gap-1"
                            >
                                <svg class="w-3.5 h-3.5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                                </svg>
                                Registrar
                            </button>
                        </div>
                    @endif

                    @if($productoId)
                        <div class="mt-1.5 flex items-center justify-between text-xs">
                            <span class="text-gray-500 dark:text-gray-400">Producto: <strong class="text-gray-800 dark:text-gray-200">{{ $productoNombre }}</strong></span>
                            <button
                                type="button"
                                wire:click="abrirCrearPresentacionModal()"
                                class="text-amber-600 hover:text-amber-700 dark:text-amber-400 font-semibold flex items-center gap-0.5"
                            >
                                + Agregar presentación
                            </button>
                        </div>
                    @endif

                    @error('productoId')
                        <p class="mt-1 text-xs text-red-500">
                            {{ $message }}
                        </p>
                    @enderror

                </div>

                <div class="relative">
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">Código de lote</label>
                    <input type="text"
                           wire:model.live.debounce.250ms="codigoLote"
                           placeholder="Ej: LOTE-2026-001"
                           class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-xs text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                    @error('codigoLote') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror

                    @if($showLotesDropdown && !empty($lotesResultados))
                        <div class="absolute z-50 left-0 right-0 mt-1 bg-white dark:bg-gray-800 rounded-md border border-gray-200 dark:border-gray-700 shadow-lg max-h-48 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($lotesResultados as $lote)
                                <button type="button"
                                        wire:click="verLoteExistente({{ $lote['id'] }})"
                                        class="w-full text-left px-3 py-2 text-xs hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors flex flex-col gap-0.5">
                                    <span class="font-bold text-gray-900 dark:text-white">{{ $lote['codigo_lote'] }}</span>
                                    <span class="text-[10px] text-gray-500 dark:text-gray-400">{{ $lote['producto_nombre'] }} (Vence: {{ $lote['fecha_vencimiento'] }})</span>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">Fecha fabricación</label>
                    <input type="date"
                           wire:model="fechaFabricacion"
                           class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-xs text-gray-900 dark:text-gray-100 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">Fecha vencimiento</label>
                    <input type="date"
                           wire:model="fechaVencimiento"
                           class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-xs text-gray-900 dark:text-gray-100 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                    @error('fechaVencimiento') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">Ubicación</label>
                    <input type="text"
                           wire:model="ubicacion"
                           placeholder="Ej: Estante A1"
                           class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-xs text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">Total pagado por el lote</label>
                    <input type="number"
                           step="0.01"
                           min="0"
                           wire:model="precioCompraTotal"
                           placeholder="0.00"
                           readonly
                           class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-800 px-3 py-2 text-xs text-gray-500 dark:text-gray-400 placeholder-gray-400 cursor-not-allowed">
                    <p class="mt-1 text-[10px] text-gray-400 dark:text-gray-500">Se calcula automáticamente sumando el total pagado de las presentaciones.</p>
                    @error('precioCompraTotal') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">Observaciones</label>
                    <textarea wire:model="observaciones"
                              rows="2"
                              placeholder="Opcional"
                              class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-xs text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:border-primary-500 focus:ring-1 focus:ring-primary-500"></textarea>
                </div>
            </div>

            <div class="rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-3 py-2 bg-gray-50 dark:bg-gray-700/50 flex items-center justify-between">
                    <span class="text-xs font-medium text-gray-700 dark:text-gray-200">Presentaciones del producto</span>
                    <div class="flex items-center gap-3">
                        @if($presentacionSeleccionadaId && !$mostrarTodasPresentaciones && $totalPresentacionesProducto > 1)
                            <button type="button"
                                    wire:click="verMasPresentaciones"
                                    class="text-xs text-amber-600 hover:text-amber-700">
                                Ver más presentaciones
                            </button>
                        @endif
                        @if($productoId)
                            <button type="button"
                                    wire:click="verHistorial({{ $productoId }})"
                                    class="text-xs text-primary-600 hover:text-primary-700">
                                Historial
                            </button>
                        @endif
                    </div>
                </div>

                @if(count($presentacionesDisponibles) > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-700/40 text-gray-500 uppercase tracking-wide">
                                    <th class="px-3 py-2 text-left font-medium">Presentación</th>
                                    <th class="px-3 py-2 text-right font-medium">Cantidad recibida</th>
                                    <th class="px-3 py-2 text-right font-medium">Total pagado pres.</th>
                                    <th class="px-3 py-2 text-right font-medium">Precio oferta</th>
                                    <th class="px-3 py-2 text-right font-medium">Venta</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @foreach($presentacionesDisponibles as $index => $presentacion)
                                    <tr>
                                        <td class="px-3 py-2 font-medium text-gray-900 dark:text-gray-100">
                                            {{ $presentacion['label'] }}
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="number"
                                                   min="0"
                                                   wire:model.live="presentacionesDisponibles.{{ $index }}.cantidad"
                                                   class="ml-auto w-28 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-2 py-1.5 text-right text-xs text-gray-900 dark:text-gray-100">
                                        </td>
                                        <td class="px-3 py-2">
                                            <div class="flex flex-col items-end">
                                                <input type="number"
                                                       step="0.01"
                                                       min="0"
                                                       wire:model.live="presentacionesDisponibles.{{ $index }}.total_pagado"
                                                       placeholder="0.00"
                                                       class="ml-auto w-28 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-2 py-1.5 text-right text-xs text-gray-900 dark:text-gray-100 placeholder-gray-400">
                                                @if(($presentacion['precio_compra'] ?? 0) > 0)
                                                    <span class="text-[10px] text-gray-400 dark:text-gray-500 mt-0.5 font-medium">
                                                        Costo u.: S/ {{ number_format($presentacion['precio_compra'], 2) }}
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-3 py-2">
                                            <div class="flex flex-col items-end">
                                                <input type="number"
                                                       step="0.01"
                                                       min="0"
                                                       wire:model.live="presentacionesDisponibles.{{ $index }}.precio_especial"
                                                       placeholder="0.00"
                                                       class="ml-auto w-28 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-2 py-1.5 text-right text-xs text-gray-900 dark:text-gray-100 placeholder-gray-400">
                                                @php
                                                    $costoUnit = (float) ($presentacion['precio_compra'] ?? 0);
                                                    $pOferta = (float) ($presentacion['precio_especial'] ?? 0);
                                                @endphp
                                                @if($costoUnit > 0 && $pOferta > 0)
                                                    @if($pOferta < $costoUnit)
                                                        <span class="text-[10px] text-red-600 dark:text-red-400 mt-0.5 font-semibold">
                                                            ⚠️ Pérdida
                                                        </span>
                                                    @elseif($pOferta > $costoUnit)
                                                        <span class="text-[10px] text-green-600 dark:text-green-400 mt-0.5 font-semibold">
                                                            Ganancia: +S/ {{ number_format($pOferta - $costoUnit, 2) }}
                                                        </span>
                                                    @else
                                                        <span class="text-[10px] text-gray-400 dark:text-gray-500 mt-0.5 font-medium">
                                                            Sin ganancia
                                                        </span>
                                                    @endif
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-3 py-2 text-right">
                                            <button type="button"
                                                    wire:click="togglePrecioVenta({{ $index }})"
                                                    class="rounded-md border border-gray-300 dark:border-gray-600 px-2 py-1.5 text-xs text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">
                                                Precio venta
                                            </button>
                                        </td>
                                    </tr>
                                    @if($presentacion['mostrar_precio_venta'] ?? false)
                                        <tr class="bg-gray-50 dark:bg-gray-700/30">
                                            <td colspan="5" class="px-4 py-3">
                                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                                    <div>
                                                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Precio venta</label>
                                                        <input type="number"
                                                               step="0.01"
                                                               min="0"
                                                               wire:model.live="presentacionesDisponibles.{{ $index }}.precio_venta"
                                                               class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-2 py-1.5 text-right text-xs text-gray-900 dark:text-gray-100">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Precio mayorista</label>
                                                        <input type="number"
                                                               step="0.01"
                                                               min="0"
                                                               wire:model.live="presentacionesDisponibles.{{ $index }}.precio_mayorista"
                                                               class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-2 py-1.5 text-right text-xs text-gray-900 dark:text-gray-100">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Mínimo mayorista</label>
                                                        <input type="number"
                                                               min="1"
                                                               wire:model.live="presentacionesDisponibles.{{ $index }}.minimo_mayorista"
                                                               class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-2 py-1.5 text-right text-xs text-gray-900 dark:text-gray-100">
                                                    </div>
                                                </div>

                                                @php
                                                    $costoUnit = (float) ($presentacion['precio_compra'] ?? 0);
                                                    $pVenta = (float) ($presentacion['precio_venta'] ?? 0);
                                                    $pMayorista = (float) ($presentacion['precio_mayorista'] ?? 0);
                                                    $qty = (int) ($presentacion['cantidad'] ?? 0);
                                                @endphp

                                                @if($costoUnit > 0 && ($pVenta > 0 || $pMayorista > 0))
                                                    <div class="mt-3 p-2.5 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-[11px] space-y-2">
                                                        @if($pVenta > 0)
                                                            <div class="flex items-center justify-between">
                                                                <span class="text-gray-500 dark:text-gray-400">Margen de venta unitario:</span>
                                                                @if($pVenta < $costoUnit)
                                                                    <span class="text-red-600 dark:text-red-400 font-semibold flex items-center gap-1">
                                                                        ⚠️ ¡Pérdida! -S/ {{ number_format($costoUnit - $pVenta, 2) }}
                                                                    </span>
                                                                @else
                                                                    @php
                                                                        $gain = $pVenta - $costoUnit;
                                                                        $marginPct = ($gain / $pVenta) * 100;
                                                                    @endphp
                                                                    <span class="text-green-600 dark:text-green-400 font-semibold">
                                                                        +S/ {{ number_format($gain, 2) }} ({{ number_format($marginPct, 1) }}%)
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        @endif

                                                        @if($pMayorista > 0)
                                                            <div class="flex items-center justify-between">
                                                                <span class="text-gray-500 dark:text-gray-400">Margen mayorista unitario:</span>
                                                                @if($pMayorista < $costoUnit)
                                                                    <span class="text-red-600 dark:text-red-400 font-semibold flex items-center gap-1">
                                                                        ⚠️ ¡Pérdida! -S/ {{ number_format($costoUnit - $pMayorista, 2) }}
                                                                    </span>
                                                                @else
                                                                    @php
                                                                        $gainM = $pMayorista - $costoUnit;
                                                                        $marginMPct = ($gainM / $pMayorista) * 100;
                                                                    @endphp
                                                                    <span class="text-green-600 dark:text-green-400 font-semibold">
                                                                        +S/ {{ number_format($gainM, 2) }} ({{ number_format($marginMPct, 1) }}%)
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        @endif

                                                        @if($pVenta > $costoUnit && $qty > 0)
                                                            <div class="flex items-center justify-between pt-2 border-t border-gray-100 dark:border-gray-700 text-gray-600 dark:text-gray-300">
                                                                <span>Ganancia total estimada (venta normal):</span>
                                                                <span class="font-bold text-primary-600 dark:text-primary-400 text-xs">
                                                                    S/ {{ number_format(($pVenta - $costoUnit) * $qty, 2) }}
                                                                </span>
                                                            </div>
                                                        @endif

                                                        @if($pMayorista > $costoUnit && $qty > 0)
                                                            <div class="flex items-center justify-between pt-2 border-t border-gray-100 dark:border-gray-700 text-gray-600 dark:text-gray-300">
                                                                <span>Ganancia total estimada (venta mayorista):</span>
                                                                <span class="font-bold text-teal-600 dark:text-teal-400 text-xs">
                                                                    S/ {{ number_format(($pMayorista - $costoUnit) * $qty, 2) }}
                                                                </span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="px-3 py-6 text-center text-xs text-gray-400 dark:text-gray-500">
                        Selecciona un producto para cargar sus presentaciones.
                    </div>
                @endif
            </div>

            <div class="flex justify-end gap-2">
                @if($editingLoteId)
                    <button type="button"
                            wire:click="cancelarEdicion"
                            class="inline-flex items-center justify-center px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-xs font-medium rounded-md transition-colors">
                        Cancelar edición
                    </button>
                    <button type="button"
                            wire:click="agregarLote"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center justify-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-xs font-medium rounded-md transition-colors disabled:opacity-50">
                        <span wire:loading.remove.delay.200ms wire:target="agregarLote">Actualizar lote en la compra</span>
                        <span wire:loading.delay.200ms wire:target="agregarLote">Actualizando...</span>
                    </button>
                @else
                    <button type="button"
                            wire:click="agregarLote"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center justify-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-xs font-medium rounded-md transition-colors disabled:opacity-50">
                        <span wire:loading.remove.delay.200ms wire:target="agregarLote">Agregar lote a la compra</span>
                        <span wire:loading.delay.200ms wire:target="agregarLote">Agregando...</span>
                    </button>
                @endif
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
            <h3 class="text-xs font-semibold text-gray-900 dark:text-white uppercase tracking-wide">Lotes agregados</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 uppercase tracking-wide">
                        <th class="px-3 py-2 text-left font-medium">Lote</th>
                        <th class="px-3 py-2 text-left font-medium">Producto</th>
                        <th class="px-3 py-2 text-left font-medium">Presentaciones</th>
                        <th class="px-3 py-2 text-right font-medium">Stock</th>
                        <th class="px-3 py-2 text-right font-medium">Total pagado</th>
                        <th class="px-3 py-2 text-center font-medium">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @php
                        $visibleDetalles = collect($detalles)->filter(fn($d) => $d['id'] !== $editingDetalleId);
                    @endphp
                    @forelse($visibleDetalles as $detalle)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                            <td class="px-3 py-3 align-top">
                                <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $detalle['lote']['codigo_lote'] ?? '—' }}</div>
                                <div class="text-gray-400">{{ $detalle['lote']['fecha_vencimiento'] ? \Carbon\Carbon::parse($detalle['lote']['fecha_vencimiento'])->format('d/m/Y') : 'Sin vencimiento' }}</div>
                            </td>
                            <td class="px-3 py-3 align-top font-medium text-gray-900 dark:text-gray-100">
                                {{ $detalle['lote']['producto_nombre'] ?? 'Producto' }}
                            </td>
                            <td class="px-3 py-3 align-top">
                                <div class="space-y-1">
                                    @foreach($detalle['presentaciones'] as $presentacion)
                                        <div class="flex flex-wrap items-center gap-2 text-gray-600 dark:text-gray-300">
                                            <span>{{ $presentacion['nombre'] }} x {{ $presentacion['cantidad'] }} {{ $presentacion['unidad'] }}</span>
                                            <span class="font-semibold">{{ number_format($presentacion['stock'], 0) }} recib.</span>
                                            @if(($presentacion['precio_compra'] ?? 0) > 0)
                                                <span class="text-gray-400 dark:text-gray-500">| Costo u.: S/ {{ number_format($presentacion['precio_compra'], 2) }}</span>
                                            @endif
                                            @if($presentacion['precio_oferta'] !== null)
                                                <span class="text-primary-600">| Oferta: S/ {{ number_format($presentacion['precio_oferta'], 2) }}</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-3 py-3 align-top text-right font-semibold text-gray-900 dark:text-gray-100">
                                {{ number_format($detalle['total_stock'], 0) }}
                            </td>
                            <td class="px-3 py-3 align-top text-right font-semibold text-primary-600">
                                S/ {{ number_format($detalle['precio_compra'], 2) }}
                            </td>
                            <td class="px-3 py-3 align-top text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button"
                                            wire:click="editarDetalle({{ $detalle['id'] }})"
                                            class="text-amber-500 hover:text-amber-700 transition-colors"
                                            title="Editar Lote">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <button type="button"
                                            wire:click="eliminarDetalle({{ $detalle['id'] }})"
                                            wire:confirm="¿Eliminar este lote de la compra?"
                                            class="text-red-500 hover:text-red-700 transition-colors"
                                            title="Eliminar Lote">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-8 text-center text-gray-400 dark:text-gray-500">
                                Todavía no agregaste lotes a esta compra.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($showCrearPresentacionModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[85vh] overflow-y-auto">
                <div class="flex justify-between items-center px-5 py-3 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white">Crear presentación</h3>
                    <button wire:click="cerrarCrearPresentacionModal" class="text-gray-400 hover:text-gray-600 text-lg leading-none">&times;</button>
                </div>

                <div class="p-5 space-y-5">
                    <div class="inline-flex rounded-md border border-gray-200 dark:border-gray-700 overflow-hidden text-xs">
                        <button type="button"
                                wire:click="$set('modoProductoPresentacion', 'existente')"
                                class="px-3 py-2 {{ $modoProductoPresentacion === 'existente' ? 'bg-primary-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200' }}">
                            Asignar a producto
                        </button>
                        <button type="button"
                                wire:click="$set('modoProductoPresentacion', 'nuevo')"
                                class="px-3 py-2 border-l border-gray-200 dark:border-gray-700 {{ $modoProductoPresentacion === 'nuevo' ? 'bg-primary-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200' }}">
                            Crear producto
                        </button>
                    </div>

                    @if($modoProductoPresentacion === 'existente')
                        <div class="relative">
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Producto existente</label>
                            <input type="text"
                                   wire:model.live.debounce.300ms="modalSearchProducto"
                                   @keydown.arrow-down.prevent="const first = $el.closest('.relative').querySelector('.dropdown-item'); if (first) first.focus();"
                                   @keydown.enter.prevent="const results = $el.closest('.relative').querySelectorAll('.dropdown-item'); if (results.length === 1) { results[0].click(); } else if (results.length > 0) { results[0].focus(); }"
                                   placeholder="Buscar producto..."
                                   class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-xs text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                            @if($modalShowProductoDropdown && count($modalProductosResultados) > 0)
                                <div class="dropdown-container absolute z-50 mt-1 w-full bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-md shadow-lg max-h-48 overflow-y-auto">
                                    @foreach($modalProductosResultados as $producto)
                                        <button type="button"
                                                wire:key="modal-prod-btn-{{ $producto['id'] }}"
                                                wire:click="seleccionarProductoParaPresentacion({{ $producto['id'] }}, @js($producto['nombre']))"
                                                @keydown.arrow-down.prevent="const next = $el.nextElementSibling; if (next && next.classList.contains('dropdown-item')) next.focus();"
                                                @keydown.arrow-up.prevent="const prev = $el.previousElementSibling; if (prev && prev.classList.contains('dropdown-item')) { prev.focus(); } else { const input = $el.closest('.relative').querySelector('input'); if (input) input.focus(); }"
                                                @keydown.escape="const input = $el.closest('.relative').querySelector('input'); if (input) { input.focus(); $wire.set('modalShowProductoDropdown', false); }"
                                                class="group dropdown-item w-full text-left px-3 py-2 text-xs border-b border-gray-100 dark:border-gray-600 last:border-0 hover:bg-blue-600 focus:bg-blue-600 dark:hover:bg-blue-500 dark:focus:bg-blue-500 focus:outline-none transition-colors">
                                            <span class="font-medium text-gray-900 dark:text-gray-100 group-hover:text-white group-focus:text-white">{{ $producto['nombre'] }}</span>
                                            @if($producto['codigo'])
                                                <span class="text-gray-400 ml-2 group-hover:text-blue-100 group-focus:text-blue-100">{{ $producto['codigo'] }}</span>
                                            @endif
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                            @error('modalProductoId') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Nombre del producto</label>
                                <input type="text"
                                       wire:model="modalNuevoProductoNombre"
                                       class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-xs text-gray-900 dark:text-gray-100">
                                @error('modalNuevoProductoNombre') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Código interno</label>
                                <input type="text"
                                       wire:model="modalCodigoInterno"
                                       placeholder="Opcional"
                                       class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-xs text-gray-900 dark:text-gray-100">
                            </div>
                            <div class="flex items-end gap-2 pb-2">
                                <input type="checkbox" wire:model="modalAfectoIgv" id="modalAfectoIgv"
                                       class="rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500">
                                <label for="modalAfectoIgv" class="text-xs text-gray-700 dark:text-gray-300">Afecto a IGV</label>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Categoría</label>
                                <select wire:model="modalCategoriaId"
                                        class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-xs text-gray-900 dark:text-gray-100">
                                    <option value="">Sin categoría</option>
                                    @foreach($this->categorias as $categoria)
                                        <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Marca</label>
                                <select wire:model="modalMarcaId"
                                        class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-xs text-gray-900 dark:text-gray-100">
                                    <option value="">Sin marca</option>
                                    @foreach($this->marcas as $marca)
                                        <option value="{{ $marca->id }}">{{ $marca->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endif

                    <div class="border-t border-gray-100 dark:border-gray-700 pt-4">
                        <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-3">Datos de presentación</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div class="relative">
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Tipo de presentación</label>
                                <input type="text"
                                       wire:model.live.debounce.150ms="modalTipoPresentacion"
                                       placeholder="Ej: Blíster, Caja, Frasco"
                                       class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-xs text-gray-900 dark:text-gray-100">
                                
                                @if($showModalPresentacionDropdown && count($modalPresentacionesResultados) > 0)
                                    <div class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-md shadow-lg max-h-48 overflow-y-auto p-1">
                                        @foreach($modalPresentacionesResultados as $pRes)
                                            <button type="button"
                                                    wire:click="seleccionarPresentacionDesdeModal({{ $pRes['id'] }})"
                                                    class="group w-full text-left rounded px-3 py-2 text-xs hover:bg-blue-500 hover:text-white dark:hover:bg-blue-600 transition-all duration-150 focus:outline-none flex items-center justify-between">
                                                <span class="font-medium text-gray-900 dark:text-gray-100 group-hover:text-white">{{ $pRes['tipo_presentacion'] }}</span>
                                                <span class="text-[11px] text-gray-500 dark:text-gray-400 group-hover:text-blue-100">({{ $pRes['cantidad'] }} {{ $pRes['unidad_medida_abreviatura'] }})</span>
                                            </button>
                                        @endforeach
                                    </div>
                                @endif
                                
                                @error('modalTipoPresentacion') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Código de barra principal</label>
                                <input type="text"
                                       wire:model="modalCodigoBarra"
                                       class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-xs text-gray-900 dark:text-gray-100">
                                @error('modalCodigoBarra') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Códigos de barra adicionales</label>
                                <div class="flex items-center gap-1.5">
                                    <input type="text"
                                           wire:model="modalNuevoCodigoBarra"
                                           placeholder="Escanear o escribir código adicional..."
                                           wire:keydown.enter.prevent="agregarCodigoBarraDesdeModal"
                                           class="flex-1 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-xs text-gray-900 dark:text-gray-100">
                                    <button type="button"
                                            wire:click="agregarCodigoBarraDesdeModal"
                                            class="inline-flex items-center justify-center rounded-md bg-primary-600 hover:bg-primary-700 text-white font-bold transition-colors h-[34px] w-[34px] flex-shrink-0"
                                            title="Agregar código adicional"
                                    >
                                        +
                                    </button>
                                </div>
                                @error('modalNuevoCodigoBarra') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror

                                @if(count($modalBarras) > 0)
                                    <div class="mt-2 flex flex-wrap gap-1.5 max-h-24 overflow-y-auto p-1.5 border border-gray-200 dark:border-gray-700 rounded-md bg-gray-50 dark:bg-gray-800">
                                        @foreach($modalBarras as $iBarra => $code)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                {{ $code }}
                                                <button type="button" 
                                                        wire:click="removerCodigoBarraDesdeModal({{ $iBarra }})" 
                                                        class="text-blue-500 hover:text-blue-700 font-bold focus:outline-none ml-1">&times;</button>
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Unidad de medida</label>
                                <select wire:model="modalUnidadMedidaId"
                                        class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-xs text-gray-900 dark:text-gray-100">
                                    <option value="">Seleccionar</option>
                                    @foreach($this->unidadesMedida as $unidad)
                                        <option value="{{ $unidad->id }}">{{ $unidad->nombre }} ({{ $unidad->abreviatura }})</option>
                                    @endforeach
                                </select>
                                @error('modalUnidadMedidaId') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Cantidad por empaque</label>
                                <input type="number"
                                       min="1"
                                       wire:model.live="modalCantidadPorEmpaque"
                                       class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-xs text-gray-900 dark:text-gray-100">
                            </div>
                            @if($modalCantidadPorEmpaque > 1)
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Presentación base</label>
                                    @if($modoProductoPresentacion === 'existente' && $modalProductoId)
                                        @php
                                            $basePresentaciones = $this->basePresentaciones;
                                        @endphp
                                        @if($basePresentaciones->isNotEmpty())
                                            <select wire:model="modalPresentacionBaseId"
                                                    class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-xs text-gray-900 dark:text-gray-100">
                                                <option value="">Seleccionar presentación base</option>
                                                @foreach($basePresentaciones as $basePres)
                                                    <option value="{{ $basePres->id }}">
                                                        {{ $basePres->tipo_presentacion }} ({{ $basePres->cantidad }} {{ $basePres->unidadMedida?->abreviatura }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('modalPresentacionBaseId') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                        @else
                                            <div class="w-full rounded-md border border-dashed border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 px-3 py-2 text-xs text-gray-500 dark:text-gray-400">
                                                No hay otras presentaciones para este producto.
                                            </div>
                                        @endif
                                    @else
                                        <div class="w-full rounded-md border border-dashed border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 px-3 py-2 text-xs text-gray-500 dark:text-gray-400" title="Para asociar una presentación base, el producto debe ser existente y tener presentaciones registradas.">
                                            No disponible para producto nuevo.
                                        </div>
                                    @endif
                                </div>
                            @endif
                            <div class="md:col-span-2 flex items-center gap-2">
                                <input type="checkbox" wire:model="modalEsPesable" id="modalEsPesable"
                                       class="rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500">
                                <label for="modalEsPesable" class="text-xs text-gray-700 dark:text-gray-300">Es pesable</label>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Imagen de la presentación (Opcional)</label>
                                <div class="flex items-center gap-3 p-3 border border-dashed border-gray-300 dark:border-gray-600 rounded-md bg-gray-50 dark:bg-gray-800">
                                    @if ($modalImagen)
                                        <div class="relative w-16 h-16 rounded bg-gray-100 dark:bg-gray-900 overflow-hidden border border-gray-200 dark:border-gray-700 flex items-center justify-center">
                                            <img src="{{ $modalImagen->temporaryUrl() }}" class="object-cover w-full h-full">
                                            <button type="button" 
                                                    wire:click="$set('modalImagen', null)" 
                                                    class="absolute top-0 right-0 bg-red-600 hover:bg-red-700 text-white rounded-bl p-1 text-[10px] line-none leading-none">
                                                &times;
                                            </button>
                                        </div>
                                    @else
                                        <div class="w-16 h-16 rounded border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-900 flex items-center justify-center text-gray-400 dark:text-gray-500">
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                    @endif
                                    <div class="flex-1 min-w-0">
                                        <input type="file" 
                                               wire:model="modalImagen" 
                                               id="modalImagen" 
                                               accept="image/*" 
                                               class="hidden">
                                        <label for="modalImagen" 
                                               class="inline-flex items-center px-2.5 py-1.5 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-xs font-medium text-gray-700 dark:text-gray-200 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none cursor-pointer transition-colors">
                                            {{ $modalImagen ? 'Cambiar imagen' : 'Seleccionar imagen' }}
                                        </label>
                                        <p class="mt-1 text-[10px] text-gray-500 dark:text-gray-400">PNG, JPG, GIF hasta 2MB</p>
                                        @error('modalImagen') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-2 px-5 py-3 border-t border-gray-100 dark:border-gray-700">
                    <button type="button"
                            wire:click="cerrarCrearPresentacionModal"
                            class="px-4 py-1.5 text-xs text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md transition-colors">
                        Cancelar
                    </button>
                    <button type="button"
                            wire:click="crearPresentacionDesdeModal"
                            wire:loading.attr="disabled"
                            class="px-4 py-1.5 text-xs bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-md disabled:opacity-50 transition-colors">
                        <span wire:loading.remove.delay.200ms wire:target="crearPresentacionDesdeModal">Crear y seleccionar</span>
                        <span wire:loading.delay.200ms wire:target="crearPresentacionDesdeModal">Guardando...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if($showHistorial)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full mx-4 max-h-[60vh] overflow-y-auto">
                <div class="flex justify-between items-center px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-xs font-medium text-gray-900 dark:text-white">Historial de compras</h3>
                    <button wire:click="cerrarHistorial" class="text-gray-400 hover:text-gray-600">&times;</button>
                </div>
                <div class="p-4 space-y-3">
                    @forelse($historialCompras as $historial)
                        <div class="pb-3 border-b border-gray-100 dark:border-gray-700 last:border-0 last:pb-0">
                            <div class="flex justify-between text-xs">
                                <span class="font-medium text-gray-900 dark:text-gray-100">{{ $historial['proveedor'] }}</span>
                                <span class="text-gray-400">{{ $historial['fecha'] }}</span>
                            </div>
                            <div class="flex justify-between text-xs text-gray-500 mt-1">
                                <span>{{ number_format($historial['unidades'], 0) }} unidades</span>
                                <span>S/ {{ number_format($historial['total'], 2) }}</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-400 text-xs text-center py-4">Sin historial disponible</p>
                    @endforelse
                </div>
            </div>
        </div>
    @endif

    @if($showLoteExistenteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-950/60 dark:bg-gray-950/80 backdrop-blur-sm">
            <div class="bg-white dark:bg-gray-900 ring-1 ring-gray-950/5 dark:ring-white/10 rounded-xl shadow-2xl max-w-lg w-full mx-auto max-h-[85vh] flex flex-col overflow-hidden">
                <!-- Header -->
                <div class="flex justify-between items-center px-6 py-4 border-b border-gray-100 dark:border-white/5">
                    <h3 class="text-sm font-semibold text-gray-950 dark:text-white uppercase tracking-wide">
                        Detalles de Lote Existente
                    </h3>
                    <button wire:click="$set('showLoteExistenteModal', false)" class="text-gray-400 hover:text-gray-505 dark:text-gray-500 dark:hover:text-gray-400 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                <!-- Content (Scrollable) -->
                <div class="p-6 space-y-5 overflow-y-auto flex-1">
                    <!-- Info Grid -->
                    <div class="grid grid-cols-2 gap-4 text-xs bg-gray-50 dark:bg-white/5 p-4 rounded-xl border border-gray-100 dark:border-white/5">
                        <div>
                            <span class="text-gray-500 dark:text-gray-400 block mb-1 font-medium">Código Lote:</span>
                            <strong class="text-gray-955 dark:text-white text-sm font-bold tracking-tight">{{ $modalLoteDetalles['codigo_lote'] }}</strong>
                        </div>
                        <div>
                            <span class="text-gray-500 dark:text-gray-400 block mb-1 font-medium">Producto:</span>
                            <strong class="text-gray-955 dark:text-white text-sm font-bold tracking-tight">{{ $modalLoteDetalles['producto_nombre'] }}</strong>
                        </div>
                        <div>
                            <span class="text-gray-500 dark:text-gray-400 block mb-1 font-medium">F. Fabricación:</span>
                            <span class="text-gray-955 dark:text-gray-200 font-semibold">{{ $modalLoteDetalles['fecha_fabricacion'] }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500 dark:text-gray-400 block mb-1 font-medium">F. Vencimiento:</span>
                            <span class="text-gray-955 dark:text-gray-200 font-semibold">{{ $modalLoteDetalles['fecha_vencimiento'] }}</span>
                        </div>
                        <div class="col-span-2">
                            <span class="text-gray-500 dark:text-gray-400 block mb-1 font-medium">Ubicación:</span>
                            <span class="text-gray-955 dark:text-gray-200 font-semibold">{{ $modalLoteDetalles['ubicacion'] }}</span>
                        </div>
                        <div class="col-span-2 border-t border-gray-100 dark:border-white/5 pt-3">
                            <span class="text-gray-500 dark:text-gray-400 block mb-1 font-medium">Observaciones:</span>
                            <span class="text-gray-955 dark:text-gray-200 block text-wrap leading-relaxed font-medium">{{ $modalLoteDetalles['observaciones'] }}</span>
                        </div>
                    </div>

                    <!-- Presentations Table -->
                    <div>
                        <h4 class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-3">Presentaciones y Stock en Sistema</h4>
                        <div class="border border-gray-100 dark:border-white/5 rounded-xl overflow-hidden shadow-sm">
                            <table class="w-full text-xs text-left">
                                <thead>
                                    <tr class="bg-gray-50 dark:bg-white/5 border-b border-gray-100 dark:border-white/5 text-gray-500 dark:text-gray-400 font-semibold text-[10px] uppercase tracking-wider">
                                        <th class="px-4 py-2.5">Presentación</th>
                                        <th class="px-4 py-2.5 text-right">Stock Inicial</th>
                                        <th class="px-4 py-2.5 text-right">Costo Unit.</th>
                                        @if(Auth::user()->can('ventas.ver'))
                                            <th class="px-4 py-2.5 text-right">Precio Oferta</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                                    @foreach($modalLoteDetalles['presentaciones'] as $pres)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                                            <td class="px-4 py-3 font-medium text-gray-955 dark:text-gray-100">
                                                {{ $pres['nombre'] }} x {{ $pres['cantidad'] }} {{ $pres['unidad'] }}
                                            </td>
                                            <td class="px-4 py-3 text-right font-bold text-gray-955 dark:text-gray-100">
                                                {{ number_format($pres['stock'], 0) }}
                                            </td>
                                            <td class="px-4 py-3 text-right text-gray-955 dark:text-gray-100 font-medium">
                                                S/ {{ number_format($pres['precio_compra'], 2) }}
                                            </td>
                                            @if(Auth::user()->can('ventas.ver'))
                                                <td class="px-4 py-3 text-right text-primary-600 dark:text-primary-400 font-bold">
                                                    {{ $pres['precio_oferta'] !== null ? 'S/ ' . number_format($pres['precio_oferta'], 2) : '—' }}
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-100 dark:border-white/5 bg-gray-50/50 dark:bg-white/5">
                    <button type="button" wire:click="$set('showLoteExistenteModal', false)" class="px-4 py-2 text-xs font-semibold text-gray-955 dark:text-white bg-white dark:bg-white/5 border border-gray-300 dark:border-white/10 hover:bg-gray-50 dark:hover:bg-white/10 rounded-lg shadow-sm transition-colors">
                        Cancelar
                    </button>
                    <button type="button" wire:click="cargarLoteExistenteParaEditar" class="px-4 py-2 text-xs font-semibold text-white bg-primary-600 hover:bg-primary-500 dark:bg-primary-500 dark:hover:bg-primary-400 rounded-lg transition-colors shadow-sm">
                        Cargar Lote para Editar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
