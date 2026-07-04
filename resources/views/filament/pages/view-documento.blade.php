<x-filament-panels::page>
@php($documento = $this->getDocumento())

<div class="space-y-6">
    <div class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
        <section class="space-y-5">
            <div class="overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-sm">
                <div class="border-b border-stone-100 px-6 py-5">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.24em] text-stone-500">{{ $documento->tipo_comprobante }}</p>
                            <h1 class="mt-2 text-3xl font-black text-stone-900">{{ $documento->serie }}-{{ $documento->numero }}</h1>
                        </div>
                        <span class="rounded-full px-4 py-2 text-xs font-black uppercase tracking-[0.24em] {{ $documento->estado ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                            {{ $documento->estado ? 'Emitido' : 'Anulado' }}
                        </span>
                    </div>
                </div>
                <div class="grid gap-4 px-6 py-5 md:grid-cols-2">
                    <div class="rounded-2xl border border-stone-200 bg-stone-50 p-4">
                        <p class="text-xs font-black uppercase tracking-[0.24em] text-stone-500">Empresa</p>
                        <p class="mt-2 text-lg font-black text-stone-900">{{ $documento->empresa?->razon_social }}</p>
                        <p class="text-sm text-stone-600">RUC {{ $documento->empresa?->ruc }}</p>
                    </div>
                    <div class="rounded-2xl border border-stone-200 bg-stone-50 p-4">
                        <p class="text-xs font-black uppercase tracking-[0.24em] text-stone-500">Sucursal</p>
                        <p class="mt-2 text-lg font-black text-stone-900">{{ $documento->sucursal?->nombre_sucursal }}</p>
                        <p class="text-sm text-stone-600">{{ $documento->sucursal?->direccion }}</p>
                    </div>
                    <div class="rounded-2xl border border-stone-200 bg-stone-50 p-4">
                        <p class="text-xs font-black uppercase tracking-[0.24em] text-stone-500">Cliente</p>
                        <p class="mt-2 text-lg font-black text-stone-900">{{ $documento->cliente && $documento->cliente->documento !== '00000000' ? ($documento->cliente->razon_social ?: trim(($documento->cliente->nombre ?? '') . ' ' . ($documento->cliente->apellido ?? ''))) : 'PÚBLICO EN GENERAL' }}</p>
                        @if($documento->cliente && $documento->cliente->documento !== '00000000')
                            <p class="text-sm text-stone-600">{{ $documento->cliente->tipo_documento }} {{ $documento->cliente->documento }}</p>
                        @endif
                    </div>
                    <div class="rounded-2xl border border-stone-200 bg-stone-50 p-4">
                        <p class="text-xs font-black uppercase tracking-[0.24em] text-stone-500">Caja</p>
                        <p class="mt-2 text-lg font-black text-stone-900">Sesion #{{ $documento->caja_sesion_id }}</p>
                        <p class="text-sm text-stone-600">Atendio {{ $documento->user?->name }}</p>
                    </div>
                </div>
            </div>

            <div class="overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-sm">
                <div class="border-b border-stone-100 px-6 py-4">
                    <h2 class="text-sm font-black uppercase tracking-[0.24em] text-stone-500">Detalle</h2>
                </div>
                <div class="divide-y divide-stone-100">
                    @foreach($documento->detalles as $detalle)
                        <div class="grid gap-4 px-6 py-4 md:grid-cols-[1.5fr_0.6fr_0.6fr_0.6fr]">
                            <div>
                                <p class="text-sm font-black text-stone-900">{{ $detalle->producto_nombre }}</p>
                                <p class="text-xs text-stone-500">{{ $detalle->presentacion?->tipo_presentacion }} {{ $detalle->presentacion?->unidadMedida?->abreviatura }}</p>
                            </div>
                            <div>
                                <p class="text-[11px] font-black uppercase tracking-wide text-stone-400">Cantidad</p>
                                <p class="text-sm font-bold text-stone-900">{{ number_format((float) $detalle->cantidad, 3) }}</p>
                            </div>
                            <div>
                                <p class="text-[11px] font-black uppercase tracking-wide text-stone-400">P. Unitario</p>
                                <p class="text-sm font-bold text-stone-900">S/ {{ number_format((float) $detalle->precio_unitario, 2) }}</p>
                            </div>
                            <div>
                                <p class="text-[11px] font-black uppercase tracking-wide text-stone-400">Total</p>
                                <p class="text-sm font-bold text-stone-900">S/ {{ number_format((float) $detalle->total_linea, 2) }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <aside class="space-y-5">
            <div class="overflow-hidden rounded-3xl border border-stone-900 bg-stone-950 text-white shadow-2xl shadow-stone-950/20">
                <div class="border-b border-white/10 px-6 py-4">
                    <h2 class="text-sm font-black uppercase tracking-[0.24em] text-amber-300">Totales</h2>
                </div>
                <div class="space-y-4 px-6 py-5">
                    <div class="flex justify-between text-sm"><span class="text-white/60">Bruto</span><span class="font-bold">S/ {{ number_format((float) $documento->total_bruto, 2) }}</span></div>
                    <div class="flex justify-between text-sm"><span class="text-white/60">Descuento</span><span class="font-bold">S/ {{ number_format((float) $documento->total_descuento, 2) }}</span></div>
                    <div class="flex justify-between text-sm"><span class="text-white/60">Base</span><span class="font-bold">S/ {{ number_format((float) $documento->subtotal, 2) }}</span></div>
                    <div class="flex justify-between text-sm"><span class="text-white/60">IGV</span><span class="font-bold">S/ {{ number_format((float) $documento->total_igv, 2) }}</span></div>
                    <div class="rounded-2xl bg-white/5 p-4">
                        <p class="text-xs font-black uppercase tracking-[0.24em] text-white/50">Total neto</p>
                        <p class="mt-2 text-4xl font-black italic text-amber-300">S/ {{ number_format((float) $documento->total_neto, 2) }}</p>
                    </div>
                    <div class="flex justify-between text-sm"><span class="text-white/60">Recibido</span><span class="font-bold">S/ {{ number_format((float) $documento->monto_recibido, 2) }}</span></div>
                    <div class="flex justify-between text-sm"><span class="text-white/60">Vuelto</span><span class="font-bold">S/ {{ number_format((float) $documento->vuelto, 2) }}</span></div>
                    <div class="flex justify-between text-sm"><span class="text-white/60">Puntos ganados</span><span class="font-bold">{{ $documento->puntos_ganados }}</span></div>
                    <div class="flex justify-between text-sm"><span class="text-white/60">Puntos canjeados</span><span class="font-bold">{{ $documento->puntos_canjeados }}</span></div>
                </div>
            </div>

            <div class="rounded-3xl border border-stone-200 bg-white p-5 shadow-sm">
                <h3 class="text-sm font-black uppercase tracking-[0.24em] text-stone-500">SUNAT</h3>
                <p class="mt-3 text-sm font-semibold text-stone-800">{{ $documento->sunat?->mensaje_sunat ?: 'Sin registro SUNAT' }}</p>
            </div>
        </aside>
    </div>
</div>
</x-filament-panels::page>
