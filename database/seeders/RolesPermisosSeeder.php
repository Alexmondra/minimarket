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

        // === LISTA LIMPIA DE PERMISOS PARA MINIMARKETING ===
        $permisos = [
            // 1. VENTAS
            'ventas.ver',
            'ventas.crear',
            'ventas.anular',

            // 2. CAJAS
            'cajas.ver',
            'cajas.crear',
            'cajas.cerrar',

            // 4. FACTURACIÓN SUNAT
            'sunat.monitor',
            'sunat.archivos',

            // 5. REPORTES
            'reportes.ver',
            'reportes.crear',
            'reportes.ventas',
            'reportes.inventario',
            
            // 5.5. Ajustes
            'stock.ajustar', //esto es mas para lo lo q es en la opcion de control de existencias poder ajustar entra o salida
            'movimientos.ver', // es para el kardex osea y la opcion esta como movimiento

            // 6. CLIENTES
            'clientes.ver',
            'clientes.crear',
            'clientes.editar',
            'clientes.eliminar',

            // 7. PRODUCTOS (INVENTARIO)
            'productos.ver',
            'productos.crear',
            'productos.editar',
            'productos.eliminar',
            'productos.global', // esto es para todo el modulo de catalogo global

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

            // 12. USUARIOS   // todo este modulo es para los user osea para q cree edite y eso
            'usuarios.ver', 
            'usuarios.crear',
            'usuarios.editar',
            'usuarios.eliminar',

            // 13. ROLES   // esto es para q puede crearlos roles q quiere 
            'roles.ver',
            'roles.crear',
            'roles.editar',
            'roles.eliminar',

            // 14. PERMISOS  // aqui es para q pueda asiganar permisos a los roles , ya q al fin dledia lo q se asigna a los usuarios son roles
            'permisos.ver',
            'permisos.asignar',

            // 15. SUCURSALES
            'sucursales.ver',
            'sucursales.crear',
            'sucursales.editar',
            'sucursales.eliminar',

            // 16. CONFIGURACIÓN GENERAL   // esto es para q pueda editar la empresa en si
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
            'clientes.ver',
            'clientes.crear',
            'productos.ver',
        ]);
        // Asignar Admin al primer usuario (TÚ)
        $user = User::first();
        if ($user) {
            $user->assignRole($admin);
        }
    }
}
