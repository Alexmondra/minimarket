<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmpresaSucursalSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener IDs de ubigeos para Lima (códigos usados por sucursales)
        $ubigeoLima    = DB::table('ubigeos')->where('codigo', '150101')->first();
        $ubigeoAncon   = DB::table('ubigeos')->where('codigo', '150102')->first();
        $ubigeoAte     = DB::table('ubigeos')->where('codigo', '150103')->first();
        $ubigeoBarranco = DB::table('ubigeos')->where('codigo', '150104')->first();

        // ==========================================
        // 1. EMPRESAS
        // ==========================================
        DB::table('empresas')->insert([
            [
                'ruc'              => '20123456789',
                'razon_social'     => 'MINIMARKET EL AHORRO S.A.C.',
                'direccion_fiscal' => 'Av. Principal 123, Lima',
                'incluido_tributo' => true,
                'entorno'          => true,
            ],
            [
                'ruc'              => '20987654321',
                'razon_social'     => 'MINIMARKET DON PEPE E.I.R.L.',
                'direccion_fiscal' => 'Jr. Las Flores 456, Lima',
                'incluido_tributo' => true,
                'entorno'          => false,
            ],
        ]);

        // ==========================================
        // 2. SUCURSALES (2 por cada empresa)
        // ==========================================
        $sucursales = [
            // Empresa 1: Minimarket El Ahorro
            [
                'empresa_id'        => 1,
                'codigo'            => 'AHO-001',
                'ubigeo'            => $ubigeoLima->id,
                'direccion'         => 'Av. Principal 123, Lima',
                'telefono'          => '987654321',
                'email'             => 'sucursal1@elahorro.com',
                'nombre_sucursal'   => 'Sucursal Centro - El Ahorro',
                'impuesto_porcentaje' => 18.00,
                'activo'            => true,
            ],
            [
                'empresa_id'        => 1,
                'codigo'            => 'AHO-002',
                'ubigeo'            => $ubigeoAte->id,
                'direccion'         => 'Av. Separadora Industrial 789, Ate',
                'telefono'          => '987654322',
                'email'             => 'sucursal2@elahorro.com',
                'nombre_sucursal'   => 'Sucursal Ate - El Ahorro',
                'impuesto_porcentaje' => 18.00,
                'activo'            => true,
            ],
            // Empresa 2: Minimarket Don Pepe
            [
                'empresa_id'        => 2,
                'codigo'            => 'PEP-001',
                'ubigeo'            => $ubigeoAncon->id,
                'direccion'         => 'Jr. Los Olivos 321, Ancón',
                'telefono'          => '987654323',
                'email'             => 'sucursal1@donpepe.com',
                'nombre_sucursal'   => 'Sucursal Ancón - Don Pepe',
                'impuesto_porcentaje' => 18.00,
                'activo'            => true,
            ],
            [
                'empresa_id'        => 2,
                'codigo'            => 'PEP-002',
                'ubigeo'            => $ubigeoBarranco->id,
                'direccion'         => 'Av. San Martín 654, Barranco',
                'telefono'          => '987654324',
                'email'             => 'sucursal2@donpepe.com',
                'nombre_sucursal'   => 'Sucursal Barranco - Don Pepe',
                'impuesto_porcentaje' => 18.00,
                'activo'            => true,
            ],
        ];

        DB::table('sucursales')->insert($sucursales);

        // ==========================================
        // 3. USUARIOS (1 por cada empresa)
        // ==========================================
        $user1 = User::create([
            'empresa_id' => 1,
            'name'       => 'Admin El Ahorro',
            'email'      => 'admin@1.com',
            'password'   => Hash::make('12345678'),
            'telefono'   => '999111001',
            'activo'     => true,
        ]);

        $user2 = User::create([
            'empresa_id' => 2,
            'name'       => 'Admin Don Pepe',
            'email'      => 'admin@2.com',
            'password'   => Hash::make('12345678'),
            'telefono'   => '999222002',
            'activo'     => true,
        ]);

        // ==========================================
        // 4. SUCURSAL_USER (asignar usuarios a sucursales)
        // ==========================================
        DB::table('sucursal_user')->insert([
            ['sucursal_id' => 1, 'user_id' => $user1->id],
            ['sucursal_id' => 2, 'user_id' => $user1->id],
            ['sucursal_id' => 3, 'user_id' => $user2->id],
            ['sucursal_id' => 4, 'user_id' => $user2->id],
        ]);
    }
}
