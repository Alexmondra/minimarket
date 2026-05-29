<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MarcaSeeder extends Seeder
{
    public function run(): void
    {
        $marcas = [
            // Abarrotes y básicos
            ['empresa_id' => 1, 'nombre' => 'Nicolini',         'descripcion' => 'Pastas y fideos.'],
            ['empresa_id' => 1, 'nombre' => 'Costeño',          'descripcion' => 'Arroz, menestras y granos.'],
            ['empresa_id' => 1, 'nombre' => 'Molitalia',        'descripcion' => 'Pastas, salsas y harinas.'],
            ['empresa_id' => 1, 'nombre' => 'Wong',             'descripcion' => 'Marca propia de abarrotes selectos.'],
            ['empresa_id' => 1, 'nombre' => 'Tottus',           'descripcion' => 'Marca propia de abarrotes económicos.'],

            // Lácteos
            ['empresa_id' => 1, 'nombre' => 'Gloria',           'descripcion' => 'Leche, yogures y derivados lácteos.'],
            ['empresa_id' => 1, 'nombre' => 'Laive',            'descripcion' => 'Quesos, yogures y mantequilla.'],
            ['empresa_id' => 1, 'nombre' => 'Pura Vida',        'descripcion' => 'Leche fresca y productos lácteos nutritivos.'],

            // Bebidas
            ['empresa_id' => 1, 'nombre' => 'Coca-Cola',        'descripcion' => 'Gaseosas y refrescos.'],
            ['empresa_id' => 1, 'nombre' => 'Inca Kola',        'descripcion' => 'Gaseosa peruana icónica.'],
            ['empresa_id' => 1, 'nombre' => 'San Luis',         'descripcion' => 'Agua mineral y de mesa.'],
            ['empresa_id' => 1, 'nombre' => 'San Mateo',        'descripcion' => 'Agua mineral natural.'],
            ['empresa_id' => 1, 'nombre' => 'Cielo',            'descripcion' => 'Agua de mesa.'],
            ['empresa_id' => 1, 'nombre' => 'Fanta',            'descripcion' => 'Gaseosas de sabores frutales.'],
            ['empresa_id' => 1, 'nombre' => 'Sprite',           'descripcion' => 'Gaseosa lima-limón.'],

            // Cuidado personal
            ['empresa_id' => 1, 'nombre' => 'Protex',           'descripcion' => 'Jabones y cuidado antibacterial.'],
            ['empresa_id' => 1, 'nombre' => 'Asepxia',          'descripcion' => 'Cuidado facial y antiacné.'],
            ['empresa_id' => 1, 'nombre' => 'Head & Shoulders', 'descripcion' => 'Shampoo anticaspa.'],
            ['empresa_id' => 1, 'nombre' => 'Colgate',          'descripcion' => 'Pastas y cepillos dentales.'],
            ['empresa_id' => 1, 'nombre' => 'Nivea',            'descripcion' => 'Cremas corporales y faciales.'],

            // Limpieza
            ['empresa_id' => 1, 'nombre' => 'Sapolio',          'descripcion' => 'Detergentes y lava vajillas.'],
            ['empresa_id' => 1, 'nombre' => 'Ariel',            'descripcion' => 'Detergente para ropa.'],
            ['empresa_id' => 1, 'nombre' => 'Patito',           'descripcion' => 'Limpiadores multiuso.'],
            ['empresa_id' => 1, 'nombre' => 'Poett',            'descripcion' => 'Aromatizantes y limpiadores.'],

            // Snacks y golosinas
            ['empresa_id' => 1, 'nombre' => 'Lays',             'descripcion' => 'Papitas fritas y snacks.'],
            ['empresa_id' => 1, 'nombre' => 'Sublime',          'descripcion' => 'Chocolate peruano clásico.'],
            ['empresa_id' => 1, 'nombre' => 'Negrita',          'descripcion' => 'Galletas dulces.'],
            ['empresa_id' => 1, 'nombre' => 'Oreo',             'descripcion' => 'Galletas rellenas de crema.'],

            // Embutidos
            ['empresa_id' => 1, 'nombre' => 'San Fernando',     'descripcion' => 'Pollo, pavo y embutidos.'],
            ['empresa_id' => 1, 'nombre' => 'Razzeto',          'descripcion' => 'Embutidos y fiambres premium.'],
        ];

        DB::table('marcas')->insert($marcas);
    }
}
