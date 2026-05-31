<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use OpenSpout\Reader\XLSX\Reader;

/**
 * Class UbigeosTableSeeder
 *
 * Seeds the ubigeos table using the data provided in the geodir-ubigeo-reniec.xlsx spreadsheet.
 *
 * @package Database\Seeders
 */
class UbigeosTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $filePath = database_path('seeders/data/geodir-ubigeo-reniec.xlsx');

        if (!file_exists($filePath)) {
            $this->command->error("El archivo de ubigeos no existe en la ruta: {$filePath}");
            return;
        }

        $this->command->info('Leyendo archivo de ubigeos...');

        // Cargar los ubigeos ya registrados para evitar duplicidades
        $existing = DB::table('ubigeos')->pluck('ubigeo')->flip()->toArray();

        $reader = new Reader();
        $reader->open($filePath);

        $insertData = [];
        $now = now();
        $isFirstRow = true;

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $cells = $row->toArray();

                if ($isFirstRow) {
                    $isFirstRow = false;
                    continue; // Saltar la fila de cabecera
                }

                if (empty($cells[0])) {
                    continue;
                }

                $ubigeo = str_pad((string)$cells[0], 6, '0', STR_PAD_LEFT);

                // Evitar duplicados si ya existe en la base de datos
                if (isset($existing[$ubigeo])) {
                    continue;
                }

                $insertData[] = [
                    'ubigeo'         => $ubigeo,
                    'distrito'       => $cells[1] ?? '',
                    'provincia'      => $cells[2] ?? '',
                    'departamento'   => $cells[3] ?? '',
                    'Superficie'     => isset($cells[5]) ? (string)$cells[5] : null,
                    'Y'              => isset($cells[6]) ? (string)$cells[6] : null,
                    'x'              => isset($cells[7]) ? (string)$cells[7] : null,
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ];
            }
            break; // Procesar solo la primera pestaña
        }
        $reader->close();

        $count = count($insertData);
        if ($count > 0) {
            $this->command->info("Insertando {$count} registros de ubigeo...");
            DB::transaction(function () use ($insertData) {
                foreach (array_chunk($insertData, 500) as $chunk) {
                    DB::table('ubigeos')->insert($chunk);
                }
            });
            $this->command->info('✅ Ubigeos migrados con éxito.');
        } else {
            $this->command->info('No se encontraron nuevos ubigeos para insertar.');
        }
    }
}

