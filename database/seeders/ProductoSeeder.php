<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductoSeeder extends Seeder
{
    private function ean13(string $prefix, int $index): string
    {
        $base = $prefix . str_pad((string) $index, 12 - strlen($prefix), '0', STR_PAD_LEFT);
        $base = substr($base, 0, 12);
        $digits = str_split($base);
        $sum = 0;
        foreach ($digits as $i => $d) {
            $sum += ($i % 2 === 0) ? (int) $d : (int) $d * 3;
        }
        $check = (10 - ($sum % 10)) % 10;

        return $base . $check;
    }

    public function run(): void
    {
        $now = now();

        // ─── Producto 1: Arroz Extra Costeño ───
        $p1 = DB::table('productos')->insertGetId([
            'empresa_id'    => 1,
            'categoria_id'  => 1, // Abarrotes
            'marca_id'      => 2, // Costeño
            'codigo_interno'=> 'PROD-001',
            'nombre'        => 'Arroz Extra Costeño',
            'slug'          => Str::slug('Arroz Extra Costeño'),
            'descripcion'   => 'Arroz extra añejo, grano entero seleccionado. Ideal para todo tipo de comidas.',
            'afecto_igv'    => true,
            'activo'        => true,
            'created_at'    => $now,
            'updated_at'    => $now,
        ]);

        $pp1a = DB::table('producto_presentacion')->insertGetId([
            'producto_id'       => $p1,
            'unidad_medida_id'  => 2, // kg
            'cantidad'          => 1,
            'tipo_presentacion' => 'Bolsa 1kg',
            'imagen'            => null,
            'es_pesable'        => false,
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);
        DB::table('producto_presentacion_barras')->insert([
            'producto_presentacion_id' => $pp1a,
            'codigo_barra'             => $this->ean13('775', 101),
            'created_at'               => $now,
            'updated_at'               => $now,
        ]);

        $pp1b = DB::table('producto_presentacion')->insertGetId([
            'producto_id'       => $p1,
            'unidad_medida_id'  => 2, // kg
            'cantidad'          => 5,
            'tipo_presentacion' => 'Bolsa 5kg',
            'imagen'            => null,
            'es_pesable'        => false,
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);
        DB::table('producto_presentacion_barras')->insert([
            'producto_presentacion_id' => $pp1b,
            'codigo_barra'             => $this->ean13('775', 102),
            'created_at'               => $now,
            'updated_at'               => $now,
        ]);

        // ─── Producto 2: Leche Evaporada Gloria ───
        $p2 = DB::table('productos')->insertGetId([
            'empresa_id'    => 1,
            'categoria_id'  => 2, // Lácteos y Huevos
            'marca_id'      => 6, // Gloria
            'codigo_interno'=> 'PROD-002',
            'nombre'        => 'Leche Evaporada Gloria',
            'slug'          => Str::slug('Leche Evaporada Gloria'),
            'descripcion'   => 'Leche evaporada entera, fuente de calcio y proteínas. Rendidora y cremosa.',
            'afecto_igv'    => true,
            'activo'        => true,
            'created_at'    => $now,
            'updated_at'    => $now,
        ]);

        $pp2a = DB::table('producto_presentacion')->insertGetId([
            'producto_id'       => $p2,
            'unidad_medida_id'  => 4, // ml
            'cantidad'          => 400,
            'tipo_presentacion' => 'Lata 400ml',
            'imagen'            => null,
            'es_pesable'        => false,
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);
        DB::table('producto_presentacion_barras')->insert([
            'producto_presentacion_id' => $pp2a,
            'codigo_barra'             => $this->ean13('775', 201),
            'created_at'               => $now,
            'updated_at'               => $now,
        ]);

        $pp2b = DB::table('producto_presentacion')->insertGetId([
            'producto_id'       => $p2,
            'unidad_medida_id'  => 5, // paq
            'cantidad'          => 1,
            'tipo_presentacion' => 'Six Pack 400ml',
            'imagen'            => null,
            'es_pesable'        => false,
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);
        DB::table('producto_presentacion_barras')->insert([
            'producto_presentacion_id' => $pp2b,
            'codigo_barra'             => $this->ean13('775', 202),
            'created_at'               => $now,
            'updated_at'               => $now,
        ]);

        // ─── Producto 3: Inca Kola ───
        $p3 = DB::table('productos')->insertGetId([
            'empresa_id'    => 1,
            'categoria_id'  => 3, // Bebidas
            'marca_id'      => 10, // Inca Kola
            'codigo_interno'=> 'PROD-003',
            'nombre'        => 'Inca Kola',
            'slug'          => Str::slug('Inca Kola'),
            'descripcion'   => 'Gaseosa saborizada, la bebida del Perú. Hecha con hierba luisa y aceites esenciales.',
            'afecto_igv'    => true,
            'activo'        => true,
            'created_at'    => $now,
            'updated_at'    => $now,
        ]);

        $pp3a = DB::table('producto_presentacion')->insertGetId([
            'producto_id'       => $p3,
            'unidad_medida_id'  => 4, // ml
            'cantidad'          => 500,
            'tipo_presentacion' => 'Botella 500ml',
            'imagen'            => null,
            'es_pesable'        => false,
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);
        DB::table('producto_presentacion_barras')->insert([
            'producto_presentacion_id' => $pp3a,
            'codigo_barra'             => $this->ean13('775', 301),
            'created_at'               => $now,
            'updated_at'               => $now,
        ]);

        $pp3b = DB::table('producto_presentacion')->insertGetId([
            'producto_id'       => $p3,
            'unidad_medida_id'  => 3, // L
            'cantidad'          => 1.5,
            'tipo_presentacion' => 'Botella 1.5L',
            'imagen'            => null,
            'es_pesable'        => false,
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);
        DB::table('producto_presentacion_barras')->insert([
            'producto_presentacion_id' => $pp3b,
            'codigo_barra'             => $this->ean13('775', 302),
            'created_at'               => $now,
            'updated_at'               => $now,
        ]);

        $pp3c = DB::table('producto_presentacion')->insertGetId([
            'producto_id'       => $p3,
            'unidad_medida_id'  => 3, // L
            'cantidad'          => 3,
            'tipo_presentacion' => 'Botella 3L',
            'imagen'            => null,
            'es_pesable'        => false,
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);
        DB::table('producto_presentacion_barras')->insert([
            'producto_presentacion_id' => $pp3c,
            'codigo_barra'             => $this->ean13('775', 303),
            'created_at'               => $now,
            'updated_at'               => $now,
        ]);

        // ─── Producto 4: Jabón Protex Avena ───
        $p4 = DB::table('productos')->insertGetId([
            'empresa_id'    => 1,
            'categoria_id'  => 8, // Cuidado Personal
            'marca_id'      => 16, // Protex
            'codigo_interno'=> 'PROD-004',
            'nombre'        => 'Jabón Protex Avena',
            'slug'          => Str::slug('Jabón Protex Avena'),
            'descripcion'   => 'Jabón antibacterial con extracto de avena. Limpieza profunda y cuidado de la piel.',
            'afecto_igv'    => true,
            'activo'        => true,
            'created_at'    => $now,
            'updated_at'    => $now,
        ]);

        $pp4a = DB::table('producto_presentacion')->insertGetId([
            'producto_id'       => $p4,
            'unidad_medida_id'  => 1, // und
            'cantidad'          => 1,
            'tipo_presentacion' => 'Barra 110g',
            'imagen'            => null,
            'es_pesable'        => false,
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);
        DB::table('producto_presentacion_barras')->insert([
            'producto_presentacion_id' => $pp4a,
            'codigo_barra'             => $this->ean13('775', 401),
            'created_at'               => $now,
            'updated_at'               => $now,
        ]);

        $pp4b = DB::table('producto_presentacion')->insertGetId([
            'producto_id'       => $p4,
            'unidad_medida_id'  => 1, // und
            'cantidad'          => 3,
            'tipo_presentacion' => 'Pack 3 barras',
            'imagen'            => null,
            'es_pesable'        => false,
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);
        DB::table('producto_presentacion_barras')->insert([
            'producto_presentacion_id' => $pp4b,
            'codigo_barra'             => $this->ean13('775', 402),
            'created_at'               => $now,
            'updated_at'               => $now,
        ]);

        // ─── Producto 5: Papitas Lays Clásicas ───
        $p5 = DB::table('productos')->insertGetId([
            'empresa_id'    => 1,
            'categoria_id'  => 9, // Snacks y Golosinas
            'marca_id'      => 25, // Lays
            'codigo_interno'=> 'PROD-005',
            'nombre'        => 'Papitas Lays Clásicas',
            'slug'          => Str::slug('Papitas Lays Clásicas'),
            'descripcion'   => 'Hojuelas de papa con sal. Crocantes y sabrosas, el snack clásico para toda ocasión.',
            'afecto_igv'    => true,
            'activo'        => true,
            'created_at'    => $now,
            'updated_at'    => $now,
        ]);

        $pp5a = DB::table('producto_presentacion')->insertGetId([
            'producto_id'       => $p5,
            'unidad_medida_id'  => 1, // und
            'cantidad'          => 1,
            'tipo_presentacion' => 'Bolsa 45g',
            'imagen'            => null,
            'es_pesable'        => false,
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);
        DB::table('producto_presentacion_barras')->insert([
            'producto_presentacion_id' => $pp5a,
            'codigo_barra'             => $this->ean13('775', 501),
            'created_at'               => $now,
            'updated_at'               => $now,
        ]);

        $pp5b = DB::table('producto_presentacion')->insertGetId([
            'producto_id'       => $p5,
            'unidad_medida_id'  => 1, // und
            'cantidad'          => 1,
            'tipo_presentacion' => 'Bolsa 150g',
            'imagen'            => null,
            'es_pesable'        => false,
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);
        DB::table('producto_presentacion_barras')->insert([
            'producto_presentacion_id' => $pp5b,
            'codigo_barra'             => $this->ean13('775', 502),
            'created_at'               => $now,
            'updated_at'               => $now,
        ]);
    }
}
