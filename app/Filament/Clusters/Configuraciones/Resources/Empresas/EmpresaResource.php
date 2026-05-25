<?php

namespace App\Filament\Clusters\Configuraciones\Resources\Empresas;

use App\Filament\Clusters\Configuraciones\Resources\Empresas\Pages\EditEmpresa;
use App\Filament\Clusters\Configuraciones\Resources\Empresas\Pages\ListEmpresas;
use App\Filament\Clusters\Configuraciones\Resources\Empresas\Schemas\EmpresaForm;
use App\Filament\Clusters\Configuraciones\Resources\Empresas\Schemas\EmpresaInfolist;
use App\Filament\Clusters\Configuraciones\Resources\Empresas\Tables\EmpresasTable;
use App\Models\Empresa;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class EmpresaResource extends Resource
{
    protected static ?string $model = Empresa::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-office';

    protected static string|UnitEnum|null $navigationGroup = 'Configuraciones';

    protected static ?string $recordTitleAttribute = 'razon_social';

    protected static ?string $navigationLabel = 'Mi Empresa';

    protected static ?string $pluralLabel = 'Mi Empresa';

    public static function form(Schema $schema): Schema
    {
        return EmpresaForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return EmpresaInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmpresasTable::configure($table);
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
            'index' => ListEmpresas::route('/'),
            'edit' => EditEmpresa::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->where('id', auth()->user()?->empresa_id)
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('id', auth()->user()->empresa_id)
            ->with('empresaConfig');
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('config.ver') ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return ($record->id === auth()->user()?->empresa_id)
            && (auth()->user()?->can('config.editar') ?? false);
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }
}
