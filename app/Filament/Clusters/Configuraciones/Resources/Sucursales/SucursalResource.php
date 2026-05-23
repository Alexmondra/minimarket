<?php

namespace App\Filament\Clusters\Configuraciones\Resources\Sucursales;

use App\Filament\Clusters\Configuraciones\Resources\Sucursales\Pages\CreateSucursal;
use App\Filament\Clusters\Configuraciones\Resources\Sucursales\Pages\EditSucursal;
use App\Filament\Clusters\Configuraciones\Resources\Sucursales\Pages\ListSucursales;
use App\Filament\Clusters\Configuraciones\Resources\Sucursales\Pages\ViewSucursal;
use App\Filament\Clusters\Configuraciones\Resources\Sucursales\Schemas\SucursalForm;
use App\Filament\Clusters\Configuraciones\Resources\Sucursales\Schemas\SucursalInfolist;
use App\Filament\Clusters\Configuraciones\Resources\Sucursales\Tables\SucursalesTable;
use App\Models\Sucursal;
use App\Support\SucursalContext;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SucursalResource extends Resource
{

    protected static ?string $model = Sucursal::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-storefront';

    protected static string|UnitEnum|null $navigationGroup = 'Configuraciones';

    protected static ?string $recordTitleAttribute = 'nombre_sucursal';

    protected static ?string $modelLabel = 'Sucursal';

    protected static ?string $pluralModelLabel = 'Sucursales';

    protected static ?string $slug = 'sucursales';

    protected static ?string $navigationLabel = 'Sucursales';

    public static function form(Schema $schema): Schema
    {
        return SucursalForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SucursalInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SucursalesTable::configure($table);
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
            'index' => ListSucursales::route('/'),
            'create' => CreateSucursal::route('/create'),
            'view' => ViewSucursal::route('/{record}'),
            'edit' => EditSucursal::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->where('empresa_id', auth()->user()->empresa_id);

        return app(SucursalContext::class)->applyToQuery($query, 'id');
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return static::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
