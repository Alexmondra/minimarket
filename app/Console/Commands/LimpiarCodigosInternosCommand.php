<?php

namespace App\Console\Commands;

use App\Models\Producto;
use App\Models\ProductoPresentacion;
use App\Models\ProductoPresentacionBarra;
use App\Models\UniMedida;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class LimpiarCodigosInternosCommand extends Command
{
    protected $signature = 'minimarket:limpiar-codigos-internos {--dry-run : Solo mostrar qué cambios se realizarían sin modificar la base de datos} {--rollback : Revertir los cambios y restaurar los códigos numéricos anteriores}';

    protected $description = 'Normaliza los códigos internos de los productos que tengan códigos de barra numéricos y los convierte a formato SKU (4 letras + 4 caracteres)';

    /**
     * Registro histórico exacto de los códigos numéricos que tenían los productos antes de la normalización.
     */
    protected array $historialAnterior = [
        6 => '9780201379624',
        7 => '7752286000061',
        196 => '7758574006722',
        208 => '7896004009612',
        281 => '7622202394799',
        286 => '9334980006372',
        287 => '7751493010269',
        288 => '7750182006088',
        289 => '7590002008898',
        296 => '7702010102660',
        368 => '650240051029',
        369 => '7702031338574',
        370 => '7702031338260',
        427 => '7756847127495',
        428 => '7568471274403',
        429 => '7622201776633',
        430 => '7622201776664',
        431 => '7622202272172',
        444 => '7750670013437',
        453 => '7750670013413',
        454 => '7750670013420',
        455 => '7750670014892',
        456 => '7750670016735',
        467 => '4891228530136',
    ];

    public function handle(): int
    {
        if ($this->option('rollback')) {
            return $this->ejecutarRollback();
        }

        $dryRun = (bool) $this->option('dry-run');

        $this->info('Iniciando inspección de códigos internos numéricos en productos...');

        // Buscar productos cuyo código interno sea puramente numérico y de longitud típica de código de barra (8 a 14 dígitos)
        $productos = Producto::query()
            ->whereNotNull('codigo_interno')
            ->whereRaw("LENGTH(codigo_interno) >= 8 AND codigo_interno REGEXP '^[0-9]+$'")
            ->with(['presentaciones.barras'])
            ->get();

        if ($productos->isEmpty()) {
            $this->info('No se encontraron productos con códigos internos tipo código de barras.');
            return self::SUCCESS;
        }

        $this->warn("Se encontraron {$productos->count()} productos con código interno numérico tipo código de barras.");

        $actualizados = 0;

        foreach ($productos as $producto) {
            $codigoBarraAntiguo = trim($producto->codigo_interno);
            $nuevoSku = Producto::generarCodigoInterno($producto->nombre);

            $this->line("--------------------------------------------------");
            $this->line("Producto ID {$producto->id}: <comment>{$producto->nombre}</comment>");
            $this->line("  Código interno actual: <error>{$codigoBarraAntiguo}</error> -> Nuevo SKU: <info>{$nuevoSku}</info>");

            $presentaciones = $producto->presentaciones;

            if ($presentaciones->isEmpty()) {
                $this->line("  [Sin presentaciones] Se crearía presentación Unidad con código {$codigoBarraAntiguo}.");
            } else {
                $tieneBarra = false;
                foreach ($presentaciones as $pres) {
                    $barras = $pres->barras->pluck('codigo_barra')->toArray();
                    if (in_array($codigoBarraAntiguo, $barras, true)) {
                        $tieneBarra = true;
                    }
                    $this->line("  Presentación ID {$pres->id} ({$pres->tipo_presentacion}): Barras actuales: " . implode(', ', $barras ?: ['Ninguno']));
                }

                if ($tieneBarra) {
                    $this->line("  -> La presentación ya tenía este código de barra. Se limpiará el código interno a SKU.");
                } else {
                    $this->warn("  -> Este código NO está en ninguna presentación. SE MANTIENE INTACTO para resolver en el POS (vincular o crear nueva presentación).");
                    continue;
                }
            }

            if (! $dryRun) {
                DB::transaction(function () use ($producto, $nuevoSku, $codigoBarraAntiguo, $presentaciones) {
                    // Si no tenía ninguna presentación, creamos la básica
                    if ($presentaciones->isEmpty()) {
                        $unidad = UniMedida::where('abreviatura', 'und')->first() ?? UniMedida::first();
                        $pres = ProductoPresentacion::create([
                            'producto_id' => $producto->id,
                            'unidad_medida_id' => $unidad?->id ?? 1,
                            'cantidad' => 1,
                            'tipo_presentacion' => 'Unidad',
                            'es_pesable' => false,
                        ]);

                        ProductoPresentacionBarra::firstOrCreate(
                            ['codigo_barra' => $codigoBarraAntiguo],
                            ['producto_presentacion_id' => $pres->id]
                        );
                    }

                    // Actualizar el código interno del producto
                    $producto->update(['codigo_interno' => $nuevoSku]);
                });

                $actualizados++;
            }
        }

        if ($dryRun) {
            $this->warn("Modo DRY-RUN finalizado. No se modificó ningún registro.");
        } else {
            $this->info("Proceso completado exitosamente. {$actualizados} productos actualizados con SKU limpio.");
        }

        return self::SUCCESS;
    }

    /**
     * Revierte los cambios y restaura los códigos anteriores de cada producto.
     */
    protected function ejecutarRollback(): int
    {
        $this->warn('Iniciando ROLLBACK: Restaurando los códigos internos numéricos anteriores...');

        $restaurados = 0;

        DB::transaction(function () use (&$restaurados) {
            foreach ($this->historialAnterior as $id => $codigoAnterior) {
                $producto = Producto::find($id);
                if ($producto) {
                    $producto->update(['codigo_interno' => $codigoAnterior]);
                    $this->line("Producto ID {$id} ({$producto->nombre}): Código interno restaurado a <info>{$codigoAnterior}</info>");
                    $restaurados++;
                }
            }
        });

        $this->info("ROLLBACK completado. Se restauraron los códigos internos de {$restaurados} productos a su estado original.");

        return self::SUCCESS;
    }
}
