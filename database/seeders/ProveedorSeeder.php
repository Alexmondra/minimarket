<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProveedorSeeder extends Seeder
{
    public function run(): void
    {
        $proveedores = [
            // ========== Empresa 1 - Sucursal 1 (Centro - El Ahorro) ==========
            [
                'empresa_id' => 1,
                'sucursal_id' => 1,
                'nombre' => 'Distribuidora Los Andes',
                'tipo_documento' => 'RUC',
                'numero_documento' => '20123456781',
                'razon_social' => 'DISTRIBUIDORA LOS ANDES S.A.C.',
                'direccion' => 'Av. Grau 456, Lima',
                'telefono' => '999111001',
                'email' => 'ventas@losandes.com',
                'contacto_principal' => 'Carlos Mendoza',
                'telefono_contacto' => '999111002',
                'rubro' => 'Abarrotes y Víveres',
                'observaciones' => 'Proveedor principal de abarrotes.',
                'estado' => true,
            ],
            [
                'empresa_id' => 1,
                'sucursal_id' => 1,
                'nombre' => 'Lácteos del Sur',
                'tipo_documento' => 'RUC',
                'numero_documento' => '20123456782',
                'razon_social' => 'LÁCTEOS DEL SUR E.I.R.L.',
                'direccion' => 'Jr. Los Olivos 789, Lima',
                'telefono' => '999222001',
                'email' => 'pedidos@lacteosdelsur.com',
                'contacto_principal' => 'María Torres',
                'telefono_contacto' => '999222002',
                'rubro' => 'Lácteos y Derivados',
                'observaciones' => null,
                'estado' => true,
            ], 
        ];

        DB::table('proveedores')->insert($proveedores);
    }
}
