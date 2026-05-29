<div class="relative" x-data="{ open: false, tab: 'vence' }" x-on:click.outside="open = false">
    <!-- Botón de la Campanita -->
    <button x-on:click="open = !open; $wire.cargarAlertas()" class="relative p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 transition focus:outline-none rounded-full hover:bg-gray-100 dark:hover:bg-gray-700/50">
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
        </svg>
        @if($totalAlertas > 0)
            <span class="absolute top-1 right-1 flex h-4.5 w-4.5 items-center justify-center rounded-full bg-danger-600 text-[10px] font-bold text-white ring-2 ring-white dark:ring-gray-900">
                {{ $totalAlertas }}
            </span>
        @endif
    </button>

    <!-- Dropdown Panel -->
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-1 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-1 scale-95"
         class="absolute right-0 mt-2 w-80 md:w-96 rounded-xl bg-white dark:bg-gray-800 shadow-2xl border border-gray-100 dark:border-gray-700 z-50 overflow-hidden"
         style="display: none;">
         
        <!-- Header -->
        <div class="px-4 py-3 bg-gray-50 dark:bg-gray-800/80 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
            <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">Panel de Alertas</span>
            <span class="text-xs px-2 py-0.5 rounded-full font-semibold bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                {{ $totalAlertas }} alertas activas
            </span>
        </div>

        <!-- Tabs Selector -->
        <div class="flex border-b border-gray-100 dark:border-gray-700 text-xs">
            <button x-on:click="tab = 'vence'" 
                    class="flex-1 py-2 text-center font-medium border-b-2 transition"
                    x-bind:class="tab === 'vence' ? 'border-primary-500 text-primary-600 dark:text-primary-400 font-semibold' : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'">
                Vencimiento ({{ count($lotesPorVencer) }})
            </button>
            <button x-on:click="tab = 'stock'" 
                    class="flex-1 py-2 text-center font-medium border-b-2 transition"
                    x-bind:class="tab === 'stock' ? 'border-primary-500 text-primary-600 dark:text-primary-400 font-semibold' : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'">
                Stock Bajo ({{ count($productosStockBajo) }})
            </button>
        </div>

        <!-- Content Area -->
        <div class="max-h-72 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-700 custom-scrollbar">
            
            <!-- Tab: Vencimiento -->
            <div x-show="tab === 'vence'">
                @forelse($lotesPorVencer as $lote)
                    <div class="p-3 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition flex gap-3 items-start">
                        <div class="p-1.5 rounded-lg bg-danger-50 dark:bg-danger-950/20 text-danger-600 dark:text-danger-400 mt-0.5">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold text-gray-800 dark:text-gray-200 truncate">
                                {{ $lote['producto_nombre'] }}
                            </p>
                            <p class="text-[10px] text-gray-500 dark:text-gray-400 flex justify-between mt-1">
                                <span>Lote: <span class="font-mono font-semibold">{{ $lote['codigo_lote'] }}</span></span>
                                <span>Stock: <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $lote['stock'] }}</span></span>
                            </p>
                            <div class="text-[10px] font-medium mt-1 flex justify-between items-center">
                                <span class="text-danger-600 dark:text-danger-400">Vence: {{ $lote['fecha_vencimiento'] }}</span>
                                @if($lote['dias_restantes'] <= 0)
                                    <span class="px-1.5 py-0.5 rounded bg-danger-100 dark:bg-danger-900/40 text-danger-700 dark:text-danger-300 text-[9px] font-bold">VENCIDO</span>
                                @else
                                    <span class="text-gray-500">Quedan {{ $lote['dias_restantes'] }} días</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-500 dark:text-gray-400 text-xs">
                        <svg class="mx-auto h-8 w-8 text-gray-300 dark:text-gray-600 mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        No hay lotes por vencer pronto.
                    </div>
                @endforelse
            </div>

            <!-- Tab: Stock Bajo -->
            <div x-show="tab === 'stock'">
                @forelse($productosStockBajo as $item)
                    <div class="p-3 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition flex gap-3 items-start">
                        <div class="p-1.5 rounded-lg bg-amber-50 dark:bg-amber-950/20 text-amber-600 dark:text-amber-400 mt-0.5">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 012.008 1.24l.885 1.77a2.25 2.25 0 002.007 1.2411.177 11.177 0 012.007-1.24l.885-1.77a2.25 2.25 0 002.007-1.241h3.886m-19.5 0A2.25 2.25 0 002.25 15.75V18.75A2.25 2.25 0 004.5 21h15a2.25 2.25 0 002.25-2.25V15.75a2.25 2.25 0 00-2.25-2.25h-3.86a2.25 2.25 0 01-2.008-1.24l-.885-1.77a2.25 2.25 0 00-2.007-1.2411.177 11.177 0 01-2.007 1.24l-.885 1.77a2.25 2.25 0 00-2.007 1.24H2.25z" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold text-gray-800 dark:text-gray-200 truncate">
                                {{ $item['producto_nombre'] }}
                            </p>
                            @if($item['presentacion'])
                                <p class="text-[10px] text-gray-500 dark:text-gray-400">
                                    Presentación: {{ $item['presentacion'] }}
                                </p>
                            @endif
                            <div class="text-[10px] font-medium mt-1 flex justify-between items-center">
                                <span class="text-danger-600 dark:text-danger-400 font-semibold">Stock: {{ $item['stock'] }}</span>
                                <span class="text-gray-500">Mínimo: {{ $item['stock_minimo'] }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-500 dark:text-gray-400 text-xs">
                        <svg class="mx-auto h-8 w-8 text-gray-300 dark:text-gray-600 mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        No hay productos con stock crítico.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Footer Actions -->
        <div class="px-4 py-2.5 bg-gray-50 dark:bg-gray-800/80 border-t border-gray-100 dark:border-gray-700 flex justify-between text-[11px] font-medium">
            <a href="{{ \App\Filament\Clusters\Compras\Resources\Lotes\LoteResource::getUrl('index') }}" class="text-primary-600 dark:text-primary-400 hover:underline">Ver Lotes</a>
            <a href="{{ \App\Filament\Clusters\Almacen\Resources\StockSucursal\StockSucursalResource::getUrl('index') }}" class="text-primary-600 dark:text-primary-400 hover:underline">Ver Stock de Sucursal</a>
        </div>
    </div>
</div>
