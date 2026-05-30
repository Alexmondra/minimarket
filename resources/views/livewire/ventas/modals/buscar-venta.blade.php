@if ($showBuscarVentaModal)
    <div wire:click.self="cerrarBuscarVentaModal" class="fixed inset-0 flex items-center justify-center bg-slate-950/70 backdrop-blur-md transition-all duration-300" style="z-index: 99999 !important;">
        <!-- Modal Container: will grow to 6xl to support split screen when detail is shown, otherwise 4xl -->
        <div class="pos-card max-h-[90vh] {{ $selectedVentaDetalles ? 'max-w-6xl' : 'max-w-4xl' }} w-full mx-4 flex flex-col shadow-2xl border pos-border bg-white/95 dark:bg-slate-900/95 rounded-2xl text-slate-800 dark:text-slate-200 transition-all duration-300">
            
            <!-- Header -->
            <div class="flex items-center justify-between border-b pos-border p-5">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-xl bg-blue-500/10 text-blue-500">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-black uppercase tracking-wider pos-text text-slate-800 dark:text-white">Buscar Ventas</h3>
                        <p class="text-[10px] pos-text-muted text-slate-500 dark:text-slate-400">Busca comprobantes de venta emitidos en esta sucursal.</p>
                    </div>
                </div>
                <button type="button" wire:click="cerrarBuscarVentaModal" class="pos-text-muted hover:text-rose-500 transition font-bold text-lg p-1">&times;</button>
            </div>

            <!-- Content Area (Split Grid if details are shown) -->
            <div class="grid grid-cols-12 overflow-hidden flex-1">
                
                <!-- Left Column: Search & Results List -->
                <div class="{{ $selectedVentaDetalles ? 'col-span-12 md:col-span-7 border-r pos-border' : 'col-span-12' }} flex flex-col p-5 overflow-y-auto max-h-[65vh]">
                    
                    <!-- Search Input -->
                    <div class="mb-4 relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </span>
                        <input 
                            type="text" 
                            wire:model.live.debounce.300ms="searchVentaQuery"
                            class="w-full text-xs font-semibold tracking-tight text-slate-850 dark:text-white bg-slate-50 dark:bg-slate-950/40 border border-slate-200 dark:border-slate-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none rounded-xl py-2.5 pl-10 pr-4 transition shadow-inner" 
                            placeholder="Buscar por Serie-Correlativo, Cliente (DNI/RUC/Nombre)..."
                            autofocus
                        >
                    </div>

                    <!-- Results Table/List -->
                    <div class="flex-1 overflow-x-auto">
                        @if (empty($ventasResultados))
                            <div class="flex flex-col items-center justify-center py-12 text-center text-slate-400 dark:text-slate-500">
                                <span class="text-3xl mb-3">🔍</span>
                                <p class="text-xs font-semibold">Ingrese un término de búsqueda para comenzar.</p>
                                <p class="text-[10px] opacity-75 mt-1">Escriba serie, correlativo o datos del cliente.</p>
                            </div>
                        @else
                            <table class="w-full text-left text-xs divide-y pos-divide border pos-border rounded-xl overflow-hidden">
                                <thead class="bg-slate-50 dark:bg-slate-950/20 text-slate-550 dark:text-slate-400 font-extrabold uppercase text-[9px] tracking-wider">
                                    <tr>
                                        <th class="px-3 py-3">Comprobante</th>
                                        <th class="px-3 py-3">Cliente</th>
                                        <th class="px-3 py-3">Fecha</th>
                                        <th class="px-3 py-3 text-right">Total</th>
                                        <th class="px-3 py-3 text-center">Estado</th>
                                        <th class="px-3 py-3 text-right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y pos-divide font-medium text-slate-700 dark:text-slate-300">
                                    @foreach ($ventasResultados as $venta)
                                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition {{ $selectedVentaId === $venta['id'] ? 'bg-blue-500/5 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400' : '' }}">
                                            <td class="px-3 py-3.5 font-bold tracking-tight">
                                                {{ $venta['comprobante'] }}
                                            </td>
                                            <td class="px-3 py-3.5 max-w-[150px] truncate">
                                                <span class="block font-bold">{{ $venta['cliente'] }}</span>
                                                @if ($venta['cliente_documento'])
                                                    <span class="block text-[9px] text-slate-450 dark:text-slate-500 font-mono">{{ $venta['cliente_documento'] }}</span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-3.5 text-[11px] font-semibold text-slate-500 dark:text-slate-400 font-mono">
                                                {{ $venta['fecha'] }}
                                            </td>
                                            <td class="px-3 py-3.5 text-right font-black text-slate-900 dark:text-white font-mono">
                                                S/ {{ number_format($venta['total'], 2) }}
                                            </td>
                                            <td class="px-3 py-3.5 text-center">
                                                @if ($venta['estado'])
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-black bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 uppercase tracking-wide border border-emerald-500/20">
                                                        Válida
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-black bg-rose-500/10 text-rose-600 dark:text-rose-400 uppercase tracking-wide border border-rose-500/20">
                                                        Anulada
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-3.5 text-right space-x-1 whitespace-nowrap">
                                                <!-- Ver Detalle -->
                                                <button 
                                                    type="button"
                                                    wire:click="verDetalleVenta({{ $venta['id'] }})"
                                                    class="p-1.5 bg-blue-600 hover:bg-blue-500 text-white rounded-lg transition inline-flex items-center justify-center"
                                                    title="Ver Detalle"
                                                >
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                </button>
                                                <!-- Ticket PDF -->
                                                <a 
                                                    href="{{ route('filament.documentos.ticket', ['documento' => $venta['id']]) }}" 
                                                    target="_blank"
                                                    class="p-1.5 border pos-border hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-350 rounded-lg transition inline-flex items-center justify-center"
                                                    title="Imprimir Ticket"
                                                >
                                                    🖨️
                                                </a>
                                                <!-- PDF A4 -->
                                                <a 
                                                    href="{{ route('filament.documentos.pdf', ['documento' => $venta['id']]) }}" 
                                                    target="_blank"
                                                    class="p-1.5 border pos-border hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-350 rounded-lg transition inline-flex items-center justify-center"
                                                    title="PDF A4"
                                                >
                                                    📄
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>

                <!-- Right Column: Selected Venta Details Panel -->
                @if ($selectedVentaDetalles)
                    <div class="col-span-12 md:col-span-5 flex flex-col p-5 bg-slate-50/40 dark:bg-slate-950/20 overflow-y-auto max-h-[65vh] border-t md:border-t-0">
                        <div class="flex items-center justify-between border-b pos-border pb-3 mb-4">
                            <h4 class="text-xs font-black uppercase tracking-wider text-slate-800 dark:text-white">Detalle de Comprobante</h4>
                            <span class="text-[10px] font-bold text-blue-600 dark:text-blue-400 bg-blue-500/10 px-2 py-0.5 rounded-full border border-blue-500/20">
                                {{ $selectedVentaDetalles['comprobante'] }}
                            </span>
                        </div>

                        <!-- Info Metadata Block -->
                        <div class="grid grid-cols-2 gap-3 text-xs mb-4">
                            <div>
                                <span class="block text-[9px] font-bold text-slate-455 dark:text-slate-500 uppercase">Fecha / Hora</span>
                                <span class="font-bold pos-text">{{ $selectedVentaDetalles['fecha'] }} - {{ $selectedVentaDetalles['hora'] }}</span>
                            </div>
                            <div>
                                <span class="block text-[9px] font-bold text-slate-455 dark:text-slate-500 uppercase">Medio de Pago</span>
                                <span class="font-bold pos-text text-emerald-600 dark:text-emerald-400">{{ $selectedVentaDetalles['medio_pago'] }}</span>
                            </div>
                            <div class="col-span-2">
                                <span class="block text-[9px] font-bold text-slate-455 dark:text-slate-500 uppercase">Cliente</span>
                                <span class="font-bold pos-text block truncate">{{ $selectedVentaDetalles['cliente'] }}</span>
                                @if ($selectedVentaDetalles['cliente_documento'])
                                    <span class="text-[10px] text-slate-500 font-mono">{{ $selectedVentaDetalles['cliente_documento'] }}</span>
                                @endif
                                @if ($selectedVentaDetalles['cliente_direccion'])
                                    <span class="block text-[9px] text-slate-450 dark:text-slate-500 mt-0.5 truncate">{{ $selectedVentaDetalles['cliente_direccion'] }}</span>
                                @endif
                            </div>
                        </div>

                        <!-- Table of items -->
                        <div class="flex-1 min-h-[150px] mb-4">
                            <table class="w-full text-left text-xs divide-y pos-divide">
                                <thead class="text-slate-400 font-extrabold uppercase text-[8px] tracking-wider">
                                    <tr>
                                        <th class="pb-2">Producto</th>
                                        <th class="pb-2 text-center">Cant.</th>
                                        <th class="pb-2 text-right">P. Unit.</th>
                                        <th class="pb-2 text-right">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y pos-divide font-medium text-slate-700 dark:text-slate-350">
                                    @foreach ($selectedVentaDetalles['items'] as $item)
                                        <tr class="py-2">
                                            <td class="py-2 pr-2">
                                                <span class="block font-bold text-slate-800 dark:text-white">{{ $item['producto_nombre'] }}</span>
                                                <span class="block text-[9px] text-slate-450 dark:text-slate-500">{{ $item['presentacion'] }} ({{ $item['unidad'] }})</span>
                                            </td>
                                            <td class="py-2 text-center font-mono">
                                                {{ number_format($item['cantidad'], $item['unidad'] === 'und' ? 0 : 3) }}
                                            </td>
                                            <td class="py-2 text-right font-mono">
                                                S/ {{ number_format($item['precio_unitario'], 2) }}
                                            </td>
                                            <td class="py-2 text-right font-mono font-bold text-slate-900 dark:text-white">
                                                S/ {{ number_format($item['subtotal'], 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Financial summary -->
                        <div class="border-t pos-border pt-3 space-y-1.5 text-xs">
                            <div class="flex justify-between font-bold text-slate-500 dark:text-slate-400">
                                <span>Subtotal</span>
                                <span class="font-mono">S/ {{ number_format($selectedVentaDetalles['subtotal'], 2) }}</span>
                            </div>
                            @if ($selectedVentaDetalles['total_descuento'] > 0)
                                <div class="flex justify-between font-bold text-rose-500">
                                    <span>Descuento</span>
                                    <span class="font-mono">- S/ {{ number_format($selectedVentaDetalles['total_descuento'], 2) }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between font-bold text-slate-500 dark:text-slate-400">
                                <span>IGV</span>
                                <span class="font-mono">S/ {{ number_format($selectedVentaDetalles['total_igv'], 2) }}</span>
                            </div>
                            <div class="flex justify-between items-baseline pt-2 border-t pos-border font-extrabold text-slate-800 dark:text-white">
                                <span class="text-xs uppercase tracking-wide">Total Venta</span>
                                <span class="text-lg font-black text-amber-500 font-mono">S/ {{ number_format($selectedVentaDetalles['total'], 2) }}</span>
                            </div>
                            @if ($selectedVentaDetalles['medio_pago'] === 'EFECTIVO')
                                <div class="flex justify-between text-[11px] font-bold text-slate-500 dark:text-slate-400 pt-1">
                                    <span>Monto Recibido</span>
                                    <span class="font-mono">S/ {{ number_format($selectedVentaDetalles['monto_recibido'], 2) }}</span>
                                </div>
                                <div class="flex justify-between text-[11px] font-bold text-slate-500 dark:text-slate-400">
                                    <span>Vuelto</span>
                                    <span class="font-mono text-emerald-500 font-bold">S/ {{ number_format($selectedVentaDetalles['vuelto'], 2) }}</span>
                                </div>
                            @endif

                            @if ($selectedVentaDetalles['puntos_ganados'] > 0 || $selectedVentaDetalles['puntos_canjeados'] > 0)
                                <div class="bg-blue-500/5 dark:bg-blue-500/10 border border-blue-500/20 rounded-xl p-2.5 mt-2 space-y-1">
                                    <div class="flex justify-between text-[10px] font-bold text-blue-600 dark:text-blue-400">
                                        <span>🌟 Puntos Ganados</span>
                                        <span>+{{ $selectedVentaDetalles['puntos_ganados'] }} pts</span>
                                    </div>
                                    @if ($selectedVentaDetalles['puntos_canjeados'] > 0)
                                        <div class="flex justify-between text-[10px] font-bold text-amber-600 dark:text-amber-400">
                                            <span>🎫 Puntos Canjeados</span>
                                            <span>-{{ $selectedVentaDetalles['puntos_canjeados'] }} pts</span>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            @if ($selectedVentaDetalles['referencia_pago'])
                                <div class="bg-slate-100 dark:bg-slate-900 border pos-border rounded-xl p-2.5 mt-2 text-[10px]">
                                    <span class="block font-bold text-slate-450 dark:text-slate-500 uppercase">Referencia de Pago</span>
                                    <span class="block font-semibold pos-text mt-0.5">{{ $selectedVentaDetalles['referencia_pago'] }}</span>
                                </div>
                            @endif

                            @if ($selectedVentaDetalles['observaciones'])
                                <div class="bg-slate-100 dark:bg-slate-900 border pos-border rounded-xl p-2.5 mt-2 text-[10px]">
                                    <span class="block font-bold text-slate-455 dark:text-slate-500 uppercase">Observaciones</span>
                                    <p class="font-semibold pos-text mt-0.5 whitespace-pre-wrap leading-normal">{{ $selectedVentaDetalles['observaciones'] }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Footer -->
            <div class="flex justify-end gap-3 border-t pos-border p-5 bg-slate-50 dark:bg-slate-950/20 rounded-b-2xl">
                <button 
                    type="button" 
                    wire:click="cerrarBuscarVentaModal"
                    class="px-5 py-2 bg-slate-105 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-350 font-bold text-xs rounded-xl transition"
                >
                    Cerrar
                </button>
            </div>

        </div>
    </div>
@endif
