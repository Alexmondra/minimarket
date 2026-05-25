<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $documento->serie }}-{{ $documento->numero }}</title>
    <style>
        body {
            font-family: "Courier New", monospace;
            margin: 0;
            padding: 18px;
            background: #f4f1ea;
            color: #1b1a18;
        }
        .ticket {
            max-width: 420px;
            margin: 0 auto;
            background: #fffdf8;
            border: 1px solid #d8d0c1;
            box-shadow: 0 20px 50px rgba(32, 25, 14, 0.08);
            padding: 20px;
        }
        .center { text-align: center; }
        .muted { color: #6f6757; font-size: 12px; }
        .line { border-top: 1px dashed #8e8575; margin: 12px 0; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; }
        td { padding: 4px 0; vertical-align: top; }
        .right { text-align: right; }
        .total { font-size: 18px; font-weight: bold; }
        .pill {
            display: inline-block;
            padding: 3px 8px;
            background: #efe4ce;
            color: #5f4520;
            border-radius: 999px;
            font-size: 11px;
            font-weight: bold;
        }
        @media print {
            body { background: white; padding: 0; }
            .ticket { box-shadow: none; border: 0; max-width: none; }
        }
    </style>
</head>
<body>
    <div class="ticket">
        <div class="center">
            <div class="pill">{{ $documento->tipo_comprobante }}</div>
            <h2 style="margin: 10px 0 4px;">{{ $documento->empresa?->razon_social }}</h2>
            <div class="muted">RUC {{ $documento->empresa?->ruc }}</div>
            <div class="muted">{{ $documento->sucursal?->nombre_sucursal }}</div>
            <div class="muted">{{ $documento->sucursal?->direccion }}</div>
        </div>

        <div class="line"></div>

        <table>
            <tr>
                <td>Comprobante</td>
                <td class="right">{{ $documento->serie }}-{{ $documento->numero }}</td>
            </tr>
            <tr>
                <td>Fecha</td>
                <td class="right">{{ $documento->fecha_emision?->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td>Cliente</td>
                <td class="right">{{ $documento->cliente?->razon_social ?: trim(($documento->cliente?->nombre ?? '') . ' ' . ($documento->cliente?->apellido ?? '')) }}</td>
            </tr>
            <tr>
                <td>Doc.</td>
                <td class="right">{{ $documento->cliente?->documento ?: '00000000' }}</td>
            </tr>
        </table>

        <div class="line"></div>

        <table>
            @foreach($documento->detalles as $detalle)
                <tr>
                    <td colspan="2"><strong>{{ $detalle->producto_nombre }}</strong></td>
                </tr>
                <tr>
                    <td>{{ number_format((float) $detalle->cantidad, 3) }} x S/ {{ number_format((float) $detalle->precio_unitario, 2) }}</td>
                    <td class="right">S/ {{ number_format((float) $detalle->total_linea, 2) }}</td>
                </tr>
            @endforeach
        </table>

        <div class="line"></div>

        <table>
            <tr>
                <td>Subtotal</td>
                <td class="right">S/ {{ number_format((float) $documento->subtotal, 2) }}</td>
            </tr>
            <tr>
                <td>Descuento</td>
                <td class="right">S/ {{ number_format((float) $documento->total_descuento, 2) }}</td>
            </tr>
            <tr>
                <td>IGV</td>
                <td class="right">S/ {{ number_format((float) $documento->total_igv, 2) }}</td>
            </tr>
            <tr>
                <td class="total">Total</td>
                <td class="right total">S/ {{ number_format((float) $documento->total_neto, 2) }}</td>
            </tr>
            <tr>
                <td>Recibido</td>
                <td class="right">S/ {{ number_format((float) $documento->monto_recibido, 2) }}</td>
            </tr>
            <tr>
                <td>Vuelto</td>
                <td class="right">S/ {{ number_format((float) $documento->vuelto, 2) }}</td>
            </tr>
        </table>

        <div class="line"></div>

        <div class="center muted">
            Emitido por {{ $documento->user?->name }}<br>
            Puntos ganados: {{ $documento->puntos_ganados }}<br>
            Gracias por su compra
        </div>
    </div>
</body>
</html>
