<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        table { border-collapse: collapse; width: 100%; }
        th { background: #0f172a; color: #ffffff; font-weight: 700; }
        th, td { border: 1px solid #cbd5e1; padding: 8px; font-family: Arial, sans-serif; font-size: 12px; }
        .title { font-size: 20px; font-weight: 800; color: #0f172a; }
        .muted { color: #64748b; }
        .money { mso-number-format: "0.00"; }
    </style>
</head>
<body>
    <table>
        <tr>
            <td colspan="{{ $tipoReporte === 'detalle' ? 18 : 15 }}" class="title">Reporte de Ventas ({{ $filtros['tipo_reporte'] }})</td>
        </tr>
        <tr>
            <td><strong>Desde</strong></td>
            <td>{{ $filtros['desde'] }}</td>
            <td><strong>Hasta</strong></td>
            <td>{{ $filtros['hasta'] }}</td>
            <td><strong>Filtro Comprobante</strong></td>
            <td>{{ $filtros['filtro_comprobante'] }}</td>
            <td colspan="{{ $tipoReporte === 'detalle' ? 12 : 9 }}"></td>
        </tr>
        <tr></tr>

        @if($tipoReporte === 'detalle')
            {{-- MODO DETALLADO --}}
            <tr>
                <th>Fecha</th>
                <th>Tipo</th>
                <th>Serie</th>
                <th>Numero</th>
                <th>Cliente</th>
                <th>Documento cliente</th>
                <th>Sucursal</th>
                <th>Cajero</th>
                <th>Medio pago</th>
                <th>Estado</th>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio Unitario</th>
                <th>Subtotal Item</th>
                <th>IGV Item</th>
                <th>Total Item</th>
                <th>Doc. Ref. Modificado</th>
                <th>Motivo NC</th>
            </tr>
            @foreach($ventas as $venta)
                @php
                    $isNc = str_starts_with($venta->tipo_comprobante, 'NOTA_CREDITO');
                    $multiplier = $isNc ? -1 : 1;
                @endphp
                @foreach($venta->detalles as $detalle)
                    <tr>
                        <td>{{ $venta->fecha_emision?->format('d/m/Y') }}</td>
                        <td>{{ $venta->tipo_comprobante }}</td>
                        <td>{{ $venta->serie }}</td>
                        <td>{{ $venta->numero }}</td>
                        <td>{{ $venta->cliente?->razon_social ?: ($venta->cliente?->nombre ?? 'Publico general') }}</td>
                        <td>{{ $venta->cliente?->documento ?? '-' }}</td>
                        <td>{{ $venta->sucursal?->nombre ?? '-' }}</td>
                        <td>{{ $venta->user?->name ?? '-' }}</td>
                        <td>{{ $venta->medio_pago ?? '-' }}</td>
                        <td>{{ $venta->estado ? 'Activo' : 'Anulado' }}</td>
                        <td>{{ $detalle->producto_nombre }}</td>
                        <td class="money">{{ (float)$detalle->cantidad * $multiplier }}</td>
                        <td class="money">{{ (float)$detalle->precio_unitario }}</td>
                        <td class="money">{{ ((float)$detalle->subtotal_neto) * $multiplier }}</td>
                        <td class="money">{{ ((float)$detalle->total_igv) * $multiplier }}</td>
                        <td class="money">{{ ((float)$detalle->total_linea) * $multiplier }}</td>
                        <td>{{ $isNc && $venta->documentoReferencia ? $venta->documentoReferencia->serie_ref . '-' . $venta->documentoReferencia->numero_ref : '-' }}</td>
                        <td>{{ $isNc && $venta->documentoReferencia ? $venta->documentoReferencia->motivo_descripcion : '-' }}</td>
                    </tr>
                @endforeach
            @endforeach
        @else
            {{-- MODO RESUMEN --}}
            <tr>
                <th>Fecha</th>
                <th>Tipo</th>
                <th>Serie</th>
                <th>Numero</th>
                <th>Cliente</th>
                <th>Documento cliente</th>
                <th>Sucursal</th>
                <th>Cajero</th>
                <th>Medio pago</th>
                <th>Estado</th>
                <th>Doc. Ref. Modificado</th>
                <th>Motivo NC</th>
                <th>Subtotal</th>
                <th>IGV</th>
                <th>Total</th>
            </tr>
            @foreach($ventas as $venta)
                @php
                    $isNc = str_starts_with($venta->tipo_comprobante, 'NOTA_CREDITO');
                    $multiplier = $isNc ? -1 : 1;
                @endphp
                <tr>
                    <td>{{ $venta->fecha_emision?->format('d/m/Y') }}</td>
                    <td>{{ $venta->tipo_comprobante }}</td>
                    <td>{{ $venta->serie }}</td>
                    <td>{{ $venta->numero }}</td>
                    <td>{{ $venta->cliente?->razon_social ?: ($venta->cliente?->nombre ?? 'Publico general') }}</td>
                    <td>{{ $venta->cliente?->documento ?? '-' }}</td>
                    <td>{{ $venta->sucursal?->nombre ?? '-' }}</td>
                    <td>{{ $venta->user?->name ?? '-' }}</td>
                    <td>{{ $venta->medio_pago ?? '-' }}</td>
                    <td>{{ $venta->estado ? 'Activo' : 'Anulado' }}</td>
                    <td>{{ $isNc && $venta->documentoReferencia ? $venta->documentoReferencia->serie_ref . '-' . $venta->documentoReferencia->numero_ref : '-' }}</td>
                    <td>{{ $isNc && $venta->documentoReferencia ? $venta->documentoReferencia->motivo_descripcion : '-' }}</td>
                    <td class="money">
                        {{ ((float) $venta->subtotal) * $multiplier }}
                    </td>
                    <td class="money">
                        {{ ((float) $venta->total_igv) * $multiplier }}
                    </td>
                    <td class="money">
                        {{ ((float) $venta->total_neto) * $multiplier }}
                    </td>
                </tr>
            @endforeach
        @endif
    </table>
</body>
</html>
