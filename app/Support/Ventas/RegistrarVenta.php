<?php

namespace App\Support\Ventas;

use App\Jobs\ProcesarFacturaSunat;
use App\Models\Cliente;
use App\Models\DetalleDocumento;
use App\Models\Documento;
use App\Models\LotePresentacion;
use App\Models\MovimientoInventario;
use App\Models\ProductoPresentacion;
use App\Models\ProductoSucursal;
use App\Models\Serie;
use App\Models\Sucursal;
use App\Models\User;
use App\Support\Facturacion\FacturacionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class RegistrarVenta
{
    public const MEDIOS_PAGO_CONTADO = [
        'EFECTIVO',
        'YAPE',
        'PLIN',
        'TARJETA',
        'TRANSFERENCIA',
        'OTRO',
    ];

    public function __construct(
        protected CajaService $cajaService,
        protected VentaCalculator $calculator,
        protected PuntosService $puntosService,
        protected VentaFileService $fileService,
        protected FacturacionService $facturacionService,
    ) {}

    public function ejecutar(User $user, array $payload): Documento
    {
        $itemsPayload = collect($payload['items'] ?? []);
        $presentacionIds = $itemsPayload->pluck('producto_presentacion_id')->filter()->unique()->all();
        $presentaciones = ProductoPresentacion::query()
            ->with('producto')
            ->whereIn('id', $presentacionIds)
            ->get()
            ->keyBy('id');

        $documento = DB::transaction(function () use ($user, $payload, $presentaciones, $itemsPayload): Documento {
            $empresa = $user->empresa()->with('empresaConfig')->firstOrFail();
            $sucursalId = (int) $payload['sucursal_id'];
            $sucursal = Sucursal::with('ubigeoRel')->findOrFail($sucursalId);
            $caja = $this->cajaService->requireCajaAbierta($user->id, $sucursalId);
            $tipoComprobante = strtoupper($payload['tipo_comprobante']);
            $medioPago = strtoupper((string) ($payload['medio_pago'] ?? 'EFECTIVO'));

            if (! in_array($medioPago, self::MEDIOS_PAGO_CONTADO, true)) {
                throw new \RuntimeException('El medio de pago seleccionado no es válido.');
            }

            $cliente = $this->resolverCliente(
                tipoComprobante: $tipoComprobante,
                clienteData: $payload['cliente'] ?? [],
            );

            $puntosCanjeados = (int) ($payload['puntos_canjeados'] ?? 0);
            $descuentoPuntos = $this->puntosService->descuentoPorPuntos($puntosCanjeados);

            $serie = Serie::query()
                ->where('sucursal_id', $sucursalId)
                ->where('tipo_comprobante', $tipoComprobante)
                ->lockForUpdate()
                ->first();

            if (! $serie) {
                $serie = Serie::create([
                    'sucursal_id' => $sucursalId,
                    'tipo_comprobante' => $tipoComprobante,
                    'serie' => $this->seriePorDefecto($tipoComprobante),
                    'correlativo' => 1,
                ]);
            }

            $lineasVenta = $itemsPayload
                ->map(function (array $item) use ($presentaciones): array {
                    $presentacionId = $item['producto_presentacion_id'];
                    $presentacion = $presentaciones->get($presentacionId);

                    if (! $presentacion) {
                        throw new \RuntimeException("La presentación de producto con ID {$presentacionId} no existe.");
                    }

                    return [
                        'producto_presentacion_id' => $presentacion->id,
                        'producto_id' => $presentacion->producto_id,
                        'producto_nombre' => $presentacion->producto?->nombre ?? 'Producto',
                        'cantidad' => (float) $item['cantidad'],
                        'precio_unitario' => (float) $item['precio_unitario'],
                        'afecto_igv' => (bool) ($presentacion->producto?->afecto_igv ?? true),
                    ];
                })
                ->all();

            if ($lineasVenta === []) {
                throw new \RuntimeException('Agrega al menos un producto para registrar la venta.');
            }

            foreach ($lineasVenta as $lineaVenta) {
                if ($lineaVenta['cantidad'] <= 0) {
                    throw new \RuntimeException("La cantidad de {$lineaVenta['producto_nombre']} debe ser mayor a cero.");
                }

                if ($lineaVenta['precio_unitario'] < 0) {
                    throw new \RuntimeException("El precio de {$lineaVenta['producto_nombre']} no puede ser negativo.");
                }
            }

            $porcentajeIgv = (float) ($payload['porcentaje_igv'] ?? 18);
            if ($this->esExentoDeIgv($sucursal)) {
                $porcentajeIgv = 0.0;
                foreach ($lineasVenta as $k => $lineaVenta) {
                    $lineasVenta[$k]['afecto_igv'] = false;
                }
            }

            $calculo = $this->calculator->calcular(
                $lineasVenta,
                (bool) $empresa->incluido_tributo,
                $porcentajeIgv,
                $descuentoPuntos
            );

            $totales = $calculo['totales'];
            $montoRecibido = (float) ($payload['monto_recibido'] ?? 0);
            $totalNeto = (float) $totales['total_neto'];
            if ($medioPago !== 'EFECTIVO') {
                $montoRecibido = $totalNeto;
            }

            $documento = Documento::create([
                'caja_sesion_id' => $caja->id,
                'sucursal_id' => $sucursalId,
                'empresa_id' => $empresa->id,
                'cliente_id' => $cliente?->id,
                'user_id' => $user->id,
                'tipo_comprobante' => $tipoComprobante,
                'serie' => $serie->serie,
                'numero' => str_pad((string) $serie->correlativo, 8, '0', STR_PAD_LEFT),
                'fecha_emision' => now()->toDateString(),
                'total_bruto' => $totales['total_bruto'],
                'total_descuento' => $totales['total_descuento'],
                'subtotal' => $totales['subtotal'],
                'total_neto' => $totales['total_neto'],
                'op_gravada' => $totales['op_gravada'],
                'op_exonerada' => $totales['op_exonerada'],
                'op_inafecta' => $totales['op_inafecta'],
                'total_igv' => $totales['total_igv'],
                'porcentaje_igv' => $porcentajeIgv,
                'tipo_moneda' => $payload['tipo_moneda'] ?? 'PEN',
                'medio_pago' => $medioPago,
                'monto_recibido' => $montoRecibido,
                'descuento_puntos' => $descuentoPuntos,
                'referencia_pago' => $payload['referencia_pago'] ?? null,
                'estado' => true,
            ]);

            foreach ($lineasVenta as $index => $lineaVenta) {
                $lineaCalculada = $calculo['lineas'][$index];
                $this->descontarStockYCrearDetalles(
                    documento: $documento,
                    sucursalId: $sucursalId,
                    lineItem: $lineaVenta,
                    lineCalculation: $lineaCalculada,
                    userId: $user->id,
                    empresaId: $empresa->id,
                );
            }

            $serie->increment('correlativo');

            if ($cliente && $puntosCanjeados > 0) {
                $this->puntosService->registrarCanje(
                    cliente: $cliente,
                    empresaId: $empresa->id,
                    sucursalId: $sucursalId,
                    userId: $user->id,
                    puntos: $puntosCanjeados,
                    descuento: $descuentoPuntos,
                    documento: $documento
                );
            }

            if ($cliente) {
                $puntosGanados = $this->puntosService->puntosGanados($documento->total_neto);

                $this->puntosService->registrarAcumulacion(
                    cliente: $cliente,
                    empresaId: $empresa->id,
                    sucursalId: $sucursalId,
                    userId: $user->id,
                    puntos: $puntosGanados,
                    documento: $documento
                );
            }

            return $documento;
        });

        $documento->loadMissing([
            'empresa',
            'sucursal',
            'cliente',
            'detalles.presentacion.unidadMedida',
        ]);

        $htmlTicket = view('ventas.ticket', [
            'documento' => $documento,
        ])->render();
        $this->fileService->guardarTicketHtml($documento, $htmlTicket);

        $pdf = Pdf::loadView('ventas.pdf', ['documento' => $documento]);
        $this->fileService->guardarPdf($documento, $pdf->output());

        ProcesarFacturaSunat::dispatch($documento);

        return $documento->fresh([
            'cliente',
            'empresa',
            'sucursal',
            'detalles.presentacion.unidadMedida',
            'archivos',
            'sunat',
        ]);
    }

    protected function resolverCliente(string $tipoComprobante, array $clienteData): ?Cliente
    {
        $documento = trim((string) ($clienteData['documento'] ?? ''));
        $tipoDocumento = strtoupper(trim((string) ($clienteData['tipo_documento'] ?? '')));
        $nombre = trim((string) ($clienteData['nombre'] ?? ''));
        $apellido = trim((string) ($clienteData['apellido'] ?? ''));
        $razonSocial = trim((string) ($clienteData['razon_social'] ?? ''));

        if ($tipoComprobante === 'FACTURA') {
            if ($tipoDocumento !== 'RUC' || strlen($documento) !== 11 || $razonSocial === '') {
                throw new \RuntimeException('La factura requiere RUC y razon social del cliente.');
            }
        }

        if ($documento === '') {
            return Cliente::query()->firstOrCreate(
                [
                    'tipo_documento' => 'DNI',
                    'documento' => '00000000',
                ],
                [
                    'nombre' => 'Cliente',
                    'apellido' => 'Varios',
                    'razon_social' => 'Cliente Varios',
                ]
            );
        }

        return Cliente::query()->firstOrCreate(
            [
                'tipo_documento' => $tipoDocumento ?: (strlen($documento) === 11 ? 'RUC' : 'DNI'),
                'documento' => $documento,
            ],
            [
                'nombre' => $nombre ?: ($razonSocial !== '' ? $razonSocial : 'Cliente'),
                'apellido' => $apellido,
                'razon_social' => $razonSocial !== '' ? $razonSocial : null,
                'telefono' => $clienteData['telefono'] ?? null,
                'email' => $clienteData['email'] ?? null,
                'direccion' => $clienteData['direccion'] ?? null,
            ]
        );
    }

    protected function descontarStockYCrearDetalles(
        Documento $documento,
        int $sucursalId,
        array $lineItem,
        array $lineCalculation,
        int $userId,
        int $empresaId
    ): void {
        $remaining = round((float) $lineItem['cantidad'], 3);

        $stocks = LotePresentacion::query()
            ->with([
                'lote',
                'productoPresentacion.producto',
                'productoSucursal' => fn ($query) => $query->where('sucursal_id', $sucursalId)->latest('id'),
            ])
            ->where('producto_presentacion_id', $lineItem['producto_presentacion_id'])
            ->where('stock', '>', 0)
            ->whereHas('productoSucursal', fn ($query) => $query
                ->where('sucursal_id', $sucursalId)
                ->where('activo', true))
            ->join('lotes', 'lotes.id', '=', 'lote_presentacion.lote_id')
            ->whereNotIn('lotes.estado_lote', ['por_confirmar', 'vencido', 'agotado'])
            ->orderByRaw('CASE WHEN lotes.fecha_vencimiento IS NULL THEN 1 ELSE 0 END')
            ->orderBy('lotes.fecha_vencimiento')
            ->orderBy('lotes.created_at')
            ->select('lote_presentacion.*')
            ->lockForUpdate()
            ->get();

        if ($stocks->sum('stock') < $remaining) {
            $this->intentarDescomprimirPadres(
                productoPresentacionId: $lineItem['producto_presentacion_id'],
                cantidadNecesaria: $remaining - $stocks->sum('stock'),
                sucursalId: $sucursalId,
                userId: $userId,
                empresaId: $empresaId,
                documentoReferencia: "Venta {$documento->serie}-{$documento->numero}"
            );

            // Re-fetch stocks after decompression
            $stocks = LotePresentacion::query()
                ->with([
                    'lote',
                    'productoPresentacion.producto',
                    'productoSucursal' => fn ($query) => $query->where('sucursal_id', $sucursalId)->latest('id'),
                ])
                ->where('producto_presentacion_id', $lineItem['producto_presentacion_id'])
                ->where('stock', '>', 0)
                ->whereHas('productoSucursal', fn ($query) => $query
                    ->where('sucursal_id', $sucursalId)
                    ->where('activo', true))
                ->join('lotes', 'lotes.id', '=', 'lote_presentacion.lote_id')
                ->whereNotIn('lotes.estado_lote', ['por_confirmar', 'vencido', 'agotado'])
                ->orderByRaw('CASE WHEN lotes.fecha_vencimiento IS NULL THEN 1 ELSE 0 END')
                ->orderBy('lotes.fecha_vencimiento')
                ->orderBy('lotes.created_at')
                ->select('lote_presentacion.*')
                ->lockForUpdate()
                ->get();
        }

        if ($stocks->sum('stock') < $remaining) {
            throw new \RuntimeException("Stock insuficiente para {$lineItem['producto_nombre']}.");
        }

        while ($remaining > 0) {
            /** @var LotePresentacion|null $stock */
            $stock = $stocks->first(fn ($row) => (float) $row->stock > 0);

            if (! $stock) {
                break;
            }

            $consumir = min(round((float) $stock->stock, 3), $remaining);
            $ratio = round($consumir / (float) $lineItem['cantidad'], 8);
            $productoSucursal = $stock->productoSucursal;

            $stock->update([
                'stock' => round((float) $stock->stock - $consumir, 3),
            ]);

            DetalleDocumento::create([
                'documento_id' => $documento->id,
                'lote_id' => $stock->lote_id,
                'producto_id' => $lineItem['producto_id'],
                'producto_nombre' => $lineItem['producto_nombre'],
                'producto_presentacion_id' => $lineItem['producto_presentacion_id'],
                'producto_sucursal_id' => $productoSucursal?->id,
                'cantidad' => $consumir,
                'precio_unitario' => $lineCalculation['precio_unitario'],
                'valor_unitario' => $lineCalculation['valor_unitario'],
                'igv' => $lineCalculation['igv_unitario'],
                'total_igv' => round($lineCalculation['total_igv'] * $ratio, 2),
                'tipo_afectacion' => $lineCalculation['tipo_afectacion'],
                'descuento_unitario' => $lineCalculation['descuento_unitario'],
                'subtotal_bruto' => round($lineCalculation['subtotal_bruto'] * $ratio, 2),
                'subtotal_descuento' => round($lineCalculation['subtotal_descuento'] * $ratio, 2),
                'subtotal_neto' => round($lineCalculation['subtotal_neto'] * $ratio, 2),
                'total_linea' => round($lineCalculation['total_linea'] * $ratio, 2),
            ]);

            MovimientoInventario::create([
                'empresa_id' => $empresaId,
                'sucursal_id' => $sucursalId,
                'producto_nombre' => $lineItem['producto_nombre'],
                'producto_presentacion_id' => $lineItem['producto_presentacion_id'],
                'tipo' => 'salida_venta',
                'cantidad' => -$consumir,
                'motivo' => "Venta {$documento->serie}-{$documento->numero}",
                'referencia' => "Documento:{$documento->id}",
                'user_id' => $userId,
                'stock_final' => $stock->fresh()->stock,
            ]);

            $remaining = round($remaining - $consumir, 3);
        }
    }

    protected function intentarDescomprimirPadres(
        int $productoPresentacionId,
        float $cantidadNecesaria,
        int $sucursalId,
        int $userId,
        int $empresaId,
        string $documentoReferencia
    ): void {
        $parentPresentations = ProductoPresentacion::query()
            ->where('presentacion_base_id', $productoPresentacionId)
            ->get();

        if ($parentPresentations->isEmpty()) {
            return;
        }

        $parentIds = $parentPresentations->pluck('id')->all();

        $parentStocks = LotePresentacion::query()
            ->with(['lote', 'productoPresentacion'])
            ->whereIn('producto_presentacion_id', $parentIds)
            ->where('stock', '>', 0)
            ->whereHas('productoSucursal', fn ($query) => $query
                ->where('sucursal_id', $sucursalId)
                ->where('activo', true))
            ->join('lotes', 'lotes.id', '=', 'lote_presentacion.lote_id')
            ->whereNotIn('lotes.estado_lote', ['por_confirmar', 'vencido', 'agotado'])
            ->orderByRaw('CASE WHEN lotes.fecha_vencimiento IS NULL THEN 1 ELSE 0 END')
            ->orderBy('lotes.fecha_vencimiento')
            ->orderBy('lotes.created_at')
            ->select('lote_presentacion.*')
            ->lockForUpdate()
            ->get();

        $acumulado = 0.0;

        foreach ($parentStocks as $parentStock) {
            if ($acumulado >= $cantidadNecesaria) {
                break;
            }

            $parentPres = $parentStock->productoPresentacion;
            $factor = (float) $parentPres->cantidad;
            if ($factor <= 0) {
                continue;
            }

            $faltante = $cantidadNecesaria - $acumulado;
            $cajasANecesitar = ceil($faltante / $factor);
            $cajasDisponibles = (float) $parentStock->stock;
            $cajasADescomprimir = min($cajasANecesitar, $cajasDisponibles);

            if ($cajasADescomprimir <= 0) {
                continue;
            }

            $parentStock->update([
                'stock' => round($parentStock->stock - $cajasADescomprimir, 3),
            ]);

            $baseLotePres = LotePresentacion::query()
                ->where('lote_id', $parentStock->lote_id)
                ->where('producto_presentacion_id', $productoPresentacionId)
                ->first();

            $cantidadAdicionada = $cajasADescomprimir * $factor;

            if ($baseLotePres) {
                $baseLotePres->update([
                    'stock' => round($baseLotePres->stock + $cantidadAdicionada, 3),
                ]);
            } else {
                $baseLotePres = LotePresentacion::create([
                    'lote_id' => $parentStock->lote_id,
                    'producto_presentacion_id' => $productoPresentacionId,
                    'stock' => round($cantidadAdicionada, 3),
                ]);
            }

            $baseProdSucursal = ProductoSucursal::query()
                ->where('sucursal_id', $sucursalId)
                ->where('lote_presentacion_id', $baseLotePres->id)
                ->first();

            if (! $baseProdSucursal) {
                $anyBaseProdSucursal = ProductoSucursal::query()
                    ->where('sucursal_id', $sucursalId)
                    ->whereHas('lotePresentacion', fn ($q) => $q->where('producto_presentacion_id', $productoPresentacionId))
                    ->first();

                $precioBase = $anyBaseProdSucursal ? $anyBaseProdSucursal->precio : 0.00;
                $precioBaseMayorista = $anyBaseProdSucursal ? $anyBaseProdSucursal->precio_mayorista : null;

                ProductoSucursal::create([
                    'producto_id' => $parentPres->producto_id,
                    'sucursal_id' => $sucursalId,
                    'lote_presentacion_id' => $baseLotePres->id,
                    'stock_minimo' => 0,
                    'precio' => $precioBase,
                    'precio_mayorista' => $precioBaseMayorista,
                    'activo' => true,
                ]);
            }

            MovimientoInventario::create([
                'empresa_id' => $empresaId,
                'sucursal_id' => $sucursalId,
                'producto_nombre' => $parentPres->producto?->nombre.' ('.$parentPres->tipo_presentacion.')',
                'producto_presentacion_id' => $parentPres->id,
                'tipo' => 'salida_descompresion',
                'cantidad' => -$cajasADescomprimir,
                'motivo' => "Descompresión para {$documentoReferencia}",
                'referencia' => "Lote:{$parentStock->lote_id}",
                'user_id' => $userId,
                'stock_final' => $parentStock->fresh()->stock,
            ]);

            $basePres = ProductoPresentacion::find($productoPresentacionId);
            MovimientoInventario::create([
                'empresa_id' => $empresaId,
                'sucursal_id' => $sucursalId,
                'producto_nombre' => $basePres->producto?->nombre.' ('.$basePres->tipo_presentacion.')',
                'producto_presentacion_id' => $productoPresentacionId,
                'tipo' => 'entrada_descompresion',
                'cantidad' => $cantidadAdicionada,
                'motivo' => "Descompresión desde {$parentPres->tipo_presentacion} ({$cajasADescomprimir} unidades)",
                'referencia' => "Lote:{$parentStock->lote_id}",
                'user_id' => $userId,
                'stock_final' => $baseLotePres->fresh()->stock,
            ]);

            $acumulado += $cantidadAdicionada;
        }
    }

    protected function seriePorDefecto(string $tipoComprobante): string
    {
        $empresaId = auth()->user()?->empresa_id;
        if ($empresaId) {
            return Serie::siguienteSeriePorEmpresa($empresaId, $tipoComprobante);
        }

        return match ($tipoComprobante) {
            'FACTURA' => 'F001',
            'BOLETA' => 'B001',
            default => 'T001',
        };
    }

    public function esExentoDeIgv(Sucursal $sucursal): bool
    {
        if ((float) $sucursal->impuesto_porcentaje === 0.0) {
            return true;
        }

        $ubigeo = $sucursal->ubigeoRel;
        if ($ubigeo) {
            $departamento = strtoupper(trim($ubigeo->departamento));
            $exempt = ['LORETO', 'MADRE DE DIOS', 'UCAYALI', 'SAN MARTIN', 'AMAZONAS'];
            if (in_array($departamento, $exempt)) {
                return true;
            }
        }

        return false;
    }
}
