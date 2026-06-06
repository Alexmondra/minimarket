<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 22px; }
        body { color: #0f172a; font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        .header { border-bottom: 2px solid #0f172a; margin-bottom: 14px; padding-bottom: 10px; }
        .title { font-size: 22px; font-weight: 800; margin: 0; }
        .subtitle { color: #64748b; font-size: 11px; margin-top: 4px; }
        .cards { width: 100%; margin-bottom: 14px; }
        .card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px; }
        .label { color: #64748b; font-size: 8px; font-weight: 700; text-transform: uppercase; }
        .value { font-size: 15px; font-weight: 800; margin-top: 2px; }
        .filters { color: #475569; margin-bottom: 12px; }
        table { border-collapse: collapse; width: 100%; }
        th { background: #0f172a; color: #ffffff; font-size: 8px; padding: 7px 6px; text-align: left; text-transform: uppercase; }
        td { border-bottom: 1px solid #e2e8f0; padding: 7px 6px; vertical-align: top; }
        .right { text-align: right; }
        .badge { border-radius: 999px; font-size: 8px; font-weight: 700; padding: 3px 6px; }
        .ticket { background: #f1f5f9; color: #334155; }
        .sunat { background: #dcfce7; color: #166534; }
    </style>
</head>
<body>
    <div class="header">
        <p class="title">Reporte de Ventas</p>
        <div class="subtitle">Desde {{ $filtros['desde'] }} | Hasta {{ $filtros['hasta'] }}</div>
    </div>

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
                <tr>
                    <td>{{ $venta->fecha_emision?->format('d/m/Y') }}</td>
                    <td>
                        <span class="badge {{ $venta->tipo_comprobante === 'TICKET' ? 'ticket' : 'sunat' }}">{{ $venta->tipo_comprobante }}</span><br>
                        {{ $venta->serie }}-{{ $venta->numero }}
                    </td>
                    <td>{{ $venta->cliente?->razon_social ?: ($venta->cliente?->nombre ?? 'Publico general') }}</td>
                    <td>{{ $venta->sucursal?->nombre ?? '-' }}</td>
                    <td>{{ $venta->user?->name ?? '-' }}</td>
                    <td>{{ $venta->medio_pago ?? '-' }}</td>
                    <td class="right">{{ number_format((float) $venta->subtotal, 2) }}</td>
                    <td class="right">{{ number_format((float) $venta->total_igv, 2) }}</td>
                    <td class="right"><strong>{{ number_format((float) $venta->total_neto, 2) }}</strong></td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align: center; padding: 22px; color: #64748b;">No hay ventas para exportar con estos filtros.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
