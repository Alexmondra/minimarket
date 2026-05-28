<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesPermisosSeeder extends Seeder
{
    public function run(): void
    {
        // Limpia cache de permisos
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // === LISTA LIMPIA DE PERMISOS PARA FARMACIA ===
        $permisos = [
            // 1. VENTAS
            'ventas.ver',
            'ventas.crear',
            'ventas.anular',

            // 2. CAJAS
            'cajas.ver',
            'cajas.abrir',
            'cajas.cerrar',

            // 3. GUÍAS DE REMISIÓN
            'guias.ver',
            'guias.crear',

            // 4. FACTURACIÓN SUNAT
            'sunat.monitor',
            'sunat.archivos',

            // 5. REPORTES
            'reportes.ver',
            'reportes.ventas',
            'reportes.inventario',
            // 5.5. Ajustes
            'lotes.ver',
            'stock.crear',
            'movimientos.ver',

            // 6. CLIENTES
            'clientes.ver',
            'clientes.crear',
            'clientes.editar',
            'clientes.eliminar',

            // 7. MEDICAMENTOS (INVENTARIO)
            'productos.ver',
            'productos.crear',
            'productos.editar',
            'productos.eliminar',
            'productos.global',

            // 8. CATEGORÍAS
            'categorias.ver',
            'categorias.crear',
            'categorias.editar',
            'categorias.eliminar',

            // 9 CATEGORÍAS
            'marcas.ver',
            'marcas.crear',
            'marcas.editar',
            'marcas.eliminar',

            // 10. COMPRAS
            'compras.ver',
            'compras.crear',
            'compras.editar',
            'compras.anular',

            // 11. PROVEEDORES
            'proveedores.ver',
            'proveedores.crear',
            'proveedores.editar',
            'proveedores.eliminar',

            // 12. USUARIOS
            'usuarios.ver',
            'usuarios.crear',
            'usuarios.editar',
            'usuarios.eliminar',

            // 13. ROLES
            'roles.ver',
            'roles.crear',
            'roles.editar',
            'roles.eliminar',

            // 14. PERMISOS
            'permisos.ver',
            'permisos.asignar',

            // 15. SUCURSALES
            'sucursales.ver',
            'sucursales.crear',
            'sucursales.editar',
            'sucursales.eliminar',

            // 16. CONFIGURACIÓN GENERAL
            'config.ver',
            'config.editar',
        ];

        foreach ($permisos as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        // === ROLES POR DEFECTO ===

        // 1. Administrador (Tiene TODO)
        $admin = Role::firstOrCreate(['name' => 'Administrador']);
        $admin->syncPermissions(Permission::all());

        // 2. Vendedor (Solo vende y ve clientes)
        $vendedor = Role::firstOrCreate(['name' => 'Vendedor']);
        $vendedor->syncPermissions([
            'ventas.ver',
            'ventas.crear',
            'cajas.abrir',
            'clientes.ver',
            'clientes.crear',
            'productos.ver', // Necesita ver para buscar precios
        ]);
        // Asignar Admin al primer usuario (TÚ)
        $user = User::first();
        if ($user) {
            $user->assignRole($admin);
        }
    }
}
