@php
    use BaconQrCode\Renderer\Image\SvgImageBackEnd;
    use BaconQrCode\Renderer\ImageRenderer;
    use BaconQrCode\Renderer\RendererStyle\RendererStyle;
    use BaconQrCode\Writer;

    $total = (float) $documento->total_neto;
    $entero = (int) floor($total);
    $centimos = (int) round(($total - $entero) * 100);
    $letras = number_format($entero, 0, '.', '');

    if (class_exists(NumberFormatter::class)) {
        $formatter = new NumberFormatter('es_PE', NumberFormatter::SPELLOUT);
        $letras = mb_strtoupper((string) $formatter->format($entero));
    }

    $monedaNombre = $documento->tipo_moneda === 'USD' ? 'DÓLARES' : 'SOLES';
    $montoLetras = sprintf('SON %s CON %02d/100 %s', $letras, $centimos, $monedaNombre);

    // QR SUNAT
    $tipoDoc = match($documento->tipo_comprobante) {
        'FACTURA' => '01',
        'BOLETA'  => '03',
        default   => '03',
    };

    $tipoDocCliente = match($documento->cliente?->tipo_documento) {
        'RUC' => '6',
        'CE', 'CARNET_EXTRANJERIA' => '4',
        'PASAPORTE' => '7',
        default => '1',
    };

    $fechaEmision = $documento->fecha_emision instanceof \Carbon\Carbon
        ? $documento->fecha_emision->format('Y-m-d')
        : (is_string($documento->fecha_emision) ? $documento->fecha_emision : now()->format('Y-m-d'));

    $qrString = implode('|', [
        $documento->empresa?->ruc ?? '',
        $tipoDoc,
        $documento->serie,
        $documento->numero,
        number_format((float) $documento->total_igv, 2, '.', ''),
        number_format((float) $documento->total_neto, 2, '.', ''),
        $fechaEmision,
        $tipoDocCliente,
        $documento->cliente?->documento ?? '00000000',
        $documento->hash ?? '',
    ]) . '|';

    $qrBase64 = '';
    try {
        $renderer = new ImageRenderer(
            new RendererStyle(180),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $qrSvg = $writer->writeString($qrString);
        $qrBase64 = base64_encode($qrSvg);
    } catch (\Throwable $e) {
        $qrBase64 = '';
    }
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $documento->serie }}-{{ $documento->numero }}</title>
    <style>
        @page {
            size: a4;
            margin: 30px;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
        }
        .header-table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .header-table td {
            vertical-align: top;
        }
        .company-info {
            width: 60%;
        }
        .company-name {
            font-size: 16px;
            font-weight: bold;
            color: #1a365d;
            margin: 0 0 5px 0;
            text-transform: uppercase;
        }
        .company-details {
            color: #555;
            font-size: 10px;
            margin-top: 5px;
        }
        .ruc-box {
            width: 38%;
            border: 2px solid #1a365d;
            border-radius: 5px;
            text-align: center;
            padding: 12px;
            background-color: #f7fafc;
        }
        .ruc-number {
            font-size: 14px;
            font-weight: bold;
            color: #1a365d;
            margin-bottom: 5px;
        }
        .doc-type {
            font-size: 12px;
            font-weight: bold;
            letter-spacing: 1px;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .doc-number {
            font-size: 14px;
            font-weight: bold;
        }
        .info-table {
            width: 100%;
            border: 1px solid #e2e8f0;
            border-collapse: collapse;
            margin-bottom: 20px;
            background-color: #fcfcfc;
        }
        .info-table td {
            padding: 6px 10px;
            border: 1px solid #e2e8f0;
            vertical-align: top;
        }
        .info-label {
            font-weight: bold;
            color: #4a5568;
            width: 18%;
        }
        .info-value {
            color: #2d3748;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th {
            background-color: #1a365d;
            color: white;
            padding: 8px;
            font-weight: bold;
            text-align: left;
            border: 1px solid #1a365d;
            text-transform: uppercase;
            font-size: 10px;
        }
        .items-table td {
            padding: 8px;
            border: 1px solid #e2e8f0;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .totals-container {
            width: 100%;
            margin-top: 10px;
        }
        .totals-table {
            width: 40%;
            float: right;
            border-collapse: collapse;
        }
        .totals-table td {
            padding: 4px 8px;
        }
        .totals-label {
            text-align: right;
            font-weight: bold;
            color: #4a5568;
        }
        .totals-value {
            text-align: right;
            width: 120px;
            border: 1px solid #e2e8f0;
            background-color: #f7fafc;
        }
        .footer {
            margin-top: 40px;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
            font-size: 9px;
            color: #718096;
            clear: both;
        }
        .footer-note {
            text-align: center;
            margin-top: 15px;
            font-style: italic;
        }
        .amount-words {
            font-weight: bold;
            color: #2d3748;
            margin-bottom: 15px;
            background-color: #edf2f7;
            padding: 8px;
            border-radius: 4px;
        }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td class="company-info">
                <h1 class="company-name">{{ $documento->empresa?->razon_social }}</h1>
                <div class="company-details">
                    <strong>Dirección:</strong> {{ $documento->sucursal?->direccion ?: $documento->empresa?->direccion_fiscal ?: '-' }}<br>
                    <strong>Sucursal:</strong> {{ $documento->sucursal?->nombre_sucursal }}<br>
                    @if($documento->empresa?->telefono)
                        <strong>Teléfono:</strong> {{ $documento->empresa->telefono }}<br>
                    @endif
                    @if($documento->empresa?->email)
                        <strong>Email:</strong> {{ $documento->empresa->email }}
                    @endif
                </div>
            </td>
            <td class="ruc-box">
                <div class="ruc-number">R.U.C. {{ $documento->empresa?->ruc }}</div>
                <div class="doc-type">
                    @if($documento->tipo_comprobante === 'FACTURA')
                        FACTURA ELECTRÓNICA
                    @elseif($documento->tipo_comprobante === 'BOLETA')
                        BOLETA DE VENTA ELECTRÓNICA
                    @else
                        {{ $documento->tipo_comprobante }}
                    @endif
                </div>
                <div class="doc-number">{{ $documento->serie }}-{{ $documento->numero }}</div>
            </td>
        </tr>
    </table>

    <table class="info-table">
        <tr>
            <td class="info-label">Adquiriente:</td>
            <td class="info-value" colspan="3">
                {{ $documento->cliente?->razon_social ?: trim(($documento->cliente?->nombre ?? '') . ' ' . ($documento->cliente?->apellido ?? '')) }}
            </td>
        </tr>
        <tr>
            <td class="info-label">Documento:</td>
            <td class="info-value" style="width: 32%;">
                {{ $documento->cliente?->tipo_documento ?: 'DNI' }}: {{ $documento->cliente?->documento ?: '00000000' }}
            </td>
            <td class="info-label" style="width: 18%;">Fecha Emisión:</td>
            <td class="info-value">
                {{ $documento->fecha_emision?->format('d/m/Y') }}
            </td>
        </tr>
        <tr>
            <td class="info-label">Dirección:</td>
            <td class="info-value" colspan="3">
                {{ $documento->cliente?->direccion ?: '-' }}
            </td>
        </tr>
        <tr>
            <td class="info-label">Moneda:</td>
            <td class="info-value">
                {{ $documento->tipo_moneda === 'USD' ? 'Dólares Americanos' : 'Soles' }} ({{ $documento->tipo_moneda }})
            </td>
            <td class="info-label">Medio de Pago:</td>
            <td class="info-value" style="text-transform: uppercase;">
                {{ $documento->medio_pago ?: 'Efectivo' }}
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 10%;" class="text-center">Cant.</th>
                <th style="width: 12%;" class="text-center">Unidad</th>
                <th style="width: 53%;">Descripción</th>
                <th style="width: 12%;" class="text-right">P. Unit.</th>
                <th style="width: 13%;" class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($documento->detalles as $detalle)
                <tr>
                    <td class="text-center">{{ number_format((float) $detalle->cantidad, 2) }}</td>
                    <td class="text-center">{{ strtoupper($detalle->presentacion?->unidadMedida?->abreviatura ?: 'NIU') }}</td>
                    <td>{{ $detalle->producto_nombre }}</td>
                    <td class="text-right">S/ {{ number_format((float) $detalle->precio_unitario, 2) }}</td>
                    <td class="text-right">S/ {{ number_format((float) $detalle->total_linea, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="amount-words">
        {{ $montoLetras }}
    </div>

    <div class="totals-container clearfix">
        <table class="totals-table">
            <tr>
                <td class="totals-label">Subtotal:</td>
                <td class="totals-value">S/ {{ number_format((float) $documento->subtotal, 2) }}</td>
            </tr>
            @if((float)$documento->total_descuento > 0)
                <tr>
                    <td class="totals-label">Descuento:</td>
                    <td class="totals-value">S/ {{ number_format((float) $documento->total_descuento, 2) }}</td>
                </tr>
            @endif
            @if((float)$documento->op_gravada > 0)
                <tr>
                    <td class="totals-label">Op. Gravada:</td>
                    <td class="totals-value">S/ {{ number_format((float) $documento->op_gravada, 2) }}</td>
                </tr>
            @endif
            @if((float)$documento->op_exonerada > 0)
                <tr>
                    <td class="totals-label">Op. Exonerada:</td>
                    <td class="totals-value">S/ {{ number_format((float) $documento->op_exonerada, 2) }}</td>
                </tr>
            @endif
            @if((float)$documento->op_inafecta > 0)
                <tr>
                    <td class="totals-label">Op. Inafecta:</td>
                    <td class="totals-value">S/ {{ number_format((float) $documento->op_inafecta, 2) }}</td>
                </tr>
            @endif
            <tr>
                <td class="totals-label">IGV ({{ number_format((float)$documento->porcentaje_igv, 0) }}%):</td>
                <td class="totals-value">S/ {{ number_format((float) $documento->total_igv, 2) }}</td>
            </tr>
            <tr style="font-size: 12px; font-weight: bold;">
                <td class="totals-label">Total:</td>
                <td class="totals-value">S/ {{ number_format((float) $documento->total_neto, 2) }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        @if(in_array($documento->tipo_comprobante, ['FACTURA', 'BOLETA']))
            <table style="width: 100%; margin-bottom: 15px;">
                <tr>
                    <td style="width: 70%; vertical-align: top;">
                        @if($documento->sunat)
                            <div><strong>Estado SUNAT:</strong> {{ $documento->estado_sunat ?? ($documento->sunat->estado_sunat ? 'Aceptado' : 'Pendiente/Rechazado') }}</div>
                            @if($documento->sunat->codigo_respuesta_sunat)
                                <div><strong>Código SUNAT:</strong> {{ $documento->sunat->codigo_respuesta_sunat }}</div>
                            @endif
                            @if($documento->sunat->mensaje_sunat)
                                <div><strong>Mensaje:</strong> {{ $documento->sunat->mensaje_sunat }}</div>
                            @endif
                        @endif
                        @if($documento->hash)
                            <div style="font-size: 8px; word-break: break-all; margin-top: 5px;">
                                <strong>Hash:</strong> {{ $documento->hash }}
                            </div>
                        @endif
                    </td>
                    <td style="width: 30%; text-align: center; vertical-align: top;">
                        @if($qrBase64)
                            <img src="data:image/svg+xml;base64,{{ $qrBase64 }}" style="width: 100px; height: auto;"><br>
                            <span style="font-size: 7px;">{{ $documento->serie }}-{{ $documento->numero }}</span>
                        @endif
                    </td>
                </tr>
            </table>
        @else
            @if($documento->sunat)
                <div><strong>Estado SUNAT:</strong> {{ $documento->sunat->descripcion_estado ?: $documento->sunat->estado_sunat }}</div>
                @if($documento->sunat->codigo_respuesta)
                    <div><strong>Código SUNAT:</strong> {{ $documento->sunat->codigo_respuesta }}</div>
                @endif
            @endif
        @endif
        <div class="footer-note">
            Representación impresa del comprobante de pago electrónico. Consulte la validez de este documento en el portal de la SUNAT.
        </div>
    </div>
</body>
</html>
