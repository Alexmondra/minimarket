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
    protected $signature = 'minimarket:limpiar-codigos-internos {--dry-run : Solo mostrar qué cambios se realizarían sin modificar la base de datos}';

    protected $description = 'Normaliza los códigos internos de los productos que tengan códigos de barra numéricos y los convierte a formato SKU (4 letras + 4 caracteres)';

    public function handle(): int
    {
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
                    $this->line("  -> La presentación ya tenía este código de barra. Solo se limpiará el código interno del producto padre.");
                } else {
                    $this->line("  -> Este código no estaba en ninguna presentación (código desvinculado o empaque diferente). Se libera para evitar cruces.");
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
}
