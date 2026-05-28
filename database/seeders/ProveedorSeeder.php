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

            // ========== Empresa 1 - Sucursal 2 (Ate - El Ahorro) ==========
            [
                'empresa_id' => 1,
                'sucursal_id' => 2,
                'nombre' => 'Bebidas del Perú',
                'tipo_documento' => 'RUC',
                'numero_documento' => '20123456783',
                'razon_social' => 'BEBIDAS DEL PERÚ S.A.',
                'direccion' => 'Av. Industrial 123, Ate',
                'telefono' => '999333001',
                'email' => 'ventas@bebidasdelperu.com',
                'contacto_principal' => 'José García',
                'telefono_contacto' => '999333002',
                'rubro' => 'Bebidas y Gaseosas',
                'observaciones' => null,
                'estado' => true,
            ],
            [
                'empresa_id' => 1,
                'sucursal_id' => 2,
                'nombre' => 'Distribuidora San Martín',
                'tipo_documento' => 'RUC',
                'numero_documento' => '20123456784',
                'razon_social' => 'DISTRIBUIDORA SAN MARTÍN S.A.C.',
                'direccion' => 'Jr. Las Magnolias 456, Ate',
                'telefono' => '999444001',
                'email' => 'info@sanmartin.com',
                'contacto_principal' => 'Lucía Fernández',
                'telefono_contacto' => '999444002',
                'rubro' => 'Limpieza y Hogar',
                'observaciones' => 'Proveedor de artículos de limpieza.',
                'estado' => true,
            ],

            // ========== Empresa 2 - Sucursal 3 (Ancón - Don Pepe) ==========
            [
                'empresa_id' => 2,
                'sucursal_id' => 3,
                'nombre' => 'Carnes y Embutidos Norte',
                'tipo_documento' => 'RUC',
                'numero_documento' => '20987654322',
                'razon_social' => 'CARNES Y EMBUTIDOS NORTE S.A.C.',
                'direccion' => 'Av. Panamericana Norte Km 35, Ancón',
                'telefono' => '999555001',
                'email' => 'ventas@carnesnorte.com',
                'contacto_principal' => 'Pedro Ramírez',
                'telefono_contacto' => '999555002',
                'rubro' => 'Cárnicos y Embutidos',
                'observaciones' => null,
                'estado' => true,
            ],
            [
                'empresa_id' => 2,
                'sucursal_id' => 3,
                'nombre' => 'Panificadora El Trigal',
                'tipo_documento' => 'RUC',
                'numero_documento' => '20987654323',
                'razon_social' => 'PANIFICADORA EL TRIGAL E.I.R.L.',
                'direccion' => 'Jr. El Trigo 222, Ancón',
                'telefono' => '999666001',
                'email' => 'pedidos@eltrigal.com',
                'contacto_principal' => 'Rosa Sánchez',
                'telefono_contacto' => '999666002',
                'rubro' => 'Panadería y Pastelería',
                'observaciones' => 'Entrega todas las mañanas.',
                'estado' => true,
            ],

            // ========== Empresa 2 - Sucursal 4 (Barranco - Don Pepe) ==========
            [
                'empresa_id' => 2,
                'sucursal_id' => 4,
                'nombre' => 'Frutas y Verduras Frescas',
                'tipo_documento' => 'DNI',
                'numero_documento' => '12345678',
                'razon_social' => null,
                'direccion' => 'Av. Grau 111, Barranco',
                'telefono' => '999777001',
                'email' => null,
                'contacto_principal' => 'Luis Huamán',
                'telefono_contacto' => '999777002',
                'rubro' => 'Frutas y Verduras',
                'observaciones' => 'Proveedor minorista.',
                'estado' => true,
            ],
            [
                'empresa_id' => 2,
                'sucursal_id' => 4,
                'nombre' => 'Abarrotes El Proveedor',
                'tipo_documento' => 'RUC',
                'numero_documento' => '20987654324',
                'razon_social' => 'ABARROTES EL PROVEEDOR S.A.C.',
                'direccion' => 'Jr. San Martín 333, Barranco',
                'telefono' => '999888001',
                'email' => 'ventas@elproveedor.com',
                'contacto_principal' => 'Ana Castillo',
                'telefono_contacto' => '999888002',
                'rubro' => 'Abarrotes',
                'observaciones' => null,
                'estado' => true,
            ],
        ];

        DB::table('proveedores')->insert($proveedores);
    }
}
