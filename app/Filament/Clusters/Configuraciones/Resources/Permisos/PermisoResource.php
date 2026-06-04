<?php

namespace App\Filament\Clusters\Configuraciones\Resources\Permisos;

use App\Filament\Clusters\Configuraciones\Resources\Permisos\Pages\CreateRol;
use App\Filament\Clusters\Configuraciones\Resources\Permisos\Pages\EditRol;
use App\Filament\Clusters\Configuraciones\Resources\Permisos\Pages\ListPermisos;
use App\Filament\Clusters\Configuraciones\Resources\Permisos\Pages\ManagePermisos;
use BackedEnum;
use Filament\Resources\Resource;
use Spatie\Permission\Models\Permission;
use UnitEnum;
use Illuminate\Database\Eloquent\Model;

class PermisoResource extends Resource
{
    protected static ?string $model = Permission::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    protected static string|UnitEnum|null $navigationGroup = 'Configuraciones';

    protected static ?string $navigationLabel = 'Permisos';

    protected static ?string $modelLabel = 'Permiso';

    protected static ?string $pluralModelLabel = 'Permisos';

    public static function getPages(): array
    {
        return [
            'index' => ListPermisos::route('/roles'),
            'create-rol' => CreateRol::route('/roles/create'),
            'edit-rol' => EditRol::route('/roles/{record}/edit'),
            'manage-permisos' => ManagePermisos::route('/roles/manage-permisos'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('permisos.ver') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('roles.crear') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->can('roles.editar') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->can('roles.eliminar') ?? false;
    }
}
