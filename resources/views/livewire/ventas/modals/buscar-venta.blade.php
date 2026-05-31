{{-- Modal Buscar Ventas --}}
@if ($showBuscarVentaModal)
    <div
        wire:click.self="cerrarBuscarVentaModal"
        class="fixed inset-0 flex items-center justify-center bg-black/60 backdrop-blur-sm transition-all duration-200 p-4"
        style="z-index: 99999 !important;"
    >
        {{-- Modal Container --}}
        <div
            @click.stop
            class="{{ $selectedVentaDetalles ? 'max-w-6xl' : 'max-w-4xl' }} w-full max-h-[92vh] flex flex-col rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 overflow-hidden transition-all duration-200"
        >
            {{-- ============ HEADER ============ --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50/80 dark:bg-slate-950/50 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-black uppercase tracking-wider text-slate-800 dark:text-white">Buscar Ventas</h3>
                        <p class="text-[10px] text-slate-500 dark:text-slate-400 font-medium">Busca comprobantes emitidos en esta sucursal</p>
                    </div>
                </div>
                <button
                    type="button"
                    wire:click="cerrarBuscarVentaModal"
                    class="flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-red-500 dark:text-slate-500 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 transition"
                    title="Cerrar"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- ============ BODY: Split Grid ============ --}}
            <div class="grid {{ $selectedVentaDetalles ? 'grid-cols-12' : '' }} flex-1 overflow-hidden min-h-0">

                {{-- LEFT: Search + Results --}}
                <div class="{{ $selectedVentaDetalles ? 'col-span-12 md:col-span-7 border-r border-slate-200 dark:border-slate-700' : 'col-span-full' }} flex flex-col overflow-hidden">

                    {{-- Search Bar --}}
                    <div class="px-5 pt-4 pb-2 shrink-0">
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </span>
                            <input
                                type="text"
                                wire:model.live="searchVentaQuery"
                                class="w-full text-sm font-medium text-slate-800 dark:text-white bg-slate-100 dark:bg-slate-800 border-2 border-transparent focus:border-blue-500 dark:focus:border-blue-400 focus:ring-0 focus:outline-none rounded-xl py-2.5 pl-10 pr-10 transition placeholder:text-slate-400 dark:placeholder:text-slate-500"
                                placeholder="Buscar por serie, correlativo, cliente (DNI/RUC/Nombre)..."
                                autofocus
                            />
                            {{-- Clear button --}}
                            @if ($searchVentaQuery)
                                <button
                                    type="button"
                                    wire:click="$set('searchVentaQuery', '')"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition"
                                    title="Limpiar búsqueda"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            @endif
                        </div>
                        <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1.5 ml-1 font-medium">
                            Escribe para buscar — la búsqueda se borra al limpiar el campo
                        </p>
                    </div>

                    {{-- Results --}}
                    <div class="flex-1 overflow-y-auto px-5 pb-4">
                        @if ($searchVentaQuery === '' || $searchVentaQuery === null)
                            {{-- Empty state: no search yet --}}
                            <div class="flex flex-col items-center justify-center py-16 text-center">
                                <div class="w-16 h-16 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-4">
                                    <svg class="w-8 h-8 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                                <p class="text-sm font-semibold text-slate-400 dark:text-slate-500">Ingresa un término de búsqueda</p>
                                <p class="text-xs text-slate-350 dark:text-slate-600 mt-1">Serie, correlativo o datos del cliente</p>
                            </div>
                        @elseif (empty($ventasResultados))
                            {{-- No results --}}
                            <div class="flex flex-col items-center justify-center py-16 text-center">
                                <div class="w-16 h-16 rounded-2xl bg-amber-50 dark:bg-amber-500/10 flex items-center justify-center mb-4">
                                    <svg class="w-8 h-8 text-amber-400 dark:text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Sin resultados</p>
                                <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">No se encontraron documentos con "{{ $searchVentaQuery }}"</p>
                            </div>
                        @else
                            {{-- Results List --}}
                            <div class="space-y-1.5">
                                @foreach ($ventasResultados as $venta)
                                    <div
                                        wire:key="venta-{{ $venta['id'] }}"
                                        class="group flex items-center gap-3 px-3 py-2.5 rounded-xl border transition cursor-pointer
                                            {{ $selectedVentaId === $venta['id']
                                                ? 'border-blue-300 dark:border-blue-600 bg-blue-50/80 dark:bg-blue-500/10 shadow-sm'
                                                : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800/50'
                                            }}"
                                        wire:click="verDetalleVenta({{ $venta['id'] }})"
                                    >
                                        {{-- Estado indicator dot --}}
                                        <div class="shrink-0">
                                            @if ($venta['estado'])
                                                <div class="w-2.5 h-2.5 rounded-full bg-emerald-400 dark:bg-emerald-500 shadow-sm shadow-emerald-500/30" title="Válida"></div>
                                            @else
                                                <div class="w-2.5 h-2.5 rounded-full bg-rose-400 dark:bg-rose-500 shadow-sm shadow-rose-500/30" title="Anulada"></div>
                                            @endif
                                        </div>

                                        {{-- Comprobante info --}}
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2">
                                                <span class="text-xs font-black text-slate-800 dark:text-white tracking-tight">
                                                    {{ $venta['comprobante'] }}
                                                </span>
                                                <span class="text-[10px] px-1.5 py-0.5 rounded font-bold uppercase tracking-wider
                                                    {{ $venta['estado']
                                                        ? 'bg-emerald-100 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-400'
                                                        : 'bg-rose-100 dark:bg-rose-500/15 text-rose-700 dark:text-rose-400'
                                                    }}">
                                                    {{ $venta['estado'] ? 'Válida' : 'Anulada' }}
                                                </span>
                                            </div>
                                            <div class="flex items-center gap-3 mt-0.5 text-[10px] text-slate-500 dark:text-slate-400 font-medium">
                                                <span class="truncate max-w-[180px]">{{ $venta['cliente'] }}</span>
                                                @if ($venta['cliente_documento'])
                                                    <span class="font-mono text-slate-400 dark:text-slate-500">{{ $venta['cliente_documento'] }}</span>
                                                @endif
                                                <span class="font-mono">{{ $venta['fecha'] }}</span>
                                            </div>
                                        </div>

                                        {{-- Total --}}
                                        <div class="shrink-0 text-right mr-1">
                                            <span class="text-sm font-black text-slate-900 dark:text-white font-mono tracking-tight">
                                                S/ {{ number_format($venta['total'], 2) }}
                                            </span>
                                        </div>

                                        {{-- Actions --}}
                                        <div class="shrink-0 flex items-center gap-1" @click.stop>
                                            {{-- Ver detalle (highlighted when selected) --}}
                                            <button
                                                type="button"
                                                wire:click="verDetalleVenta({{ $venta['id'] }})"
                                                class="p-2 rounded-lg transition
                                                    {{ $selectedVentaId === $venta['id']
                                                        ? 'bg-blue-600 text-white'
                                                        : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-blue-100 dark:hover:bg-blue-500/20 hover:text-blue-600 dark:hover:text-blue-400'
                                                    }}"
                                                title="Ver Detalle"
                                            >
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </button>

                                            {{-- Ticket --}}
                                            <a
                                                href="{{ route('filament.documentos.ticket', ['documento' => $venta['id']]) }}"
                                                target="_blank"
                                                class="p-2 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700 hover:text-slate-700 dark:hover:text-slate-200 transition"
                                                title="Imprimir Ticket"
                                            >
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                                </svg>
                                            </a>

                                            {{-- PDF A4 --}}
                                            <a
                                                href="{{ route('filament.documentos.pdf', ['documento' => $venta['id']]) }}"
                                                target="_blank"
                                                class="p-2 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700 hover:text-slate-700 dark:hover:text-slate-200 transition"
                                                title="PDF A4"
                                            >
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                </svg>
                                            </a>

                                            {{-- Anular (only for active documents) --}}
                                            @if ($venta['estado'])
                                                <button
                                                    type="button"
                                                    wire:click="confirmarAnularVenta({{ $venta['id'] }})"
                                                    class="p-2 rounded-lg bg-rose-50 dark:bg-rose-500/10 text-rose-500 dark:text-rose-400 hover:bg-rose-100 dark:hover:bg-rose-500/20 hover:text-rose-600 dark:hover:text-rose-300 transition"
                                                    title="Anular esta venta"
                                                >
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                {{-- RIGHT: Detail Panel --}}
                @if ($selectedVentaDetalles)
                    <div class="col-span-12 md:col-span-5 flex flex-col bg-slate-50/60 dark:bg-slate-950/30 overflow-y-auto border-t md:border-t-0 border-slate-200 dark:border-slate-700">
                        {{-- Detail Header --}}
                        <div class="flex items-center justify-between px-5 pt-5 pb-3">
                            <h4 class="text-xs font-black uppercase tracking-wider text-slate-800 dark:text-white">
                                Detalle del Comprobante
                            </h4>
                            <span class="text-[10px] font-bold px-2.5 py-1 rounded-full border
                                {{ $selectedVentaDetalles['estado'] ?? true
                                    ? 'text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/10 border-emerald-200 dark:border-emerald-500/20'
                                    : 'text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-500/10 border-rose-200 dark:border-rose-500/20'
                                }}">
                                {{ $selectedVentaDetalles['comprobante'] }}
                            </span>
                        </div>

                        {{-- Metadata --}}
                        <div class="px-5 space-y-2.5 mb-4">
                            <div class="grid grid-cols-2 gap-3">
                                <div class="bg-white dark:bg-slate-800/60 rounded-xl p-2.5 border border-slate-200 dark:border-slate-700">
                                    <span class="block text-[9px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider">Fecha / Hora</span>
                                    <span class="text-xs font-bold text-slate-800 dark:text-white">{{ $selectedVentaDetalles['fecha'] }} — {{ $selectedVentaDetalles['hora'] }}</span>
                                </div>
                                <div class="bg-white dark:bg-slate-800/60 rounded-xl p-2.5 border border-slate-200 dark:border-slate-700">
                                    <span class="block text-[9px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider">Medio de Pago</span>
                                    <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">{{ $selectedVentaDetalles['medio_pago'] }}</span>
                                </div>
                            </div>
                            <div class="bg-white dark:bg-slate-800/60 rounded-xl p-2.5 border border-slate-200 dark:border-slate-700">
                                <span class="block text-[9px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider">Cliente</span>
                                <span class="text-xs font-bold text-slate-800 dark:text-white block truncate">{{ $selectedVentaDetalles['cliente'] }}</span>
                                @if ($selectedVentaDetalles['cliente_documento'])
                                    <span class="text-[10px] text-slate-500 dark:text-slate-400 font-mono">{{ $selectedVentaDetalles['cliente_documento'] }}</span>
                                @endif
                                @if ($selectedVentaDetalles['cliente_direccion'])
                                    <span class="block text-[10px] text-slate-450 dark:text-slate-500 mt-0.5 truncate">{{ $selectedVentaDetalles['cliente_direccion'] }}</span>
                                @endif
                            </div>
                        </div>

                        {{-- Items Table --}}
                        <div class="flex-1 px-5 min-h-[120px] mb-3">
                            <div class="rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                                <table class="w-full text-left text-xs">
                                    <thead>
                                        <tr class="bg-slate-100 dark:bg-slate-800/80 text-slate-500 dark:text-slate-400 font-extrabold uppercase text-[9px] tracking-wider">
                                            <th class="px-3 py-2.5">Producto</th>
                                            <th class="px-3 py-2.5 text-center">Cant.</th>
                                            <th class="px-3 py-2.5 text-right">P. Unit.</th>
                                            <th class="px-3 py-2.5 text-right">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                        @foreach ($selectedVentaDetalles['items'] as $item)
                                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition">
                                                <td class="px-3 py-2.5">
                                                    <span class="block font-bold text-slate-800 dark:text-white text-[11px]">{{ $item['producto_nombre'] }}</span>
                                                    <span class="block text-[9px] text-slate-450 dark:text-slate-500">{{ $item['presentacion'] }} ({{ $item['unidad'] }})</span>
                                                </td>
                                                <td class="px-3 py-2.5 text-center font-mono text-[11px] font-semibold text-slate-700 dark:text-slate-300">
                                                    {{ number_format($item['cantidad'], $item['unidad'] === 'und' ? 0 : 3) }}
                                                </td>
                                                <td class="px-3 py-2.5 text-right font-mono text-[11px] text-slate-600 dark:text-slate-400">
                                                    S/ {{ number_format($item['precio_unitario'], 2) }}
                                                </td>
                                                <td class="px-3 py-2.5 text-right font-mono text-[11px] font-bold text-slate-900 dark:text-white">
                                                    S/ {{ number_format($item['subtotal'], 2) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Financial Summary --}}
                        <div class="px-5 pb-5">
                            <div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/60 p-3.5 space-y-2 text-xs">
                                <div class="flex justify-between font-semibold text-slate-500 dark:text-slate-400">
                                    <span>Subtotal</span>
                                    <span class="font-mono">S/ {{ number_format($selectedVentaDetalles['subtotal'], 2) }}</span>
                                </div>
                                @if ($selectedVentaDetalles['total_descuento'] > 0)
                                    <div class="flex justify-between font-semibold text-rose-500">
                                        <span>Descuento</span>
                                        <span class="font-mono">- S/ {{ number_format($selectedVentaDetalles['total_descuento'], 2) }}</span>
                                    </div>
                                @endif
                                <div class="flex justify-between font-semibold text-slate-500 dark:text-slate-400">
                                    <span>IGV</span>
                                    <span class="font-mono">S/ {{ number_format($selectedVentaDetalles['total_igv'], 2) }}</span>
                                </div>
                                <div class="flex justify-between items-baseline pt-2 border-t border-slate-200 dark:border-slate-700 font-extrabold text-slate-800 dark:text-white">
                                    <span class="text-xs uppercase tracking-wide">Total Venta</span>
                                    <span class="text-base font-black text-amber-500 dark:text-amber-400 font-mono">S/ {{ number_format($selectedVentaDetalles['total'], 2) }}</span>
                                </div>
                                @if ($selectedVentaDetalles['medio_pago'] === 'EFECTIVO')
                                    <div class="flex justify-between text-[11px] font-semibold text-slate-500 dark:text-slate-400">
                                        <span>Monto Recibido</span>
                                        <span class="font-mono">S/ {{ number_format($selectedVentaDetalles['monto_recibido'], 2) }}</span>
                                    </div>
                                    <div class="flex justify-between text-[11px] font-semibold text-slate-500 dark:text-slate-400">
                                        <span>Vuelto</span>
                                        <span class="font-mono text-emerald-500 dark:text-emerald-400 font-bold">S/ {{ number_format($selectedVentaDetalles['vuelto'], 2) }}</span>
                                    </div>
                                @endif
                                @if ($selectedVentaDetalles['referencia_pago'])
                                    <div class="mt-2 p-2.5 rounded-xl bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-[10px]">
                                        <span class="block font-bold text-slate-450 dark:text-slate-500 uppercase">Referencia de Pago</span>
                                        <span class="block font-semibold text-slate-800 dark:text-slate-200 mt-0.5">{{ $selectedVentaDetalles['referencia_pago'] }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- ============ FOOTER ============ --}}
            <div class="flex items-center justify-between px-5 py-3.5 border-t border-slate-200 dark:border-slate-700 bg-slate-50/80 dark:bg-slate-950/50 shrink-0 rounded-b-2xl">
                <span class="text-[10px] text-slate-400 dark:text-slate-500 font-medium">
                    @if ($searchVentaQuery && !empty($ventasResultados))
                        {{ count($ventasResultados) }} resultado(s)
                    @endif
                </span>
                <button
                    type="button"
                    wire:click="cerrarBuscarVentaModal"
                    class="px-5 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold text-xs rounded-xl transition shadow-sm"
                >
                    Cerrar
                </button>
            </div>
        </div>
    </div>

    {{-- ============ ANULAR CONFIRMATION MODAL ============ --}}
    @if ($showAnularVentaModal)
        <div
            wire:click.self="cerrarAnularVentaModal"
            class="fixed inset-0 flex items-center justify-center bg-black/70 backdrop-blur-sm transition-all duration-200 p-4"
            style="z-index: 100001 !important;"
        >
            <div @click.stop class="w-full max-w-md rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 overflow-hidden">
                {{-- Header --}}
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-rose-50/80 dark:bg-rose-500/10">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-rose-500/10 text-rose-600 dark:text-rose-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-black uppercase tracking-wider text-rose-700 dark:text-rose-400">Confirmar Anulación</h3>
                            <p class="text-[10px] text-rose-600/70 dark:text-rose-400/70 font-medium">Esta acción no se puede deshacer</p>
                        </div>
                    </div>
                </div>

                {{-- Body --}}
                <div class="px-6 py-4 space-y-4">
                    {{-- Comprobante info --}}
                    <div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 p-3.5">
                        <span class="block text-[9px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-1">Comprobante a anular</span>
                        <span class="text-sm font-black text-slate-800 dark:text-white">{{ $anularVentaComprobante }}</span>
                    </div>

                    {{-- Motivo Select --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-2 uppercase tracking-wider">
                            Motivo de Anulación
                        </label>
                        <select
                            wire:model="anularMotivoCodigo"
                            class="w-full text-sm font-medium rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-white focus:border-rose-500 dark:focus:border-rose-400 focus:ring-0 focus:outline-none px-4 py-2.5 transition"
                        >
                            @foreach (\App\Support\Ventas\AnulacionService::MOTIVOS as $codigo => $descripcion)
                                <option value="{{ $codigo }}">{{ $codigo }} — {{ $descripcion }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Footer Actions --}}
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50/80 dark:bg-slate-950/50 flex justify-end gap-3">
                    <button
                        type="button"
                        wire:click="cerrarAnularVentaModal"
                        class="px-5 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold text-xs rounded-xl transition shadow-sm"
                    >
                        Cancelar
                    </button>
                    <button
                        type="button"
                        wire:click="anularVenta"
                        class="px-5 py-2.5 bg-rose-600 hover:bg-rose-500 dark:bg-rose-600 dark:hover:bg-rose-500 text-white font-bold text-xs rounded-xl transition shadow-sm shadow-rose-500/20"
                    >
                        Confirmar Anulación
                    </button>
                </div>
            </div>
        </div>
    @endif
@endif
