@php
    use BaconQrCode\Renderer\Image\SvgImageBackEnd;
    use BaconQrCode\Renderer\ImageRenderer;
    use BaconQrCode\Renderer\RendererStyle\RendererStyle;
    use BaconQrCode\Writer;

    $esTicket = $documento->tipo_comprobante === 'TICKET';
    $total = (float) $documento->total_neto;
    $entero = (int) floor($total);
    $centimos = (int) round(($total - $entero) * 100);
    $letras = number_format($entero, 0, '.', '');

    if (class_exists(NumberFormatter::class)) {
        $formatter = new NumberFormatter('es_PE', NumberFormatter::SPELLOUT);
        $letras = mb_strtoupper((string) $formatter->format($entero));
    }

    $monedaNombre = $documento->tipo_moneda === 'USD' ? 'DÓLARES' : 'SOLES';
    $monedaSimbolo = $documento->tipo_moneda === 'USD' ? '$' : 'S/';
    $montoLetras = sprintf('SON %s CON %02d/100 %s', $letras, $centimos, $monedaNombre);

    $sunat = $documento->sunat;
    $hash = $sunat?->hash ?? '';
    $codigoSunat = $sunat?->codigo_respuesta_sunat ?? '';
    $mensajeSunat = $sunat?->mensaje_sunat ?? '';
    $sunatAceptado = $sunat && $sunat->estado_sunat;
    $numeroComprobante = str_pad((string) $documento->numero, 8, '0', STR_PAD_LEFT);

    $tipoDoc = match($documento->tipo_comprobante) {
        'FACTURA' => '01',
        'BOLETA'  => '03',
        default   => 'TK',
    };

    $tipoDocCliente = match($documento->cliente?->tipo_documento) {
        'RUC' => '6',
        'CE', 'CARNET_EXTRANJERIA' => '4',
        'PASAPORTE' => '7',
        default => '1',
    };

    $fechaEmision = $documento->fecha_emision instanceof \Carbon\Carbon
        ? $documento->fecha_emision
        : \Carbon\Carbon::parse($documento->fecha_emision ?? now());

    $unidadSunat = static function (?string $unidad): string {
        $unidad = strtoupper(trim((string) $unidad));

        return match ($unidad) {
            'NIU', 'ZZ', 'KGM', 'LTR', 'MTR', 'BX' => $unidad,
            default => 'NIU',
        };
    };

    $descripcionPresentacion = static function ($presentacion): string {
        if (! $presentacion) {
            return '';
        }

        $partes = [];
        $tipo = trim((string) ($presentacion->tipo_presentacion ?? ''));
        if ($tipo !== '') {
            $partes[] = $tipo;
        }

        $cantidad = (float) ($presentacion->cantidad ?? 0);
        $unidad = strtoupper(trim((string) ($presentacion->unidadMedida?->abreviatura ?? '')));
        if ($cantidad > 0 && $unidad !== '') {
            $cantidadTexto = rtrim(rtrim(number_format($cantidad, 3, '.', ''), '0'), '.');
            $partes[] = 'x ' . $cantidadTexto . ' ' . $unidad;
        }

        return trim(implode(' ', $partes));
    };

    $formatoCantidad = static function (mixed $cantidad): string {
        $valor = (float) $cantidad;

        if (abs($valor - round($valor)) < 0.00001) {
            return number_format($valor, 0, '.', '');
        }

        return rtrim(rtrim(number_format($valor, 3, '.', ''), '0'), '.');
    };

    $qrString = $esTicket
        ? implode('|', [
            $documento->empresa?->ruc ?? '',
            'TICKET',
            $documento->serie,
            $numeroComprobante,
            number_format((float) $documento->total_neto, 2, '.', ''),
            $fechaEmision->format('Y-m-d'),
            $documento->cliente?->documento ?? '00000000',
        ]) . '|'
        : implode('|', [
            $documento->empresa?->ruc ?? '',
            $tipoDoc,
            $documento->serie,
            $numeroComprobante,
            number_format((float) $documento->total_igv, 2, '.', ''),
            number_format((float) $documento->total_neto, 2, '.', ''),
            $fechaEmision->format('Y-m-d'),
            $tipoDocCliente,
            $documento->cliente?->documento ?? '00000000',
            $hash,
        ]) . '|';

    $qrBase64 = '';
    try {
        $renderer = new ImageRenderer(new RendererStyle(220), new SvgImageBackEnd());
        $writer = new Writer($renderer);
        $qrBase64 = base64_encode($writer->writeString($qrString));
    } catch (\Throwable $e) {
        $qrBase64 = '';
    }

    $logoBase64 = null;
    $rutaLogo = $documento->empresa?->logo;
    if ($rutaLogo) {
        $path = null;
        if (file_exists(storage_path('app/public/' . $rutaLogo))) {
            $path = storage_path('app/public/' . $rutaLogo);
        } elseif (file_exists(public_path($rutaLogo))) {
            $path = public_path($rutaLogo);
        }

        if ($path) {
            $type = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $type = $type === 'jpg' ? 'jpeg' : $type;
            $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode(file_get_contents($path));
        }
    }

    $razonSocial = $documento->empresa?->razon_social ?? 'EMPRESA';
    $ruc = $documento->empresa?->ruc ?? '';
    $sucursal = $documento->sucursal;
    $sucursalNombre = $sucursal?->nombre_sucursal ?? 'Sucursal principal';
    $direccionSucursal = $sucursal?->direccion ?: $documento->empresa?->direccion_fiscal ?: '-';
    $telefono = $sucursal?->telefono ?? $documento->empresa?->telefono ?? '';
    $email = $sucursal?->email ?? $documento->empresa?->email ?? '';
    $ubigeo = $sucursal?->ubigeoRel;
    $ubicacion = collect([$ubigeo?->distrito, $ubigeo?->provincia, $ubigeo?->departamento])->filter()->implode(' - ');

    $clienteNombre = $documento->cliente?->razon_social
        ?: trim(($documento->cliente?->nombre ?? '') . ' ' . ($documento->cliente?->apellido ?? ''));
    $clienteNombre = $clienteNombre !== '' ? $clienteNombre : 'PÚBLICO EN GENERAL';

    $tipoComprobanteLegible = match($documento->tipo_comprobante) {
        'FACTURA' => 'FACTURA ELECTRÓNICA',
        'BOLETA' => 'BOLETA DE VENTA ELECTRÓNICA',
        'NOTA_CREDITO' => 'NOTA DE CRÉDITO ELECTRÓNICA',
        'NOTA_DEBITO' => 'NOTA DE DÉBITO ELECTRÓNICA',
        default => 'TICKET DE VENTA',
    };

    $mostrarEstadoSunat = false;
    $estadoSunatTexto = '';
    $estadoSunatClase = '';
    if (! $esTicket && in_array($documento->tipo_comprobante, ['FACTURA', 'BOLETA', 'NOTA_CREDITO', 'NOTA_DEBITO'], true) && $codigoSunat !== '') {
        $mostrarEstadoSunat = true;
        if ($sunatAceptado) {
            $estadoSunatTexto = 'Aceptado por SUNAT';
            $estadoSunatClase = 'status-ok';
        } elseif ($codigoSunat === 'ERROR' || str_starts_with((string) $codigoSunat, '2') || str_starts_with((string) $codigoSunat, '3')) {
            $estadoSunatTexto = 'Rechazado por SUNAT';
            $estadoSunatClase = 'status-danger';
        } else {
            $estadoSunatTexto = 'Respuesta SUNAT registrada';
            $estadoSunatClase = 'status-warn';
        }
    }

    $esAnulado = $documento->estado === false || $documento->estado === 'ANULADO';
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $documento->serie }}-{{ $documento->numero }}</title>
    <style>
        @page { size: a4; margin: 24px 28px; }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            font-size: 10.5px;
            color: #172033;
            line-height: 1.45;
            background: #ffffff;
        }

        .page {
            position: relative;
        }

        .watermark {
            position: fixed;
            top: 350px;
            left: 90px;
            width: 560px;
            text-align: center;
            transform: rotate(-35deg);
            font-size: 72px;
            font-weight: 800;
            color: rgba(185, 28, 28, 0.13);
            border: 6px solid rgba(185, 28, 28, 0.12);
            padding: 18px 0;
            z-index: -1;
        }

        .brand-strip {
            height: 8px;
            border-radius: 999px;
            background: #0f766e;
            margin-bottom: 18px;
        }

        .header-table,
        .info-table,
        .items-table,
        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td { vertical-align: top; }

        .brand-cell { width: 58%; padding-right: 18px; }
        .doc-cell { width: 42%; }

        .logo-box {
            float: left;
            width: 92px;
            height: 72px;
            border: 1px solid #dbe3ef;
            border-radius: 12px;
            text-align: center;
            padding: 8px;
            margin-right: 12px;
            background: #f8fafc;
        }

        .logo-box img {
            max-width: 76px;
            max-height: 56px;
        }

        .logo-fallback {
            font-size: 24px;
            font-weight: 800;
            color: #0f766e;
            line-height: 54px;
        }

        .company-name {
            margin: 0 0 4px 0;
            font-size: 18px;
            line-height: 1.18;
            font-weight: 800;
            letter-spacing: .2px;
            color: #0f172a;
            text-transform: uppercase;
        }

        .branch-line {
            margin: 0 0 8px 0;
            color: #0f766e;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .muted { color: #64748b; }
        .strong { font-weight: 800; color: #0f172a; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .doc-box {
            border: 2px solid #0f766e;
            border-radius: 16px;
            overflow: hidden;
            background: #ffffff;
        }

        .doc-box .ruc {
            padding: 9px 12px;
            text-align: center;
            font-size: 14px;
            font-weight: 800;
            color: #0f172a;
            background: #f0fdfa;
            border-bottom: 1px solid #99f6e4;
        }

        .doc-box .type {
            padding: 11px 14px 4px 14px;
            text-align: center;
            font-size: 14px;
            font-weight: 900;
            letter-spacing: .6px;
            color: #0f766e;
            text-transform: uppercase;
        }

        .doc-box .number {
            padding: 2px 14px 13px 14px;
            text-align: center;
            font-size: 17px;
            font-weight: 900;
            color: #0f172a;
        }

        .section-title {
            margin: 18px 0 8px 0;
            font-size: 10px;
            font-weight: 900;
            letter-spacing: .7px;
            color: #0f766e;
            text-transform: uppercase;
        }

        .panel {
            border: 1px solid #dbe3ef;
            border-radius: 14px;
            background: #ffffff;
            overflow: hidden;
        }

        .info-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #edf2f7;
            vertical-align: top;
        }

        .info-table tr:last-child td { border-bottom: 0; }
        .label { width: 16%; color: #64748b; font-size: 9px; font-weight: 800; text-transform: uppercase; }
        .value { color: #0f172a; font-weight: 650; }

        .items-table {
            margin-top: 8px;
            border: 1px solid #dbe3ef;
            border-radius: 14px;
            overflow: hidden;
        }

        .items-table th {
            padding: 9px 8px;
            background: #0f766e;
            color: #ffffff;
            font-size: 9px;
            font-weight: 900;
            text-transform: uppercase;
            border-right: 1px solid rgba(255,255,255,.25);
        }

        .items-table th:last-child { border-right: 0; }
        .items-table thead { display: table-header-group; }
        .items-table tr { page-break-inside: avoid; }

        .items-table td {
            padding: 8px;
            border-bottom: 1px solid #edf2f7;
            vertical-align: top;
        }

        .items-table tbody tr:nth-child(even) td { background: #f8fafc; }
        .items-table tbody tr:last-child td { border-bottom: 0; }

        .product-name {
            font-weight: 750;
            color: #0f172a;
        }

        .product-presentation {
            margin-top: 2px;
            color: #64748b;
            font-size: 9px;
        }

        .amount-row {
            width: 100%;
            margin-top: 14px;
            border-collapse: collapse;
        }

        .amount-words {
            width: 58%;
            padding-right: 16px;
            vertical-align: top;
        }

        .amount-box {
            border: 1px solid #dbe3ef;
            border-radius: 14px;
            padding: 11px 13px;
            background: #f8fafc;
            color: #334155;
            font-size: 10px;
            font-weight: 800;
        }

        .totals-cell {
            width: 42%;
            vertical-align: top;
        }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #dbe3ef;
            border-radius: 14px;
            overflow: hidden;
        }

        .totals-table td {
            padding: 6px 10px;
            border-bottom: 1px solid #edf2f7;
        }

        .totals-table tr:last-child td { border-bottom: 0; }
        .totals-label { color: #475569; font-weight: 800; }
        .totals-value { text-align: right; color: #0f172a; font-weight: 850; }

        .grand-total td {
            background: #0f766e;
            color: #ffffff;
            font-size: 14px;
            font-weight: 900;
            padding: 10px;
        }

        .footer {
            margin-top: 18px;
            border: 1px solid #dbe3ef;
            border-radius: 14px;
            overflow: hidden;
            page-break-inside: avoid;
        }

        .footer-table td {
            padding: 12px;
            vertical-align: top;
        }

        .qr-cell {
            width: 128px;
            text-align: center;
            border-left: 1px solid #edf2f7;
            background: #f8fafc;
        }

        .qr-cell img { width: 104px; height: 104px; }

        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 999px;
            font-size: 9px;
            font-weight: 900;
            text-transform: uppercase;
        }

        .status-ok { background: #dcfce7; color: #166534; }
        .status-warn { background: #fef3c7; color: #92400e; }
        .status-danger { background: #fee2e2; color: #991b1b; }
        .status-muted { background: #e2e8f0; color: #475569; }

        .hash {
            margin-top: 7px;
            font-size: 8px;
            color: #64748b;
            word-break: break-all;
        }

        .legal-note {
            margin-top: 10px;
            color: #64748b;
            font-size: 9px;
        }
    </style>
</head>
<body>
    <div class="page">
        @if($esAnulado)
            <div class="watermark">ANULADO</div>
        @endif

        <div class="brand-strip"></div>

        <table class="header-table">
            <tr>
                <td class="brand-cell">
                    <div class="logo-box">
                        @if($logoBase64)
                            <img src="{{ $logoBase64 }}" alt="Logo">
                        @else
                            <div class="logo-fallback">{{ mb_substr($razonSocial, 0, 1) }}</div>
                        @endif
                    </div>

                    <h1 class="company-name">{{ $razonSocial }}</h1>
                    <div class="branch-line">Sucursal: {{ $sucursalNombre }}</div>
                    <div class="muted">
                        <span class="strong">Dirección:</span> {{ $direccionSucursal }}<br>
                        @if($ubicacion)
                            <span class="strong">Ubicación:</span> {{ $ubicacion }}<br>
                        @endif
                        @if($telefono || $email)
                            <span class="strong">Contacto:</span>
                            {{ $telefono }}{{ $telefono && $email ? ' | ' : '' }}{{ $email }}
                        @endif
                    </div>
                </td>
                <td class="doc-cell">
                    <div class="doc-box">
                        <div class="ruc">R.U.C. {{ $ruc }}</div>
                        <div class="type">{{ $tipoComprobanteLegible }}</div>
                        <div class="number">{{ $documento->serie }}-{{ $numeroComprobante }}</div>
                    </div>
                </td>
            </tr>
        </table>

        <div class="section-title">Datos del cliente y operación</div>
        <div class="panel">
            <table class="info-table">
                <tr>
                    <td class="label">Adquiriente</td>
                    <td class="value" colspan="3">{{ $clienteNombre }}</td>
                </tr>
                <tr>
                    <td class="label">Documento</td>
                    <td class="value">{{ $documento->cliente?->tipo_documento ?: 'DNI' }}: {{ $documento->cliente?->documento ?: '00000000' }}</td>
                    <td class="label">Emisión</td>
                    <td class="value">{{ $fechaEmision->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td class="label">Dirección</td>
                    <td class="value" colspan="3">{{ $documento->cliente?->direccion ?: '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Moneda</td>
                    <td class="value">{{ $documento->tipo_moneda === 'USD' ? 'Dólares Americanos' : 'Soles' }} ({{ $documento->tipo_moneda }})</td>
                    <td class="label">Pago</td>
                    <td class="value" style="text-transform: uppercase;">{{ $documento->medio_pago ?: 'EFECTIVO' }}</td>
                </tr>
            </table>
        </div>

        <div class="section-title">Detalle de productos</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 9%;" class="text-center">Cant.</th>
                    <th style="width: 11%;" class="text-center">Cod. UND</th>
                    <th style="width: 43%;">Descripción</th>
                    <th style="width: 12%;" class="text-right">P. Unit.</th>
                    <th style="width: 12%;" class="text-right">Desc.</th>
                    <th style="width: 13%;" class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($documento->detalles as $detalle)
                    @php($presentacionTexto = $descripcionPresentacion($detalle->presentacion))
                    <tr>
                        <td class="text-center">{{ $formatoCantidad($detalle->cantidad) }}</td>
                        <td class="text-center">{{ $unidadSunat($detalle->presentacion?->unidadMedida?->abreviatura) }}</td>
                        <td>
                            <span class="product-name">{{ $detalle->producto_nombre }}</span>
                            @if($presentacionTexto !== '' && ! str_contains(mb_strtolower($detalle->producto_nombre), mb_strtolower($presentacionTexto)))
                                <div class="product-presentation">Presentación: {{ $presentacionTexto }}</div>
                            @endif
                        </td>
                        <td class="text-right">{{ $monedaSimbolo }} {{ number_format((float) $detalle->precio_unitario, 2) }}</td>
                        <td class="text-right">{{ $monedaSimbolo }} {{ number_format((float) $detalle->subtotal_descuento, 2) }}</td>
                        <td class="text-right strong">{{ $monedaSimbolo }} {{ number_format((float) $detalle->total_linea, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="amount-row">
            <tr>
                <td class="amount-words">
                    <div class="amount-box">
                        <span class="muted">Importe en letras</span><br>
                        {{ $montoLetras }}
                    </div>
                </td>
                <td class="totals-cell">
                    <table class="totals-table">
                        <tr>
                            <td class="totals-label">Subtotal</td>
                            <td class="totals-value">{{ $monedaSimbolo }} {{ number_format((float) $documento->subtotal, 2) }}</td>
                        </tr>
                        @if((float) $documento->total_descuento > 0)
                            <tr>
                                <td class="totals-label">Descuento</td>
                                <td class="totals-value">- {{ $monedaSimbolo }} {{ number_format((float) $documento->total_descuento, 2) }}</td>
                            </tr>
                        @endif
                        @if((float) $documento->op_gravada > 0)
                            <tr>
                                <td class="totals-label">Op. Gravada</td>
                                <td class="totals-value">{{ $monedaSimbolo }} {{ number_format((float) $documento->op_gravada, 2) }}</td>
                            </tr>
                        @endif
                        @if((float) $documento->op_exonerada > 0)
                            <tr>
                                <td class="totals-label">Op. Exonerada</td>
                                <td class="totals-value">{{ $monedaSimbolo }} {{ number_format((float) $documento->op_exonerada, 2) }}</td>
                            </tr>
                        @endif
                        @if((float) $documento->op_inafecta > 0)
                            <tr>
                                <td class="totals-label">Op. Inafecta</td>
                                <td class="totals-value">{{ $monedaSimbolo }} {{ number_format((float) $documento->op_inafecta, 2) }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td class="totals-label">IGV ({{ number_format((float) $documento->porcentaje_igv, 0) }}%)</td>
                            <td class="totals-value">{{ $monedaSimbolo }} {{ number_format((float) $documento->total_igv, 2) }}</td>
                        </tr>
                        <tr class="grand-total">
                            <td>Total</td>
                            <td class="text-right">{{ $monedaSimbolo }} {{ number_format((float) $documento->total_neto, 2) }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <div class="footer">
            <table class="footer-table">
                <tr>
                    <td>
                        @if($mostrarEstadoSunat)
                            <div>
                                <span class="status-badge {{ $estadoSunatClase }}">{{ $estadoSunatTexto }}</span>
                            </div>
                        @endif
                        @if(! $esTicket && $codigoSunat)
                            <div style="margin-top: 8px;"><span class="strong">Código SUNAT:</span> {{ $codigoSunat }}</div>
                        @endif
                        @if(! $esTicket && $mensajeSunat && $codigoSunat)
                            <div><span class="strong">Mensaje:</span> {{ $mensajeSunat }}</div>
                        @endif
                        @if(! $esTicket && $hash)
                            <div class="hash"><span class="strong">Hash:</span> {{ $hash }}</div>
                        @endif
                        <div class="legal-note">
                            @if($esTicket)
                                Documento interno de venta. No válido como comprobante electrónico SUNAT.
                            @else
                                Representación impresa de {{ mb_strtolower($tipoComprobanteLegible) }}. El código QR contiene los datos tributarios exigidos por SUNAT para consulta del comprobante.
                            @endif
                        </div>
                    </td>
                    <td class="qr-cell">
                        @if($qrBase64)
                            <img src="data:image/svg+xml;base64,{{ $qrBase64 }}" alt="QR">
                            <div class="muted" style="font-size: 8px; margin-top: 4px;">{{ $documento->serie }}-{{ $numeroComprobante }}</div>
                        @else
                            <div class="muted">QR no disponible</div>
                        @endif
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
