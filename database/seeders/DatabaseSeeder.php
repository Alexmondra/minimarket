<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UbigeosTableSeeder::class,
            EmpresaSucursalSeeder::class,
            RolesPermisosSeeder::class,
            UnidadesMedidaSeeder::class,
            ProveedorSeeder::class,
        ]);

        $this->command->info('✅ Seed completado: ubigeos, empresas, sucursales, usuarios, roles, permisos y proveedores.');
    }
}
