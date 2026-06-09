<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Empresa;
use App\Models\Sucursal;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EmpresaSucursalSeeder extends Seeder
{
    public function run(): void
    {
        // ==========================================
        // 1. EMPRESAS
        // ==========================================
        $empresa = Empresa::create([
            'ruc' => '20000000001',
            'razon_social' => 'MINIMARKET G0 FOOD MARKET',
            'direccion_fiscal' => 'JR. 28 DE JULIO CON JR ICA',
            'incluido_tributo' => true,
            'entorno' => false,
        ]);

        // ==========================================
        // 2. SUCURSALES
        // ==========================================
        $sucursal = Sucursal::create([
            'empresa_id' => $empresa->id,
            'codigo' => '0000',
            'ubigeo' => '170101',
            'direccion' => 'JR. 28 DE JULIO CON JR ICA',
            'telefono' => '987654322',
            'email' => 'sucursal2@elahorro.com',
            'nombre_sucursal' => 'principal',
            'activo' => true,
        ]);

        // ==========================================
        // 3. USUARIOS
        // ==========================================
        $user1 = User::create([
            'empresa_id' => $empresa->id,
            'name' => 'Admin',
            'email' => 'admin@1.com',
            'password' => Hash::make('12345678'),
            'telefono' => '999111001',
            'activo' => true,
        ]);

        // ==========================================
        // 4. SUCURSAL_USER (asignar usuarios a sucursales)
        // ==========================================
        $user1->sucursales()->attach($sucursal->id);
    }
}
