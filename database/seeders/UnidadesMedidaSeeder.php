<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnidadesMedidaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fecha = Carbon::now();

        DB::table('unidades_medida')->insert([
            ['nombre' => 'Unidad', 'abreviatura' => 'und', 'activo' => true, 'created_at' => $fecha, 'updated_at' => $fecha],
            ['nombre' => 'Kilogramo', 'abreviatura' => 'kg', 'activo' => true, 'created_at' => $fecha, 'updated_at' => $fecha],
            ['nombre' => 'Litro', 'abreviatura' => 'L', 'activo' => true, 'created_at' => $fecha, 'updated_at' => $fecha],
            ['nombre' => 'Mililitro', 'abreviatura' => 'ml', 'activo' => true, 'created_at' => $fecha, 'updated_at' => $fecha],
            ['nombre' => 'Paquete', 'abreviatura' => 'paq', 'activo' => true, 'created_at' => $fecha, 'updated_at' => $fecha],
            ['nombre' => 'Caja', 'abreviatura' => 'caja', 'activo' => true, 'created_at' => $fecha, 'updated_at' => $fecha],
            ['nombre' => 'Docena', 'abreviatura' => 'dz', 'activo' => true, 'created_at' => $fecha, 'updated_at' => $fecha],
            ['nombre' => 'Saco', 'abreviatura' => 'saco', 'activo' => true, 'created_at' => $fecha, 'updated_at' => $fecha],
            ['nombre' => 'Tubo', 'abreviatura' => 'tubo', 'activo' => true, 'created_at' => $fecha, 'updated_at' => $fecha],
            ['nombre' => 'Frasco', 'abreviatura' => 'frasco', 'activo' => true, 'created_at' => $fecha, 'updated_at' => $fecha],
        ]);
    }
}
