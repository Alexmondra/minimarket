<?php

namespace App\Support\Ventas;

use App\Jobs\ProcesarNotaCreditoSunat;
use App\Models\Cliente;
use App\Models\DetalleDocumento;
use App\Models\Documento;
use App\Models\DocumentoReferencium;
use App\Models\LotePresentacion;
use App\Models\MovimientoInventario;
use App\Models\Serie;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AnulacionService
{
    public const MOTIVOS = [
        '01' => 'Anulación de la operación',
        '02' => 'Anulación por error en el RUC',
        '03' => 'Corrección por error en la descripción',
        '04' => 'Descuento global',
        '05' => 'Descuento por ítem',
        '06' => 'Devolución total',
        '07' => 'Devolución por ítem',
        '08' => 'Bonificación',
        '09' => 'Disminución en el valor',
        '10' => 'Otros conceptos',
    ];

    public function __construct(
        protected VentaCalculator $calculator,
        protected PuntosService $puntosService,
    ) {}

    /**
     * Anula un documento creando una Nota de Crédito y restaurando el stock.
     *
     * @param User $user Usuario que realiza la anulación
     * @param Documento $documento Documento original a anular
     * @param string $motivoCodigo Código de motivo (01-10)
     * @param string $motivoDescripcion Descripción detallada del motivo
     * @return Documento La Nota de Crédito creada
     */
    public function anular(User $user, Documento $documento, string $motivoCodigo = '01', string $motivoDescripcion = 'Anulación de la operación'): Documento
    {
        if ($documento->estado === false || $documento->estado === 'ANULADO') {
            throw new \RuntimeException('El documento ya se encuentra anulado.');
        }

        if (! in_array($documento->tipo_comprobante, ['FACTURA', 'BOLETA', 'TICKET'], true)) {
            throw new \RuntimeException('Solo se pueden anular Facturas, Boletas y Tickets.');
        }

        $documento->loadMissing([
            'detalles',
            'empresa',
            'sucursal',
            'cliente',
        ]);

        $empresa = $documento->empresa;
        $sucursalId = (int) $documento->sucursal_id;
        $tipoComprobante = $documento->tipo_comprobante;

        // Determinar serie para NC
        $ncTipo = 'NOTA_CREDITO';
        $serie = Serie::query()
            ->where('sucursal_id', $sucursalId)
            ->where('tipo_comprobante', $ncTipo)
            ->lockForUpdate()
            ->first();

        if (! $serie) {
            $serie = Serie::create([
                'sucursal_id' => $sucursalId,
                'tipo_comprobante' => $ncTipo,
                'serie' => 'N001',
                'correlativo' => 1,
            ]);
        }

        $notaCredito = DB::transaction(function () use (
            $user, $documento, $empresa, $sucursalId, $tipoComprobante,
            $serie, $motivoCodigo, $motivoDescripcion
        ): Documento {
            // 1. Restaurar stock
            $this->restaurarStock($documento, $sucursalId, $user->id, $empresa->id);

            // 2. Crear documento NC
            $nota = Documento::create([
                'caja_sesion_id' => $documento->caja_sesion_id,
                'sucursal_id' => $sucursalId,
                'empresa_id' => $empresa->id,
                'cliente_id' => $documento->cliente_id,
                'user_id' => $user->id,
                'tipo_comprobante' => 'NOTA_CREDITO',
                'serie' => $serie->serie,
                'numero' => str_pad((string) $serie->correlativo, 8, '0', STR_PAD_LEFT),
                'fecha_emision' => now()->toDateString(),
                'total_bruto' => $documento->total_bruto,
                'total_descuento' => $documento->total_descuento,
                'subtotal' => $documento->subtotal,
                'total_neto' => $documento->total_neto,
                'op_gravada' => $documento->op_gravada,
                'op_exonerada' => $documento->op_exonerada,
                'op_inafecta' => $documento->op_inafecta,
                'total_igv' => $documento->total_igv,
                'porcentaje_igv' => $documento->porcentaje_igv,
                'tipo_moneda' => $documento->tipo_moneda ?? 'PEN',
                'medio_pago' => $documento->medio_pago,
                'monto_recibido' => 0,
                'vuelto' => 0,
                'puntos_ganados' => 0,
                'puntos_canjeados' => 0,
                'descuento_puntos' => 0,
                'referencia_pago' => null,
                'estado' => true,
                'observaciones' => "Anula {$tipoComprobante} {$documento->serie}-{$documento->numero}. Motivo: {$motivoDescripcion}",
                'estado_sunat' => 'PENDIENTE',
            ]);

            // 3. Crear detalles NC (copia de los items originales)
            foreach ($documento->detalles as $detalle) {
                DetalleDocumento::create([
                    'documento_id' => $nota->id,
                    'lote_id' => $detalle->lote_id,
                    'producto_id' => $detalle->producto_id,
                    'producto_nombre' => $detalle->producto_nombre,
                    'producto_presentacion_id' => $detalle->producto_presentacion_id,
                    'producto_sucursal_id' => $detalle->producto_sucursal_id,
                    'cantidad' => $detalle->cantidad,
                    'precio_unitario' => $detalle->precio_unitario,
                    'valor_unitario' => $detalle->valor_unitario,
                    'igv' => $detalle->igv,
                    'total_igv' => $detalle->total_igv,
                    'tipo_afectacion' => $detalle->tipo_afectacion,
                    'descuento_unitario' => $detalle->descuento_unitario,
                    'subtotal_bruto' => $detalle->subtotal_bruto,
                    'subtotal_descuento' => $detalle->subtotal_descuento,
                    'subtotal_neto' => $detalle->subtotal_neto,
                    'total_linea' => $detalle->total_linea,
                ]);
            }

            // 4. Crear documento_referencia (NC → documento original)
            DocumentoReferencium::create([
                'documento_id' => $nota->id,
                'tipo_relacion' => 'NOTA_CREDITO',
                'documento_referenciado_id' => $documento->id,
                'tipo_documento_ref' => $tipoComprobante,
                'serie_ref' => $documento->serie,
                'numero_ref' => $documento->numero,
                'motivo_codigo' => $motivoCodigo,
                'motivo_descripcion' => $motivoDescripcion,
                'fecha_emision_ref' => $documento->fecha_emision,
                'moneda_ref' => $documento->tipo_moneda,
            ]);

            // 5. Marcar documento original como ANULADO
            $documento->update([
                'estado' => false,
                'estado_sunat' => 'ANULADO',
            ]);

            // 6. Incrementar correlativo
            $serie->increment('correlativo');

            // 7. Revertir puntos si los hubo
            if ($documento->cliente_id && $documento->puntos_ganados > 0) {
                $cliente = Cliente::find($documento->cliente_id);
                if ($cliente) {
                    $this->puntosService->registrarReversion(
                        cliente: $cliente,
                        empresaId: $empresa->id,
                        sucursalId: $sucursalId,
                        userId: $user->id,
                        puntos: $documento->puntos_ganados,
                        motivo: "Anulación {$nota->serie}-{$nota->numero}",
                    );
                }
            }

            return $nota;
        });

        // Encolar envío a SUNAT (si el original era FACTURA o BOLETA)
        if (in_array($tipoComprobante, ['FACTURA', 'BOLETA'], true)) {
            ProcesarNotaCreditoSunat::dispatch($notaCredito, $documento);
        }

        return $notaCredito->fresh([
            'cliente',
            'empresa',
            'sucursal',
            'detalles.presentacion.unidadMedida',
            'documentoReferencia',
        ]);
    }

    /**
     * Restaura el stock de todos los ítems del documento.
     */
    protected function restaurarStock(Documento $documento, int $sucursalId, int $userId, int $empresaId): void
    {
        foreach ($documento->detalles as $detalle) {
            $presentacionId = $detalle->producto_presentacion_id;
            $cantidad = (float) $detalle->cantidad;

            if (! $presentacionId || $cantidad <= 0) {
                continue;
            }

            // Buscar el lote_presentacion más reciente para esta presentación
            $lotePresentacion = LotePresentacion::query()
                ->where('producto_presentacion_id', $presentacionId)
                ->whereHas('productoSucursal', fn ($q) => $q
                    ->where('sucursal_id', $sucursalId)
                    ->where('activo', true))
                ->orderByDesc('id')
                ->first();

            if ($lotePresentacion) {
                $lotePresentacion->increment('stock', $cantidad);
            }

            // Registrar movimiento de inventario
            MovimientoInventario::create([
                'empresa_id' => $empresaId,
                'sucursal_id' => $sucursalId,
                'producto_nombre' => $detalle->producto_nombre,
                'producto_presentacion_id' => $presentacionId,
                'tipo' => 'entrada_anulacion',
                'cantidad' => $cantidad,
                'motivo' => "Anulación {$documento->serie}-{$documento->numero}",
                'referencia' => "Documento:{$documento->id}",
                'user_id' => $userId,
                'stock_final' => $lotePresentacion?->fresh()?->stock,
            ]);
        }
    }
}
