<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 22px; }
        body { color: #0f172a; font-family: DejaVu Sans, sans-serif; font-size: 9px; }
        .header { border-bottom: 2px solid #0f172a; margin-bottom: 14px; padding-bottom: 10px; }
        .title { font-size: 20px; font-weight: 800; margin: 0; }
        .subtitle { color: #64748b; font-size: 10px; margin-top: 4px; }
        .filters { color: #475569; margin-bottom: 12px; font-size: 9px; }
        table { border-collapse: collapse; width: 100%; }
        th { background: #0f172a; color: #ffffff; font-size: 8px; padding: 6px 5px; text-align: left; text-transform: uppercase; }
        td { border-bottom: 1px solid #e2e8f0; padding: 6px 5px; vertical-align: top; }
        .right { text-align: right; }
        .badge { border-radius: 999px; font-size: 8px; font-weight: 700; padding: 2px 5px; display: inline-block; }
        .ticket { background: #f1f5f9; color: #334155; }
        .sunat { background: #dcfce7; color: #166534; }
        .badge-nc { background: #fef3c7; color: #d97706; }
        .badge-anulado { background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }
        .ref-text { color: #b45309; font-size: 8px; margin-top: 2px; }
        .totals-row { font-weight: bold; background-color: #f8fafc; border-top: 2px solid #0f172a; }
    </style>
</head>
<body>
    <div class="header">
        <p class="title">Reporte de Ventas ({{ $filtros['tipo_reporte'] }})</p>
        <div class="subtitle">
            Desde {{ $filtros['desde'] }} | Hasta {{ $filtros['hasta'] }} |
            Comprobante: {{ $filtros['filtro_comprobante'] }} | Alcance: {{ $alcance }}
        </div>
    </div>

    @if($tipoReporte === 'detalle')
        {{-- MODO DETALLADO --}}
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Comprobante</th>
                    <th>Cliente</th>
                    <th>Producto</th>
                    <th class="right">Cant</th>
                    <th class="right">P. Unit</th>
                    <th class="right">Subtotal</th>
                    <th class="right">IGV</th>
                    <th class="right">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ventas as $venta)
                    @php
                        $isNc = str_starts_with($venta->tipo_comprobante, 'NOTA_CREDITO');
                        $multiplier = $isNc ? -1 : 1;
                    @endphp
                    @foreach($venta->detalles as $detalle)
                        <tr>
                            <td>{{ $venta->fecha_emision?->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge {{ $venta->tipo_comprobante === 'TICKET' ? 'ticket' : ($isNc ? 'badge-nc' : 'sunat') }}">
                                    {{ str_replace('NOTA_CREDITO_', 'NC ', $venta->tipo_comprobante) }}
                                </span>
                                @if(!$venta->estado)
                                    <span class="badge badge-anulado">ANULADO</span>
                                @endif
                                <br>
                                {{ $venta->serie }}-{{ $venta->numero }}
                                @if($isNc && $venta->documentoReferencia)
                                    <div class="ref-text">Modifica: {{ $venta->documentoReferencia->serie_ref }}-{{ $venta->documentoReferencia->numero_ref }}</div>
                                @endif
                            </td>
                            <td>{{ $venta->cliente?->razon_social ?: ($venta->cliente?->nombre ?? 'Publico general') }}</td>
                            <td>{{ $detalle->producto_nombre }}</td>
                            <td class="right">{{ number_format(((float)$detalle->cantidad) * $multiplier, 2) }}</td>
                            <td class="right">{{ number_format((float)$detalle->precio_unitario, 2) }}</td>
                            <td class="right">{{ number_format(((float)$detalle->subtotal_neto) * $multiplier, 2) }}</td>
                            <td class="right">{{ number_format(((float)$detalle->total_igv) * $multiplier, 2) }}</td>
                            <td class="right"><strong>{{ number_format(((float)$detalle->total_linea) * $multiplier, 2) }}</strong></td>
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 22px; color: #64748b;">No hay ventas para exportar con estos filtros.</td>
                    </tr>
                @endforelse
                @if($ventas->count() > 0)
                    <tr class="totals-row">
                        <td colspan="6" class="right">TOTAL NETO RESTANDO NOTAS DE CRÉDITO:</td>
                        <td class="right">
                            @php
                                $totSub = $ventas->sum(fn ($v) => str_starts_with($v->tipo_comprobante, 'NOTA_CREDITO') ? -(float)$v->subtotal : (float)$v->subtotal);
                            @endphp
                            {{ number_format($totSub, 2) }}
                        </td>
                        <td class="right">
                            @php
                                $totIgv = $ventas->sum(fn ($v) => str_starts_with($v->tipo_comprobante, 'NOTA_CREDITO') ? -(float)$v->total_igv : (float)$v->total_igv);
                            @endphp
                            {{ number_format($totIgv, 2) }}
                        </td>
                        <td class="right">
                            @php
                                $totNet = $ventas->sum(fn ($v) => str_starts_with($v->tipo_comprobante, 'NOTA_CREDITO') ? -(float)$v->total_neto : (float)$v->total_neto);
                            @endphp
                            {{ number_format($totNet, 2) }}
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    @else
        {{-- MODO RESUMEN --}}
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Comprobante</th>
                    <th>Cliente</th>
                    <th>Sucursal</th>
                    <th>Cajero</th>
                    <th>Pago</th>
                    <th class="right">Subtotal</th>
                    <th class="right">IGV</th>
                    <th class="right">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ventas as $venta)
                    @php
                        $isNc = str_starts_with($venta->tipo_comprobante, 'NOTA_CREDITO');
                        $multiplier = $isNc ? -1 : 1;
                    @endphp
                    <tr>
                        <td>{{ $venta->fecha_emision?->format('d/m/Y') }}</td>
                        <td>
                            <span class="badge {{ $venta->tipo_comprobante === 'TICKET' ? 'ticket' : ($isNc ? 'badge-nc' : 'sunat') }}">
                                {{ str_replace('NOTA_CREDITO_', 'NC ', $venta->tipo_comprobante) }}
                            </span>
                            @if(!$venta->estado)
                                <span class="badge badge-anulado">ANULADO</span>
                            @endif
                            <br>
                            {{ $venta->serie }}-{{ $venta->numero }}
                            @if($isNc && $venta->documentoReferencia)
                                <div class="ref-text">Modifica: {{ $venta->documentoReferencia->serie_ref }}-{{ $venta->documentoReferencia->numero_ref }}</div>
                            @endif
                        </td>
                        <td>{{ $venta->cliente?->razon_social ?: ($venta->cliente?->nombre ?? 'Publico general') }}</td>
                        <td>{{ $venta->sucursal?->nombre ?? '-' }}</td>
                        <td>{{ $venta->user?->name ?? '-' }}</td>
                        <td>{{ $venta->medio_pago ?? '-' }}</td>
                        <td class="right">
                            {{ number_format(((float) $venta->subtotal) * $multiplier, 2) }}
                        </td>
                        <td class="right">
                            {{ number_format(((float) $venta->total_igv) * $multiplier, 2) }}
                        </td>
                        <td class="right">
                            <strong>{{ number_format(((float) $venta->total_neto) * $multiplier, 2) }}</strong>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 22px; color: #64748b;">No hay ventas para exportar con estos filtros.</td>
                    </tr>
                @endforelse
                @if($ventas->count() > 0)
                    <tr class="totals-row">
                        <td colspan="6" class="right">TOTAL NETO RESTANDO NOTAS DE CRÉDITO:</td>
                        <td class="right">
                            @php
                                $totSub = $ventas->sum(fn ($v) => str_starts_with($v->tipo_comprobante, 'NOTA_CREDITO') ? -(float)$v->subtotal : (float)$v->subtotal);
                            @endphp
                            {{ number_format($totSub, 2) }}
                        </td>
                        <td class="right">
                            @php
                                $totIgv = $ventas->sum(fn ($v) => str_starts_with($v->tipo_comprobante, 'NOTA_CREDITO') ? -(float)$v->total_igv : (float)$v->total_igv);
                            @endphp
                            {{ number_format($totIgv, 2) }}
                        </td>
                        <td class="right">
                            @php
                                $totNet = $ventas->sum(fn ($v) => str_starts_with($v->tipo_comprobante, 'NOTA_CREDITO') ? -(float)$v->total_neto : (float)$v->total_neto);
                            @endphp
                            {{ number_format($totNet, 2) }}
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    @endif
</body>
</html>
