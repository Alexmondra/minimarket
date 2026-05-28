<?php

namespace App\Filament\Clusters\Configuraciones\Resources\Permisos\Pages;

use App\Filament\Clusters\Configuraciones\Resources\Permisos\PermisoResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ManagePermisos extends Page
{
    protected static string $resource = PermisoResource::class;

    protected string $view = 'filament.clusters.configuraciones.resources.roles.pages.manage-permisos';

    public ?int $roleId = null;

    public $roles;

    public ?string $selectedRoleId = null;

    public array $groupedPermissions = [];

    public array $rolePermissions = [];

    // Define las acciones principales que van en la cabecera
    public array $mainActions = ['ver', 'crear', 'editar', 'eliminar'];

    // Mapeo de módulos para nombres legibles
    private array $moduleNames = [
        'ventas' => 'Ventas',
        'cajas' => 'Cajas',
        'guias' => 'Guías de Remisión',
        'sunat' => 'Facturación SUNAT',
        'reportes' => 'Reportes',
        'lotes' => 'Lotes',
        'stock' => 'Stock',
        'clientes' => 'Clientes',
        'productos' => 'Productos',
        'categorias' => 'Categorías',
        'marcas' => 'Marcas',
        'compras' => 'Compras',
        'proveedores' => 'Proveedores',
        'usuarios' => 'Usuarios',
        'roles' => 'Roles',
        'permisos' => 'Permisos',
        'sucursales' => 'Sucursales',
        'config' => 'Configuración',
    ];

    protected $listeners = ['refreshPermissions' => '$refresh'];

    public function mount(): void
    {
        $this->roles = Role::all();

        $queryRoleId = request()->query('roleId');
        if ($queryRoleId) {
            $this->roleId = (int) $queryRoleId;
            $this->selectedRoleId = (string) $queryRoleId;
            $this->loadPermissions();
        }
    }

    public function updatedSelectedRoleId(): void
    {
        $this->loadPermissions();
    }

    public function loadPermissions(): void
    {
        $this->groupedPermissions = [];
        $this->rolePermissions = [];

        if (! $this->selectedRoleId) {
            return;
        }

        $role = Role::find($this->selectedRoleId);
        if (! $role) {
            return;
        }

        $this->rolePermissions = $role->permissions->pluck('name')->toArray();

        // Lista de todos los permisos que existen en la BD
        $allPermissions = Permission::all()->pluck('name')->toArray();

        // Agrupar permisos por módulo
        $tempGrouped = [];
        foreach ($allPermissions as $perm) {
            [$module, $action] = explode('.', $perm);

            if (! isset($tempGrouped[$module])) {
                $tempGrouped[$module] = [];
            }
            $tempGrouped[$module][] = $action;
        }

        // Construir la estructura final ordenada
        foreach ($tempGrouped as $module => $actions) {
            $this->groupedPermissions[$module] = [
                'main' => [],      // acciones principales
                'special' => [],    // permisos especiales
            ];

            // Inicializar acciones principales (solo si existen en este módulo)
            foreach ($this->mainActions as $action) {
                if (in_array($action, $actions)) {
                    $permName = $module.'.'.$action;
                    $this->groupedPermissions[$module]['main'][$action] = in_array($permName, $this->rolePermissions);
                }
            }

            // Agregar permisos especiales (los que no son main actions)
            foreach ($actions as $action) {
                if (! in_array($action, $this->mainActions)) {
                    $permName = $module.'.'.$action;
                    $this->groupedPermissions[$module]['special'][$action] = in_array($permName, $this->rolePermissions);
                }
            }
        }
    }

    public function togglePermission(string $permName): void
    {
        if (! $this->selectedRoleId) {
            return;
        }

        $role = Role::find($this->selectedRoleId);
        if (! $role) {
            return;
        }

        if ($role->hasPermissionTo($permName)) {
            $role->revokePermissionTo($permName);
        } else {
            $role->givePermissionTo($permName);
        }

        $this->loadPermissions(); // refrescar la tabla
    }

    /**
     * Obtiene el nombre legible del módulo
     */
    public function getModuleName(string $module): string
    {
        return $this->moduleNames[$module] ?? ucfirst($module);
    }

    public function save(): void
    {
        Notification::make()
            ->title('Permisos actualizados correctamente.')
            ->success()
            ->send();
    }
}
