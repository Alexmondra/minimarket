<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class UbigeosTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Deshabilitar FK checks para poder truncar
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('ubigeos')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Ruta al archivo SQL
        $sqlFile = database_path('seeders/data/ubigeos_202605182054.sql');

        if (!File::exists($sqlFile)) {
            $this->command->error("Archivo SQL no encontrado: {$sqlFile}");
            return;
        }

        // Leer todo el contenido del archivo
        $sql = File::get($sqlFile);

        // Ejecutar el SQL directamente
        DB::unprepared($sql);

        $this->command->info('Ubigeos insertados correctamente desde SQL.');
    }
}