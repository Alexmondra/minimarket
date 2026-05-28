<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Lote;
use App\Models\LotePresentacion;
use App\Models\Producto;
use App\Models\ProductoPresentacion;
use App\Models\ProductoSucursal;
use App\Models\Sucursal;
use App\Models\UniMedida;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class POSDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Desactivar llaves foráneas para limpieza limpia
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('producto_sucursal')->truncate();
        DB::table('lote_presentacion')->truncate();
        DB::table('lotes')->truncate();
        DB::table('producto_presentacion')->truncate();
        DB::table('productos')->truncate();
        DB::table('categorias')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $empresaId = 1; // El Ahorro S.A.C.
        $sucursales = Sucursal::where('empresa_id', $empresaId)->get();

        if ($sucursales->isEmpty()) {
            $this->command->error('No se encontraron sucursales para la empresa ID 1. Ejecuta primero EmpresaSucursalSeeder.');

            return;
        }

        // 1. Obtener unidades de medida
        $und = UniMedida::where('abreviatura', 'und')->first() ?: UniMedida::create(['nombre' => 'Unidad', 'abreviatura' => 'und', 'activo' => true]);
        $bot = UniMedida::where('abreviatura', 'bot')->first() ?: UniMedida::create(['nombre' => 'Botella', 'abreviatura' => 'bot', 'activo' => true]);
        $caja = UniMedida::where('abreviatura', 'caja')->first() ?: UniMedida::create(['nombre' => 'Caja', 'abreviatura' => 'caja', 'activo' => true]);
        $paq = UniMedida::where('abreviatura', 'paq')->first() ?: UniMedida::create(['nombre' => 'Paquete', 'abreviatura' => 'paq', 'activo' => true]);
        $bolsa = UniMedida::where('abreviatura', 'bolsa')->first() ?: UniMedida::create(['nombre' => 'Bolsa', 'abreviatura' => 'bolsa', 'activo' => true]);

        // 2. Crear Categorías
        $catBebidas = Categoria::create(['empresa_id' => $empresaId, 'nombre' => 'Bebidas', 'descripcion' => 'Refrescos, aguas y gaseosas', 'estado' => true]);
        $catLacteos = Categoria::create(['empresa_id' => $empresaId, 'nombre' => 'Lácteos', 'descripcion' => 'Leche, quesos, yogures', 'estado' => true]);
        $catAbarrotes = Categoria::create(['empresa_id' => $empresaId, 'nombre' => 'Abarrotes', 'descripcion' => 'Arroz, azúcar, aceites', 'estado' => true]);
        $catPanaderia = Categoria::create(['empresa_id' => $empresaId, 'nombre' => 'Panadería', 'descripcion' => 'Panes, tostadas, tortas', 'estado' => true]);
        $catSnacks = Categoria::create(['empresa_id' => $empresaId, 'nombre' => 'Snacks', 'descripcion' => 'Papas, galletas, golosinas', 'estado' => true]);
        $catHuevos = Categoria::create(['empresa_id' => $empresaId, 'nombre' => 'Huevos', 'descripcion' => 'Huevos de gallina y codorniz', 'estado' => true]);
        $catLimpieza = Categoria::create(['empresa_id' => $empresaId, 'nombre' => 'Limpieza', 'descripcion' => 'Detergentes, jabones, desinfectantes', 'estado' => true]);
        $catOtros = Categoria::create(['empresa_id' => $empresaId, 'nombre' => 'Otros', 'descripcion' => 'Productos diversos', 'estado' => true]);

        // 3. Crear Productos del Mockup
        $productosData = [
            [
                'nombre' => 'Agua sin gas 1.5 L',
                'categoria_id' => $catBebidas->id,
                'codigo_interno' => 'AGU001',
                'codigo_barra' => '7750123456789',
                'precio' => 2.00,
                'precio_mayorista' => 1.80,
                'minimo_mayorista' => 6,
                'stock' => 120,
                'unidad_id' => $bot->id,
                'tipo_presentacion' => 'Botella 1.5L',
            ],
            [
                'nombre' => 'Gaseosa Cola 2.5 L',
                'categoria_id' => $catBebidas->id,
                'codigo_interno' => 'GAS001',
                'codigo_barra' => '7750987654321',
                'precio' => 6.50,
                'precio_mayorista' => 6.00,
                'minimo_mayorista' => 4,
                'stock' => 85,
                'unidad_id' => $bot->id,
                'tipo_presentacion' => 'Botella 2.5L',
            ],
            [
                'nombre' => 'Pan de molde Bimbo',
                'categoria_id' => $catPanaderia->id,
                'codigo_interno' => 'PAN001',
                'codigo_barra' => '7751112223334',
                'precio' => 4.20,
                'precio_mayorista' => 3.90,
                'minimo_mayorista' => 3,
                'stock' => 45,
                'unidad_id' => $bolsa->id,
                'tipo_presentacion' => 'Bolsa 480g',
            ],
            [
                'nombre' => 'Leche entera Gloria 1 L',
                'categoria_id' => $catLacteos->id,
                'codigo_interno' => 'LEC001',
                'codigo_barra' => '7754445556667',
                'precio' => 4.80,
                'precio_mayorista' => 4.50,
                'minimo_mayorista' => 12,
                'stock' => 150,
                'unidad_id' => $caja->id,
                'tipo_presentacion' => 'Caja UHT 1L',
            ],
            [
                'nombre' => 'Snack Papas Clásicas 160g',
                'categoria_id' => $catSnacks->id,
                'codigo_interno' => 'SNK001',
                'codigo_barra' => '7757778889990',
                'precio' => 5.00,
                'precio_mayorista' => 4.60,
                'minimo_mayorista' => 5,
                'stock' => 95,
                'unidad_id' => $bolsa->id,
                'tipo_presentacion' => 'Bolsa 160g',
            ],
            [
                'nombre' => 'Arroz extra 5 kg',
                'categoria_id' => $catAbarrotes->id,
                'codigo_interno' => 'ARR001',
                'codigo_barra' => '7758888999911',
                'precio' => 18.00,
                'precio_mayorista' => 17.20,
                'minimo_mayorista' => 3,
                'stock' => 50,
                'unidad_id' => $bolsa->id,
                'tipo_presentacion' => 'Saco 5kg',
            ],
            [
                'nombre' => 'Huevos pardos x 30 und.',
                'categoria_id' => $catHuevos->id,
                'codigo_interno' => 'HUE001',
                'codigo_barra' => '7750000111222',
                'precio' => 15.00,
                'precio_mayorista' => 14.00,
                'minimo_mayorista' => 2,
                'stock' => 35,
                'unidad_id' => $paq->id,
                'tipo_presentacion' => 'Paquete 30und',
            ],
            [
                'nombre' => 'Detergente Opal 800g',
                'categoria_id' => $catLimpieza->id,
                'codigo_interno' => 'OPA001',
                'codigo_barra' => '7756667778889',
                'precio' => 9.20,
                'precio_mayorista' => 8.50,
                'minimo_mayorista' => 3,
                'stock' => 60,
                'unidad_id' => $bolsa->id,
                'tipo_presentacion' => 'Bolsa 800g',
            ],
        ];

        foreach ($productosData as $data) {
            $producto = Producto::create([
                'empresa_id' => $empresaId,
                'categoria_id' => $data['categoria_id'],
                'marca_id' => null,
                'codigo_interno' => $data['codigo_interno'],
                'nombre' => $data['nombre'],
                'slug' => Str::slug($data['nombre']),
                'descripcion' => $data['nombre'],
                'afecto_igv' => true,
                'activo' => true,
            ]);

            $presentacion = ProductoPresentacion::create([
                'producto_id' => $producto->id,
                'unidad_medida_id' => $data['unidad_id'],
                'cantidad' => 1,
                'tipo_presentacion' => $data['tipo_presentacion'],
                'imagen' => null,
                'codigo_barra' => $data['codigo_barra'],
                'es_pesable' => false,
            ]);

            // Asignar a todas las sucursales con lotes
            foreach ($sucursales as $sucursal) {
                $lote = Lote::create([
                    'sucursal_id' => $sucursal->id,
                    'codigo_lote' => 'LOTE-'.strtoupper(Str::random(6)),
                    'producto_nombre' => $producto->nombre,
                    'fecha_fabricacion' => now()->subMonths(1),
                    'fecha_vencimiento' => now()->addYear(),
                    'precio_compra' => round($data['precio'] * 0.7, 2),
                    'estado_lote' => 'activo',
                ]);

                $lotePresentacion = LotePresentacion::create([
                    'lote_id' => $lote->id,
                    'producto_presentacion_id' => $presentacion->id,
                    'stock' => $data['stock'],
                    'precio_oferta' => null,
                ]);

                ProductoSucursal::create([
                    'producto_id' => $producto->id,
                    'sucursal_id' => $sucursal->id,
                    'lote_presentacion_id' => $lotePresentacion->id,
                    'stock_minimo' => 5,
                    'precio' => $data['precio'],
                    'minimo_mayorista' => $data['minimo_mayorista'],
                    'precio_mayorista' => $data['precio_mayorista'],
                    'activo' => true,
                ]);
            }
        }

        $this->command->info('✅ POSDemoSeeder completado: Categorías y productos del mockup creados con éxito.');
    }
}
