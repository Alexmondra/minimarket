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
        // ==========================================
        // 1. EMPRESAS
        // ==========================================
        DB::table('empresas')->insert([
            [
                'ruc' => '20000000001',
                'razon_social' => 'MINIMARKET G0 FOOD MARKET',
                'direccion_fiscal' => 'JR. 28 DE JULIO CON JR ICA',
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
                'ubigeo' => '170101',
                'direccion' => 'JR. 28 DE JULIO CON JR ICA',
                'telefono' => '987654322',
                'email' => 'sucursal2@elahorro.com',
                'nombre_sucursal' => 'principal',
                'activo' => true,
            ],
            
        ];

        DB::table('sucursales')->insert($sucursales);

        // ==========================================
        // 3. USUARIOS (1 por cada empresa)
        // ==========================================
        $user1 = User::create([
            'empresa_id' => 1,
            'name' => 'Admin',
            'email' => 'admin@1.com',
            'password' => Hash::make('12345678'),
            'telefono' => '999111001',
            'activo' => true,
        ]);

       
        // ==========================================
        // 4. SUCURSAL_USER (asignar usuarios a sucursales)
        // ==========================================
        DB::table('sucursal_user')->insert([
            ['sucursal_id' => 1, 'user_id' => $user1->id],
        ]);
    }
}
