<?php

namespace App\Filament\Clusters\Configuraciones\Resources\Usuarios;

use App\Filament\Clusters\Configuraciones\Resources\Usuarios\Pages\CreateUsuario;
use App\Filament\Clusters\Configuraciones\Resources\Usuarios\Pages\EditUsuario;
use App\Filament\Clusters\Configuraciones\Resources\Usuarios\Pages\ListUsuarios;
use App\Filament\Clusters\Configuraciones\Resources\Usuarios\Pages\ViewUsuario;
use App\Filament\Clusters\Configuraciones\Resources\Usuarios\Schemas\UsuarioForm;
use App\Filament\Clusters\Configuraciones\Resources\Usuarios\Schemas\UsuarioInfolist;
use App\Filament\Clusters\Configuraciones\Resources\Usuarios\Tables\UsuariosTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;
use Illuminate\Database\Eloquent\Model;

class UsuarioResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static string|UnitEnum|null $navigationGroup = 'Configuraciones';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Usuario';

    protected static ?string $pluralModelLabel = 'Usuarios';

    protected static ?string $slug = 'usuarios';

    protected static ?string $navigationLabel = 'Usuarios';

    public static function form(Schema $schema): Schema
    {
        return UsuarioForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return UsuarioInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsuariosTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsuarios::route('/'),
            'create' => CreateUsuario::route('/create'),
            'view' => ViewUsuario::route('/{record}'),
            'edit' => EditUsuario::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('empresa_id', auth()->user()->empresa_id);
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('usuarios.ver') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('usuarios.crear') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->can('usuarios.editar') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->can('usuarios.eliminar') ?? false;
    }
}
