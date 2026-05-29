<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            ['empresa_id' => 1, 'nombre' => 'Abarrotes',           'descripcion' => 'Arroz, azúcar, menestras, conservas y productos básicos de despensa.', 'estado' => true],
            ['empresa_id' => 1, 'nombre' => 'Lácteos y Huevos',     'descripcion' => 'Leche, quesos, yogures, mantequilla y huevos.',                   'estado' => true],
            ['empresa_id' => 1, 'nombre' => 'Bebidas',              'descripcion' => 'Gaseosas, aguas, jugos, néctares y bebidas energizantes.',          'estado' => true],
            ['empresa_id' => 1, 'nombre' => 'Panadería y Pastelería','descripcion' => 'Panes, pasteles, galletas y productos de repostería.',               'estado' => true],
            ['empresa_id' => 1, 'nombre' => 'Carnes y Embutidos',   'descripcion' => 'Carnes rojas, pollo, embutidos y fiambres.',                       'estado' => true],
            ['empresa_id' => 1, 'nombre' => 'Frutas y Verduras',    'descripcion' => 'Frutas frescas, verduras y hortalizas.',                           'estado' => true],
            ['empresa_id' => 1, 'nombre' => 'Limpieza y Hogar',     'descripcion' => 'Detergentes, desinfectantes, bolsas y artículos de limpieza.',     'estado' => true],
            ['empresa_id' => 1, 'nombre' => 'Cuidado Personal',     'descripcion' => 'Jabones, shampoo, cremas, desodorantes y papel higiénico.',        'estado' => true],
            ['empresa_id' => 1, 'nombre' => 'Snacks y Golosinas',   'descripcion' => 'Papitas, chocolates, caramelos, galletas dulces y frutos secos.',   'estado' => true],
            ['empresa_id' => 1, 'nombre' => 'Congelados',           'descripcion' => 'Helados, productos congelados y hielo.',                           'estado' => true],
        ];

        DB::table('categorias')->insert($categorias);
    }
}
