<div>
    {{-- Modal de Ajuste de Stock Rediseñado - Paso Único y Confirmación --}}
    <div x-data="{ open: @entangle('showModal') }"
         x-show="open"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto"
         style="display: none;">
        
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity"
             @click="$wire.cerrarModal()"
             x-show="open">
        </div>

        <!-- Contenido Modal -->
        <div x-show="open"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-4"
             class="relative bg-white dark:bg-[#0c101d] border border-slate-200 dark:border-[#1c243a] rounded-3xl w-full max-w-lg overflow-hidden shadow-2xl transition-all my-8 z-10">
            
            <!-- Cabecera -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-[#1d2745]/30 bg-slate-50/50 dark:bg-slate-950/20">
                <h3 class="text-sm font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="p-1.5 rounded-lg {{ $tipoAjuste === 'entrada' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'bg-rose-500/10 text-rose-600 dark:text-rose-400' }}">
                        @if ($tipoAjuste === 'entrada')
                            ➕
                        @else
                            ➖
                        @endif
                    </span>
                    <span>{{ $tipoAjuste === 'entrada' ? 'Ajustar Entrada (Ingreso)' : 'Ajustar Salida (Merma)' }}</span>
                </h3>
                <button type="button" 
                        wire:click="cerrarModal"
                        class="p-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-900 text-slate-400 hover:text-slate-600 dark:text-slate-500 dark:hover:text-slate-300 transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Formularios -->
            @if (!$showConfirmStep)
                <!-- PASO 1: CAPTURA DE DATOS -->
                <form wire:submit.prevent="guardar" class="p-6 space-y-4 max-h-[75vh] overflow-y-auto scrollbar-thin">
                    {{-- Sucursal selector (se muestra únicamente si no está bloqueada) --}}
                    @if (!$isSucursalLocked)
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Sucursal *</label>
                            <select wire:model.live="sucursalId"
                                    class="w-full py-2.5 px-3 text-xs rounded-xl border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition">
                                <option value="">Seleccionar sucursal...</option>
                                @foreach ($this->sucursales as $suc)
                                    <option value="{{ $suc->id }}">{{ $suc->nombre_sucursal }}</option>
                                @endforeach
                            </select>
                            @error('sucursalId') <span class="text-xs text-rose-500 font-medium block mt-1">{{ $message }}</span> @enderror
                        </div>
                    @endif

                    {{-- Búsqueda Inteligente Única de Producto + Presentación --}}
                    <div class="space-y-1.5 relative"
                         x-data="{
                             activeIndex: -1,
                             openDropdown: @entangle('showProductoDropdown'),
                             get total() {
                                 return $el.querySelectorAll('.search-item').length;
                             },
                             selectActive() {
                                 if (this.activeIndex >= 0) {
                                     const items = $el.querySelectorAll('.search-item');
                                     if (items[this.activeIndex]) {
                                         items[this.activeIndex].click();
                                         this.activeIndex = -1;
                                     }
                                 }
                             }
                         }"
                         x-init="$watch('openDropdown', val => { if (!val) activeIndex = -1; })">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Buscar Producto y Presentación *</label>
                        <input type="text"
                               wire:model.live.debounce.300ms="searchProducto"
                               placeholder="Ej: Coca 500ml, bolsa 1kg, lata..."
                               @keydown.arrow-down.prevent="if (openDropdown) { activeIndex = (activeIndex + 1) % total; } else { openDropdown = true; }"
                               @keydown.arrow-up.prevent="if (openDropdown) { activeIndex = (activeIndex - 1 + total) % total; }"
                               @keydown.enter.prevent="if (openDropdown && activeIndex >= 0) { selectActive(); } else { $wire.guardar(); }"
                               @keydown.escape.prevent="if (openDropdown) { openDropdown = false; activeIndex = -1; } else { $wire.cerrarModal(); }"
                               @input="activeIndex = -1"
                               class="w-full px-4 py-2.5 text-xs rounded-xl border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition">
                        @error('productoId') <span class="text-xs text-rose-500 font-medium block mt-1">{{ $message }}</span> @enderror
                        @error('presentacionId') <span class="text-xs text-rose-500 font-medium block mt-1">{{ $message }}</span> @enderror

                        @if ($showProductoDropdown && count($productosResultados) > 0)
                            <div class="absolute z-50 left-0 right-0 mt-1 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl max-h-48 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800">
                                @foreach ($productosResultados as $index => $prod)
                                    <button type="button"
                                            wire:click="seleccionarPresentacion({{ json_encode($prod) }})"
                                            class="search-item w-full text-left px-4 py-2.5 text-xs font-semibold text-slate-800 dark:text-slate-300 transition-colors border-l-2"
                                            :class="activeIndex === {{ $index }} ? 'bg-indigo-50 dark:bg-indigo-950/40 border-indigo-500 text-indigo-700 dark:text-indigo-300' : 'hover:bg-slate-50 dark:hover:bg-slate-900 border-transparent'">
                                        {{ $prod['producto_nombre'] }} &mdash; <span class="text-indigo-600 dark:text-indigo-400 font-bold">{{ $prod['tipo_presentacion'] }}</span>
                                        @if ($prod['unidad_medida_abr'])
                                            <span class="text-slate-400 text-[10px]">({{ $prod['unidad_medida_abr'] }})</span>
                                        @endif
                                        @if ($prod['codigo_interno'])
                                            <span class="text-slate-400 text-[10px]"> - {{ $prod['codigo_interno'] }}</span>
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Alerta de Stock Cero Inmediato (solo para salida) --}}
                    @if ($tipoAjuste === 'salida' && $presentacionId && $totalStockDisponible === 0)
                        <div class="p-3 rounded-xl bg-rose-500/10 border border-rose-500/20 text-xs text-rose-600 dark:text-rose-400 font-semibold flex items-center gap-2">
                            <span>⚠️</span>
                            <span>Esta presentación no tiene stock disponible en esta sucursal.</span>
                        </div>
                    @endif

                    {{-- Cantidad (solo para salida) --}}
                    @if ($tipoAjuste === 'salida')
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Cantidad *</label>
                            <input type="number"
                                   wire:model.live="cantidad"
                                   min="1"
                                   class="w-full px-4 py-2.5 text-xs rounded-xl border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition">
                            @error('cantidad') <span class="text-xs text-rose-500 font-medium block mt-1">{{ $message }}</span> @enderror
                        </div>
                    @endif

                    @if ($tipoAjuste === 'salida')
                        <!-- CAMPOS ESPECÍFICOS DE SALIDA (MERMA) -->
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Lote de Salida *</label>
                            <select wire:model.live="lotePresentacionId"
                                    class="w-full py-2.5 px-3 text-xs rounded-xl border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition">
                                <option value="fifo">Automático (FIFO) — Priorizar más antiguos</option>
                                @foreach ($lotesDisponibles as $lote)
                                    <option value="{{ $lote['id'] }}">
                                        Lote: {{ $lote['codigo_lote'] }} (Stock: {{ $lote['stock'] }} uds @if($lote['vencimiento'])· vence {{ $lote['vencimiento'] }}@endif)
                                    </option>
                                @endforeach
                            </select>
                            @error('lotePresentacionId') <span class="text-xs text-rose-500 font-medium block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Tipo de Merma *</label>
                            <select wire:model.live="tipoMerma"
                                    class="w-full py-2.5 px-3 text-xs rounded-xl border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition">
                                <option value="roto">Roto / Dañado</option>
                                <option value="vencido">Producto Vencido</option>
                                <option value="robo">Pérdida / Robo</option>
                                <option value="otro">Otros (Consumo interno, ajuste inventario, etc)</option>
                            </select>
                            @error('tipoMerma') <span class="text-xs text-rose-500 font-medium block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Motivo del Ajuste (Opcional)</label>
                            <textarea wire:model.live="motivo"
                                      rows="2"
                                      placeholder="Describa brevemente el motivo del retiro de mercadería..."
                                      class="w-full px-4 py-2.5 text-xs rounded-xl border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition"></textarea>
                            @error('motivo') <span class="text-xs text-rose-500 font-medium block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Observación de Auditoría (Opcional)</label>
                            <textarea wire:model.live="observacion"
                                      rows="2"
                                      placeholder="Detalles adicionales opcionales..."
                                      class="w-full px-4 py-2.5 text-xs rounded-xl border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition"></textarea>
                            @error('observacion') <span class="text-xs text-rose-500 font-medium block mt-1">{{ $message }}</span> @enderror
                        </div>

                    @else
                        <!-- CAMPOS ESPECÍFICOS DE ENTRADA (INGRESO MANUAL) -->
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Código de Lote *</label>
                            <input type="text"
                                   wire:model.live="loteCodigo"
                                   placeholder="Ej: LOTE-NUEVO-A"
                                   class="w-full px-4 py-2.5 text-xs rounded-xl border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition">
                            @error('loteCodigo') <span class="text-xs text-rose-500 font-medium block mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Grid de 3 columnas para Entrada: Cantidad, Total Pagado, Costo Unitario --}}
                        <div class="grid grid-cols-3 gap-3">
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Cantidad *</label>
                                <input type="number"
                                       wire:model.live="cantidad"
                                       min="1"
                                       class="w-full px-3 py-2.5 text-xs rounded-xl border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition">
                                @error('cantidad') <span class="text-xs text-rose-500 font-medium block mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Pagado (S/)</label>
                                <input type="number"
                                       step="0.01"
                                       wire:model.live="totalPagado"
                                       placeholder="Ej: 100"
                                       class="w-full px-3 py-2.5 text-xs rounded-xl border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition">
                                @error('totalPagado') <span class="text-xs text-rose-500 font-medium block mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Costo Unitario (S/)</label>
                                <input type="number"
                                       step="0.0001"
                                       wire:model.live="costo"
                                       readonly
                                       placeholder="Calculado"
                                       class="w-full px-3 py-2.5 text-xs rounded-xl border-slate-200 bg-slate-50 dark:bg-slate-800/40 dark:border-slate-800 text-slate-500 dark:text-slate-400 shadow-sm cursor-not-allowed select-none">
                                @error('costo') <span class="text-xs text-rose-500 font-medium block mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Fecha Vencimiento</label>
                            <input type="date"
                                   wire:model.live="fechaVencimiento"
                                   class="w-full px-4 py-2.5 text-xs rounded-xl border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition">
                            @error('fechaVencimiento') <span class="text-xs text-rose-500 font-medium block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Proveedor</label>
                            <select wire:model.live="proveedorId"
                                    class="w-full py-2.5 px-3 text-xs rounded-xl border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition">
                                <option value="">Ninguno (Ajuste / Regularización)</option>
                                @foreach ($this->proveedores as $prov)
                                    <option value="{{ $prov->id }}">{{ $prov->nombre }}</option>
                                @endforeach
                            </select>
                            @error('proveedorId') <span class="text-xs text-rose-500 font-medium block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Observación (Opcional)</label>
                            <textarea wire:model.live="observacion"
                                      rows="2"
                                      placeholder="Detalles sobre esta regularización de inventario..."
                                      class="w-full px-4 py-2.5 text-xs rounded-xl border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition"></textarea>
                            @error('observacion') <span class="text-xs text-rose-500 font-medium block mt-1">{{ $message }}</span> @enderror
                        </div>
                    @endif

                    <!-- Footer Acciones Paso 1 -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-[#1d2745]/30">
                        <button type="button" 
                                wire:click="cerrarModal"
                                class="px-5 py-2.5 text-xs font-bold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl transition">
                            Cancelar
                        </button>
                        <button type="submit" 
                                @if ($tipoAjuste === 'salida' && $presentacionId && $totalStockDisponible === 0) disabled @endif
                                class="px-5 py-2.5 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 active:scale-95 transition-all shadow-md shadow-indigo-500/20 rounded-xl disabled:opacity-50 disabled:cursor-not-allowed">
                            <span>Revisar Ajuste →</span>
                        </button>
                    </div>
                </form>
            @else
                <!-- PASO 2: CONFIRMACIÓN Y RESUMEN ERP -->
                <div class="p-6 space-y-5 max-h-[75vh] overflow-y-auto scrollbar-thin">
                    <div class="bg-indigo-500/5 dark:bg-indigo-500/10 border border-indigo-500/15 rounded-2xl p-4 space-y-3.5">
                        <h4 class="text-xs font-black uppercase text-indigo-600 dark:text-indigo-400 tracking-wider">
                            Resumen de Operación
                        </h4>
                        
                        <!-- Contenido Condicional del Resumen -->
                        @if ($tipoAjuste === 'salida')
                            <!-- Resumen Salida / Merma -->
                            <div class="space-y-2.5 text-xs">
                                <p class="flex justify-between">
                                    <span class="text-slate-400">Producto:</span>
                                    <strong class="text-slate-800 dark:text-slate-200">{{ $searchProducto }}</strong>
                                </p>
                                <p class="flex justify-between">
                                    <span class="text-slate-400">Cantidad Total:</span>
                                    <strong class="text-rose-600 font-extrabold">{{ $cantidad }} uds</strong>
                                </p>
                                <p class="flex justify-between">
                                    <span class="text-slate-400">Tipo Merma:</span>
                                    <span class="px-2.5 py-0.5 rounded-lg font-bold bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/25">
                                        {{ $tipoMerma === 'roto' ? 'Roto / Dañado' : ($tipoMerma === 'vencido' ? 'Vencido' : ($tipoMerma === 'robo' ? 'Robo / Pérdida' : 'Otros')) }}
                                    </span>
                                </p>
                                <p class="flex justify-between">
                                    <span class="text-slate-400">Motivo:</span>
                                    <span class="text-slate-800 dark:text-slate-200 font-semibold">{{ $motivo ?: 'Sin motivo registrado' }}</span>
                                </p>
                            </div>

                            <!-- Distribución de Lotes (FIFO) -->
                            <div class="pt-3 border-t border-slate-100 dark:border-slate-800/60 space-y-2">
                                <span class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-wider block">Distribución de Retiro por Lotes:</span>
                                
                                <!-- Warn if manual lot wasn't enough -->
                                @php
                                    $manualLotCount = collect($provisionalDistribution)->filter(fn($x) => $x['is_manual'])->count();
                                    $totalLotsCount = count($provisionalDistribution);
                                @endphp
                                @if ($manualLotCount > 0 && $totalLotsCount > 1)
                                    <div class="p-2.5 rounded-xl bg-amber-500/5 border border-amber-500/20 text-[10px] text-amber-600 dark:text-amber-400 font-semibold mb-2">
                                        ⚠️ El lote seleccionado no alcanza el total. Se completará la cantidad restante utilizando el sistema FIFO.
                                    </div>
                                @endif

                                <div class="space-y-1.5">
                                    @foreach ($provisionalDistribution as $dist)
                                        <div class="flex items-center justify-between p-2 rounded-xl bg-white dark:bg-slate-900 border dark:border-slate-800 text-[11px]">
                                            <div class="flex items-center gap-1.5">
                                                <span class="text-slate-400">📦 Lote:</span>
                                                <strong class="text-slate-800 dark:text-slate-200">{{ $dist['codigo_lote'] }}</strong>
                                                @if ($dist['is_manual'])
                                                    <span class="text-[9px] bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/25 px-1.5 py-0.5 rounded-md font-bold">Manual</span>
                                                @else
                                                    <span class="text-[9px] bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 px-1.5 py-0.5 rounded-md font-bold">FIFO</span>
                                                @endif
                                            </div>
                                            <div class="font-extrabold text-rose-600">
                                                -{{ $dist['cantidad_retirar'] }} uds
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <!-- Resumen Entrada / Ingreso -->
                            <div class="space-y-2.5 text-xs">
                                <p class="flex justify-between">
                                    <span class="text-slate-400">Producto:</span>
                                    <strong class="text-slate-800 dark:text-slate-200">{{ $searchProducto }}</strong>
                                </p>
                                <p class="flex justify-between">
                                    <span class="text-slate-400">Lote a registrar:</span>
                                    <strong class="text-slate-800 dark:text-slate-200">{{ $loteCodigo }}</strong>
                                </p>
                                <p class="flex justify-between">
                                    <span class="text-slate-400">Cantidad Ingresar:</span>
                                    <strong class="text-emerald-600 font-extrabold">+{{ $cantidad }} uds</strong>
                                </p>
                                <p class="flex justify-between">
                                    <span class="text-slate-400">Costo Unitario:</span>
                                    <span class="text-slate-800 dark:text-slate-200 font-semibold">
                                        {{ $costo ? 'S/ ' . number_format($costo, 2) : 'No especificado' }}
                                    </span>
                                </p>
                                <p class="flex justify-between">
                                    <span class="text-slate-400">Proveedor:</span>
                                    <span class="text-slate-800 dark:text-slate-200 font-semibold">
                                        @if ($proveedorId)
                                            {{ collect($this->proveedores)->firstWhere('id', $proveedorId)['nombre'] ?? 'No especificado' }}
                                        @else
                                            No especificado (Regularización)
                                        @endif
                                    </span>
                                </p>
                                <p class="flex justify-between">
                                    <span class="text-slate-400">Fecha Vencimiento:</span>
                                    <span class="text-slate-800 dark:text-slate-200 font-semibold">
                                        {{ $fechaVencimiento ? date('d/m/Y', strtotime($fechaVencimiento)) : 'Sin vencimiento' }}
                                    </span>
                                </p>
                            </div>
                        @endif
                    </div>

                    <p class="text-xs text-slate-500 font-semibold text-center mt-4">
                        ¿Confirmas que deseas registrar esta operación en el inventario?
                    </p>

                    <!-- Footer Acciones Paso 2 -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-[#1d2745]/30">
                        <button type="button" 
                                wire:click="volverAtras"
                                class="px-5 py-2.5 text-xs font-bold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl transition">
                            ← Volver
                        </button>
                        <button type="button" 
                                wire:click="guardar"
                                class="px-5 py-2.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 active:scale-95 transition-all shadow-md shadow-emerald-500/20 rounded-xl">
                            <span>Confirmar y Guardar ✓</span>
                        </button>
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>
