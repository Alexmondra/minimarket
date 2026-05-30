<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Models\Lote;
use App\Models\LotePresentacion;
use App\Models\LotePresentacionMerma;
use App\Models\MovimientoInventario;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

#[Signature('app:procesar-lotes-vencidos')]
#[Description('Procesa los lotes vencidos para marcarlos como pendientes para confirmar como mermas.')]
class ProcesarLotesVencidos extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();
        $this->info("Iniciando procesamiento de lotes vencidos para la fecha: {$today->toDateString()}");

        // 1. Obtener lotes activos/vencidos que ya expiraron (fecha_vencimiento <= hoy)
        $lotesVencidos = Lote::query()
            ->with(['lotePresentaciones.productoPresentacion.producto', 'sucursal'])
            ->whereIn('estado_lote', ['activo', 'vencido'])
            ->whereDate('fecha_vencimiento', '<=', $today)
            ->get();

        $this->info("Se encontraron {$lotesVencidos->count()} lotes vencidos/por vencer para verificar.");

        foreach ($lotesVencidos as $lote) {
            $daysExpired = $lote->fecha_vencimiento->startOfDay()->diffInDays($today->startOfDay(), false);
            
            // Verificar si el lote tiene stock real
            $stockTotal = $lote->stock_total;
            $hasPending = $lote->lotePresentaciones()->where('estado', LotePresentacion::ESTADO_PENDIENTE)->exists();

            if ($stockTotal <= 0 && !$hasPending) {
                // Si ya no tiene stock y no tiene mermas pendientes de confirmar, marcar como agotado
                if ($lote->estado_lote !== 'agotado') {
                    $lote->update(['estado_lote' => 'agotado']);
                    $this->line("Lote {$lote->codigo_lote} marcado como agotado porque no tiene stock.");
                }
                continue;
            }

            // Proceder a auto-mermar las presentaciones que tienen stock > 0 y están en estado activo
            $presentacionesParaMermar = $lote->lotePresentaciones()
                ->where('stock', '>', 0)
                ->where('estado', 'activo')
                ->get();

            if ($presentacionesParaMermar->isNotEmpty()) {
                $this->warn("Lote {$lote->codigo_lote} venció hace {$daysExpired} días. Procediendo a auto-mermar en estado PENDIENTE.");

                DB::transaction(function () use ($lote, $presentacionesParaMermar) {
                    if ($lote->estado_lote !== 'vencido') {
                        $lote->update(['estado_lote' => 'vencido']);
                    }

                    foreach ($presentacionesParaMermar as $lp) {
                        $cantidad = $lp->stock;

                        // Registrar la Merma Automática (sin user_id, pendiente de confirmar)
                        LotePresentacionMerma::create([
                            'lote_presentacion_id' => $lp->id,
                            'cantidad' => $cantidad,
                            'tipo_merma' => 'vencido',
                            'motivo' => 'Vencimiento automático de lote (pendiente de confirmar)',
                            'user_id' => null,
                        ]);

                        // Registrar la salida en Kardex (Movimientos de Inventario)
                        MovimientoInventario::create([
                            'empresa_id' => $lote->sucursal?->empresa_id ?? 1,
                            'sucursal_id' => $lote->sucursal_id,
                            'producto_nombre' => $lp->productoPresentacion?->producto?->nombre ?? $lote->producto_nombre,
                            'producto_presentacion_id' => $lp->producto_presentacion_id,
                            'tipo' => 'salida_merma',
                            'cantidad' => -$cantidad,
                            'motivo' => "Merma automática (Vencimiento) - Lote {$lote->codigo_lote}",
                            'referencia' => "LotePresentacion:{$lp->id}",
                            'user_id' => null,
                            'stock_final' => 0,
                        ]);

                        // Actualizar stock a 0 y estado de la presentación a PENDIENTE (pendiente de confirmar)
                        $lp->update([
                            'stock' => 0,
                            'estado' => LotePresentacion::ESTADO_PENDIENTE,
                        ]);
                    }
                });

                $this->info("Presentaciones del lote {$lote->codigo_lote} auto-mermadas y marcadas como pendientes.");
            }
        }

        // 3. Chequeo de seguridad: lotes que no están vencidos pero tienen stock = 0 deben ser 'agotado'
        $lotesAgotados = Lote::query()
            ->whereIn('estado_lote', ['activo', 'vencido'])
            ->whereDate('fecha_vencimiento', '>', $today)
            ->get();

        foreach ($lotesAgotados as $lote) {
            if ($lote->stock_total <= 0) {
                $lote->update(['estado_lote' => 'agotado']);
                $this->line("Lote activo {$lote->codigo_lote} marcado como agotado por stock 0.");
            }
        }

        $this->info("Procesamiento de lotes completado.");
    }
}
