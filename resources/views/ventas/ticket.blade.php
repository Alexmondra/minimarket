@php
    use BaconQrCode\Renderer\Image\SvgImageBackEnd;
    use BaconQrCode\Renderer\ImageRenderer;
    use BaconQrCode\Renderer\RendererStyle\RendererStyle;
    use BaconQrCode\Writer;

    // --- MONTO EN LETRAS ---
    $total = (float) $documento->total_neto;
    $entero = (int) floor($total);
    $centimos = (int) round(($total - $entero) * 100);

    if (class_exists(NumberFormatter::class)) {
        $formatter = new NumberFormatter('es_PE', NumberFormatter::SPELLOUT);
        $letras = mb_strtoupper((string) $formatter->format($entero));
    } else {
        $letras = number_format($entero, 0, '.', '');
    }
    $monedaNombre = ($documento->tipo_moneda === 'USD') ? 'DÓLARES' : 'SOLES';
    $montoLetras = sprintf('SON: %s CON %02d/100 %s', $letras, $centimos, $monedaNombre);

    // --- DATOS SUNAT DESDE LA TABLA SUNAT ---
    $sunat = $documento->sunat;
    $hash = $sunat?->hash ?? '';
    $numeroComprobante = str_pad((string) $documento->numero, 8, '0', STR_PAD_LEFT);

    // --- QR SUNAT ---
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
        $numeroComprobante,
        number_format((float) $documento->total_igv, 2, '.', ''),
        number_format((float) $documento->total_neto, 2, '.', ''),
        $fechaEmision,
        $tipoDocCliente,
        $documento->cliente?->documento ?? '00000000',
        $hash,
    ]) . '|';

    try {
        $renderer = new ImageRenderer(new RendererStyle(180), new SvgImageBackEnd());
        $writer = new Writer($renderer);
        $qrSvg = $writer->writeString($qrString);
        $qrBase64 = base64_encode($qrSvg);
    } catch (\Throwable $e) {
        $qrBase64 = '';
    }

    // --- LOGO ---
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
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        }
    }

    // --- DATOS EMISOR ---
    $razonSocial = $documento->empresa?->razon_social ?? 'EMPRESA';
    $ruc = $documento->empresa?->ruc ?? '';
    $direccion = $documento->sucursal?->direccion ?? $documento->empresa?->direccion_fiscal ?? '';
    $sucursalNombre = $documento->sucursal?->nombre_sucursal ?? '';
    $telefono = $documento->empresa?->telefono ?? $documento->sucursal?->telefono ?? '';
    $email = $documento->empresa?->email ?? '';
    $ubigeo = $documento->sucursal?->ubigeoRel;
    $distrito = $ubigeo?->distrito ?? '';
    $provincia = $ubigeo?->provincia ?? '';

    // --- URL CONSULTA ---
    $consultaUrl = $documento->empresa?->slug
        ? url('/consultar/' . $documento->empresa->slug)
        : 'https://cpe.sunat.gob.pe/';

    $tipoComprobanteLegible = match($documento->tipo_comprobante) {
        'FACTURA' => 'FACTURA ELECTRÓNICA',
        'BOLETA'  => 'BOLETA DE VENTA ELECTRÓNICA',
        'NOTA_CREDITO' => 'NOTA DE CRÉDITO ELECTRÓNICA',
        'NOTA_DEBITO'  => 'NOTA DE DÉBITO ELECTRÓNICA',
        default   => 'TICKET DE VENTA',
    };

    $esAnulado = $documento->estado === false || $documento->estado === 'ANULADO';

    // --- PUNTOS (desde movimientos, no de columna) ---
    $puntosGanados = \App\Models\ClientePuntoMovimiento::query()
        ->where('documento_id', $documento->id)
        ->where('tipo', 'acumulacion')
        ->sum('puntos');
    $puntosCanjeados = \App\Models\ClientePuntoMovimiento::query()
        ->where('documento_id', $documento->id)
        ->where('tipo', 'canje')
        ->sum('puntos');

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
@endphp
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Ticket {{ $documento->serie }}-{{ $documento->numero }}</title>
    <style>
        /* 1. RESET TOTAL */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        /* 2. ELIMINAR MÁRGENES DE PÁGINA — la impresora decide el ancho */
        @page {
            margin: 0;
            size: auto;
        }

        html,
        body {
            width: 100%;
            margin: 0 !important;
            padding: 0 !important;
            background: #fff;
            height: auto !important;
            overflow: visible !important;
            position: absolute;
            top: 0;
            left: 0;
        }

        body {
            font-family: 'Arial Narrow', Arial, sans-serif;
            font-size: 11px;
            color: #000;
            line-height: 1.2;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* 3. CONTENEDOR AJUSTADO — sin ancho fijo, se adapta a la térmica */
        .ticket-wrapper {
            width: 100%;
            padding: 2mm 3mm 8mm 3mm;
            position: relative;
            display: block;
            margin-top: 0 !important;
        }

        @media print {
            .ticket-wrapper {
                page-break-inside: avoid !important;
            }

            table,
            tr,
            img,
            .points-box {
                page-break-inside: avoid !important;
            }
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        .text-red { color: red !important; }

        .border-dashed {
            border-top: 1px dashed #000;
            margin: 5px 0;
        }

        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        td, th { padding: 2px 0; vertical-align: top; word-wrap: break-word; }

        .presentation-line {
            display: block;
            font-size: 9px;
            color: #444;
            line-height: 1.1;
        }

        img.logo {
            max-width: 65%;
            height: auto;
            filter: grayscale(100%) contrast(150%);
            margin-bottom: 4px;
        }

        .watermark {
            position: absolute;
            top: 25%;
            left: 5%;
            transform: rotate(-45deg);
            font-size: 50px;
            font-weight: bold;
            border: 5px solid #000;
            color: #000;
            padding: 10px;
            opacity: 0.25;
            z-index: 100;
            pointer-events: none;
            text-align: center;
            width: 90%;
        }

        .total-box {
            background: #000 !important;
            color: #fff !important;
            padding: 5px 8px;
            display: flex;
            justify-content: space-between;
            font-size: 15px;
            margin-top: 5px;
            border-radius: 2px;
        }

        .points-box {
            border: 1px solid #000;
            border-radius: 4px;
            padding: 5px;
            margin: 8px 0;
        }
    </style>
</head>
<body onload="window.print(); setTimeout(function(){ window.close(); }, 1500);">
    <div class="ticket-wrapper">
        @if($esAnulado)
            <div class="watermark">ANULADO</div>
        @endif

        {{-- ENCABEZADO --}}
        <div class="text-center">
            @if($logoBase64)
                <img class="logo" src="{{ $logoBase64 }}" alt="Logo">
            @endif
            <div style="font-size: 14px; font-weight: bold; line-height: 1.1;">{{ $razonSocial }}</div>
            <div style="font-size: 12px; font-weight: bold; margin-top: 3px;">RUC: {{ $ruc }}</div>
            <div style="font-size: 10px; line-height: 1.2;">
                {{ $direccion }}<br>
                @if($distrito || $provincia)
                    {{ $distrito }}@if($distrito && $provincia) - @endif{{ $provincia }}<br>
                @endif
                @if($telefono || $email)
                    {{ $telefono ?? '' }}{{ $telefono && $email ? ' | ' : '' }}{{ $email ?? '' }}<br>
                @endif
                @if($sucursalNombre)
                    <b>{{ $sucursalNombre }}</b><br>
                @endif
            </div>
        </div>

        <div class="border-dashed"></div>

        {{-- DATOS COMPROBANTE --}}
        <div style="font-size: 11px;">
            <div style="display: flex; justify-content: space-between;">
                <b>{{ $tipoComprobanteLegible }}:</b>
                <span>{{ $documento->serie }}-{{ $numeroComprobante }}</span>
            </div>
            <div><b>Fecha:</b> {{ $fechaEmision instanceof \Carbon\Carbon ? $fechaEmision->format('d/m/Y H:i') : date('d/m/Y H:i', strtotime($fechaEmision)) }}</div>
            <div>
                <b>Cliente:</b>
                {{ $documento->cliente && $documento->cliente->documento !== '00000000' ? ($documento->cliente->razon_social ?: trim(($documento->cliente->nombre ?? '') . ' ' . ($documento->cliente->apellido ?? ''))) : 'PÚBLICO EN GENERAL' }}
            </div>
            @if($documento->cliente && $documento->cliente->documento != '00000000')
                <div><b>{{ $documento->cliente->tipo_documento }}:</b> {{ $documento->cliente->documento }}</div>
            @endif
            @if($documento->medio_pago)
                <div><b>Medio de Pago:</b> {{ $documento->medio_pago }}</div>
            @endif
        </div>

        <div class="border-dashed"></div>

        {{-- DETALLE PRODUCTOS --}}
        <table>
            <thead>
                <tr>
                    <th class="text-left" style="width: 15%;">CANT</th>
                    <th class="text-left">DESCRIPCIÓN</th>
                    <th class="text-right" style="width: 25%;">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @foreach($documento->detalles as $det)
                    @php($presentacionTexto = $descripcionPresentacion($det->presentacion))
                    <tr>
                        <td class="font-bold">{{ $formatoCantidad($det->cantidad) }}</td>
                        <td>
                            {{ $det->producto_nombre }}
                            @if($presentacionTexto !== '' && ! str_contains(mb_strtolower($det->producto_nombre), mb_strtolower($presentacionTexto)))
                                <span class="presentation-line">{{ $presentacionTexto }}</span>
                            @endif
                        </td>
                        <td class="text-right">{{ number_format((float)$det->total_linea, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="border-dashed"></div>

        {{-- RESUMEN IMPORTES --}}
        <table>
            @if((float)$documento->op_gravada > 0)
                <tr><td class="text-right">Op. Gravada:</td><td class="text-right" style="width: 40%;">S/ {{ number_format((float)$documento->op_gravada, 2) }}</td></tr>
            @endif
            @if((float)$documento->op_exonerada > 0)
                <tr><td class="text-right">Op. Exonerada:</td><td class="text-right" style="width: 40%;">S/ {{ number_format((float)$documento->op_exonerada, 2) }}</td></tr>
            @endif
            @if((float)$documento->op_inafecta > 0)
                <tr><td class="text-right">Op. Inafecta:</td><td class="text-right" style="width: 40%;">S/ {{ number_format((float)$documento->op_inafecta, 2) }}</td></tr>
            @endif
            @if((float)$documento->total_igv > 0)
                <tr><td class="text-right">IGV ({{ number_format((float)$documento->porcentaje_igv, 0) }}%):</td><td class="text-right" style="width: 40%;">S/ {{ number_format((float)$documento->total_igv, 2) }}</td></tr>
            @endif
            @if((float)$documento->total_descuento > 0)
                <tr class="text-red"><td class="text-right font-bold">DESCUENTO:</td><td class="text-right font-bold">- S/ {{ number_format((float)$documento->total_descuento, 2) }}</td></tr>
            @endif
        </table>

        {{-- TOTAL --}}
        <div class="total-box">
            <span class="font-bold">TOTAL</span>
            <span class="font-bold">S/ {{ number_format((float)$documento->total_neto, 2) }}</span>
        </div>

        {{-- MONTO EN LETRAS --}}
        <div class="text-center uppercase" style="margin-top: 6px; font-size: 9px;">{{ $montoLetras }}</div>

        {{-- PAGO --}}
        @if($documento->medio_pago === 'EFECTIVO')
            <div style="display: flex; justify-content: space-between; font-size: 10px; margin-top: 5px;">
                <span>Recibido:</span><span>S/ {{ number_format((float)$documento->monto_recibido, 2) }}</span>
            </div>
            @if($documento->vuelto > 0)
                <div style="display: flex; justify-content: space-between; font-size: 10px;">
                    <span>Vuelto:</span><span>S/ {{ number_format($documento->vuelto, 2) }}</span>
                </div>
            @endif
        @endif

        {{-- PUNTOS --}}
        @if($documento->cliente && $documento->cliente->documento !== '00000000' && ($puntosGanados > 0 || $puntosCanjeados < 0))
            <div class="points-box">
                <div style="font-weight: bold; font-size: 10px; background: #eee; text-align: center;">MONEDERO PUNTOS</div>
                @if($puntosGanados > 0)
                    <div style="display: flex; justify-content: space-between; padding: 2px 5px; font-size: 10px;">
                        <span>Ganados hoy:</span><b>+{{ $puntosGanados }}</b>
                    </div>
                @endif
                @if($puntosCanjeados < 0)
                    <div style="display: flex; justify-content: space-between; padding: 2px 5px; font-size: 10px;">
                        <span>Canjeados:</span><b>{{ $puntosCanjeados }}</b>
                    </div>
                @endif
            </div>
        @endif

        {{-- QR + LEYENDA (mismo formato visual para todos) --}}
        <div class="text-center" style="margin-top: 10px;">
            @if($qrBase64)
                <img src="data:image/svg+xml;base64,{{ $qrBase64 }}" style="width: 95px;">
            @endif
            <div style="font-size: 9px; margin-top: 5px; color: #444;">
                @if(in_array($documento->tipo_comprobante, ['FACTURA', 'BOLETA']))
                    Representación impresa de la <b>{{ $tipoComprobanteLegible }}</b><br>
                    @if($hash)
                        <span style="font-size: 7px;">Hash: {{ substr($hash, 0, 40) }}...</span><br>
                    @endif
                    Consulte en: <b>{{ $consultaUrl }}</b><br>
                    @if($documento->esExentoAmazonia())
                        <div style="font-weight: bold; margin-top: 4px; font-size: 8px;">BIENES TRANSFERIDOS EN LA AMAZONÍA REGIÓN SELVA PARA SER CONSUMIDOS EN LA MISMA</div>
                    @endif
                @else
                    <b>{{ $documento->serie }}-{{ $numeroComprobante }}</b><br>
                    Emitido: {{ now()->format('d/m/Y H:i') }}<br>
                    {{ $documento->empresa?->razon_social }}
                @endif
            </div>
        </div>

        {{-- PIE --}}
        <div class="text-center" style="margin-top: 8px; font-size: 9px;">
            <div style="font-weight: bold;">¡Gracias por su compra!</div>
        </div>
    </div>
</body>
</html>
