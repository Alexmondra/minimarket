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
            CategoriaSeeder::class,
            MarcaSeeder::class,
            ProductoSeeder::class,
            ProveedorSeeder::class,
        ]);

        $this->command->info('✅ Seed completado: ubigeos, empresas, sucursales, usuarios, roles, permisos, categorías, marcas, productos y proveedores.');
    }
}
