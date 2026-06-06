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
            <td colspan="12" class="title">Reporte de Ventas</td>
        </tr>
        <tr>
            <td><strong>Desde</strong></td>
            <td>{{ $filtros['desde'] }}</td>
            <td><strong>Hasta</strong></td>
            <td>{{ $filtros['hasta'] }}</td>
            <td colspan="8"></td>
        </tr>
        <tr></tr>
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
            <th>Subtotal</th>
            <th>IGV</th>
            <th>Total</th>
        </tr>
        @foreach($ventas as $venta)
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
                <td class="money">{{ number_format((float) $venta->subtotal, 2, '.', '') }}</td>
                <td class="money">{{ number_format((float) $venta->total_igv, 2, '.', '') }}</td>
                <td class="money">{{ number_format((float) $venta->total_neto, 2, '.', '') }}</td>
            </tr>
        @endforeach
    </table>
</body>
</html>
