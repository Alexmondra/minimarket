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
        // Códigos de ubigeos para Lima (usados por sucursales)
        $ubigeoLima = '150101';
        $ubigeoAncon = '150102';
        $ubigeoAte = '150103';
        $ubigeoBarranco = '150104';

        // ==========================================
        // 1. EMPRESAS
        // ==========================================
        DB::table('empresas')->insert([
            [
                'ruc' => '20100066603',
                'razon_social' => 'MINIMARKET go go',
                'direccion_fiscal' => 'Av. Principal 123, Lima',
                'incluido_tributo' => true,
                'entorno' => true,
            ],
            [
                'ruc' => '20987654321',
                'razon_social' => 'MINIMARKET DON PEPE E.I.R.L.',
                'direccion_fiscal' => 'Jr. Las Flores 456, Lima',
                'incluido_tributo' => true,
                'entorno' => false,
            ],
        ]);

        // ==========================================
        // 2. SUCURSALES (2 por cada empresa)
        // ==========================================
        $sucursales = [
            // Empresa 1: Minimarket El Ahorro
            [
                'empresa_id' => 1,
                'codigo' => '0000',
                'ubigeo' => $ubigeoLima,
                'direccion' => 'Av. Principal 123, Lima',
                'telefono' => '987654321',
                'email' => 'sucursal1@elahorro.com',
                'nombre_sucursal' => 'Sucursal Centro - El Ahorro',
                'impuesto_porcentaje' => 18.00,
                'activo' => true,
            ],
            [
                'empresa_id' => 1,
                'codigo' => '0001',
                'ubigeo' => $ubigeoAte,
                'direccion' => 'Av. Separadora Industrial 789, Ate',
                'telefono' => '987654322',
                'email' => 'sucursal2@elahorro.com',
                'nombre_sucursal' => 'Sucursal Ate - El Ahorro',
                'impuesto_porcentaje' => 18.00,
                'activo' => true,
            ],
            // Empresa 2: Minimarket Don Pepe
            [
                'empresa_id' => 2,
                'codigo' => '0000',
                'ubigeo' => $ubigeoAncon,
                'direccion' => 'Jr. Los Olivos 321, Ancón',
                'telefono' => '987654323',
                'email' => 'sucursal1@donpepe.com',
                'nombre_sucursal' => 'Sucursal Ancón - Don Pepe',
                'impuesto_porcentaje' => 18.00,
                'activo' => true,
            ],
            [
                'empresa_id' => 2,
                'codigo' => '0001',
                'ubigeo' => $ubigeoBarranco,
                'direccion' => 'Av. San Martín 654, Barranco',
                'telefono' => '987654324',
                'email' => 'sucursal2@donpepe.com',
                'nombre_sucursal' => 'Sucursal Barranco - Don Pepe',
                'impuesto_porcentaje' => 18.00,
                'activo' => true,
            ],
        ];

        DB::table('sucursales')->insert($sucursales);

        // ==========================================
        // 3. USUARIOS (1 por cada empresa)
        // ==========================================
        $user1 = User::create([
            'empresa_id' => 1,
            'name' => 'Admin El Ahorro',
            'email' => 'admin@1.com',
            'password' => Hash::make('12345678'),
            'telefono' => '999111001',
            'activo' => true,
        ]);

        $user2 = User::create([
            'empresa_id' => 2,
            'name' => 'Admin Don Pepe',
            'email' => 'admin@2.com',
            'password' => Hash::make('12345678'),
            'telefono' => '999222002',
            'activo' => true,
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
