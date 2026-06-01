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

        // 1. Obtener lotes activos/vencidos/por_confirmar que ya expiraron (fecha_vencimiento <= hoy)
        $lotesVencidos = Lote::query()
            ->with(['lotePresentaciones.productoPresentacion.producto', 'sucursal'])
            ->whereIn('estado_lote', ['activo', 'vencido', 'por_confirmar'])
            ->whereDate('fecha_vencimiento', '<=', $today)
            ->get();

        $this->info("Se encontraron {$lotesVencidos->count()} lotes vencidos/por vencer para verificar.");

        foreach ($lotesVencidos as $lote) {
            $daysExpired = $lote->fecha_vencimiento->startOfDay()->diffInDays($today->startOfDay(), false);
            
            // Verificar si el lote tiene stock real
            $stockTotal = $lote->stock_total;

            if ($stockTotal <= 0) {
                // Si ya no tiene stock, marcar como agotado
                if ($lote->estado_lote !== 'agotado') {
                    $lote->update(['estado_lote' => 'agotado']);
                    $this->line("Lote {$lote->codigo_lote} marcado como agotado porque no tiene stock.");
                }
                continue;
            }

            // Proceder a cambiar el estado del lote a por_confirmar sin alterar stock ni crear merma
            if ($lote->estado_lote !== 'por_confirmar') {
                $lote->update(['estado_lote' => 'por_confirmar']);
                $this->warn("Lote {$lote->codigo_lote} venció hace {$daysExpired} días. Marcado como 'por_confirmar' para merma manual.");
            }
        }

        // 3. Chequeo de seguridad: lotes activos o vencidos que no tienen stock deben ser 'agotado'
        $lotesSinStock = Lote::query()
            ->whereIn('estado_lote', ['activo', 'vencido', 'por_confirmar'])
            ->get();

        foreach ($lotesSinStock as $lote) {
            if ($lote->stock_total <= 0) {
                $lote->update(['estado_lote' => 'agotado']);
                $this->line("Lote {$lote->codigo_lote} marcado como agotado por stock 0.");
            }
        }

        $this->info("Procesamiento de lotes completado.");
    }
}
