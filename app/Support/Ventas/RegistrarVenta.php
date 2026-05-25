<?php

namespace App\Support\Ventas;

use App\Models\Cliente;
use App\Models\DetalleDocumento;
use App\Models\Documento;
use App\Models\LotePresentacion;
use App\Models\MovimientoInventario;
use App\Models\ProductoPresentacion;
use App\Models\Serie;
use App\Models\Sunat;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RegistrarVenta
{
    public function __construct(
        protected CajaService $cajaService,
        protected VentaCalculator $calculator,
        protected PuntosService $puntosService,
        protected VentaXmlGenerator $xmlGenerator,
        protected VentaFileService $fileService,
    ) {}

    public function ejecutar(User $user, array $payload): Documento
    {
        return DB::transaction(function () use ($user, $payload): Documento {
            $empresa = $user->empresa()->with('empresaConfig')->firstOrFail();
            $sucursalId = (int) $payload['sucursal_id'];
            $caja = $this->cajaService->requireCajaAbierta($user->id, $sucursalId);
            $tipoComprobante = strtoupper($payload['tipo_comprobante']);

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
                throw new \RuntimeException('No hay una serie configurada para este tipo de comprobante en la sucursal.');
            }

            $lineasVenta = collect($payload['items'] ?? [])
                ->map(function (array $item): array {
                    $presentacion = ProductoPresentacion::query()
                        ->with('producto')
                        ->findOrFail($item['producto_presentacion_id']);

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

            $calculo = $this->calculator->calcular(
                $lineasVenta,
                (bool) $empresa->incluido_tributo,
                (float) ($payload['porcentaje_igv'] ?? 18),
                $descuentoPuntos
            );

            $totales = $calculo['totales'];
            $montoRecibido = (float) ($payload['monto_recibido'] ?? 0);
            $totalNeto = (float) $totales['total_neto'];
            $vuelto = strtoupper((string) ($payload['medio_pago'] ?? 'EFECTIVO')) === 'EFECTIVO'
                ? max(round($montoRecibido - $totalNeto, 2), 0)
                : 0;

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
                'porcentaje_igv' => $payload['porcentaje_igv'] ?? 18,
                'tipo_moneda' => $payload['tipo_moneda'] ?? 'PEN',
                'medio_pago' => strtoupper((string) ($payload['medio_pago'] ?? 'EFECTIVO')),
                'monto_recibido' => $montoRecibido,
                'vuelto' => $vuelto,
                'puntos_ganados' => 0,
                'puntos_canjeados' => $puntosCanjeados,
                'descuento_puntos' => $descuentoPuntos,
                'referencia_pago' => $payload['referencia_pago'] ?? null,
                'estado' => true,
                'observaciones' => $payload['observaciones'] ?? null,
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
                $documento->update(['puntos_ganados' => $puntosGanados]);

                $this->puntosService->registrarAcumulacion(
                    cliente: $cliente,
                    empresaId: $empresa->id,
                    sucursalId: $sucursalId,
                    userId: $user->id,
                    puntos: $puntosGanados,
                    documento: $documento
                );
            }

            $documento->loadMissing([
                'empresa',
                'sucursal',
                'cliente',
                'detalles.presentacion.unidadMedida',
            ]);

            $xml = $this->xmlGenerator->generar($documento);
            $this->fileService->guardarXml($documento, $xml);

            $htmlTicket = view('ventas.ticket', [
                'documento' => $documento,
            ])->render();
            $this->fileService->guardarTicketHtml($documento, $htmlTicket);

            Sunat::create([
                'empresa_id' => $empresa->id,
                'documento_id' => $documento->id,
                'estado_sunat' => false,
                'mensaje_sunat' => 'Pendiente de envio a cola SUNAT.',
            ]);

            return $documento->fresh([
                'cliente',
                'empresa',
                'sucursal',
                'detalles.presentacion.unidadMedida',
                'archivos',
                'sunat',
            ]);
        });
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
            ->orderByRaw('CASE WHEN lotes.fecha_vencimiento IS NULL THEN 1 ELSE 0 END')
            ->orderBy('lotes.fecha_vencimiento')
            ->orderBy('lotes.created_at')
            ->select('lote_presentacion.*')
            ->lockForUpdate()
            ->get();

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
}
