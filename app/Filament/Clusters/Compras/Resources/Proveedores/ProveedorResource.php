<?php

namespace App\Filament\Clusters\Compras\Resources\Proveedores;

use App\Filament\Clusters\Compras\Resources\Proveedores\Pages\CreateProveedor;
use App\Filament\Clusters\Compras\Resources\Proveedores\Pages\EditProveedor;
use App\Filament\Clusters\Compras\Resources\Proveedores\Pages\ListProveedores;
use App\Filament\Clusters\Compras\Resources\Proveedores\Schemas\ProveedorForm;
use App\Filament\Clusters\Compras\Resources\Proveedores\Tables\ProveedoresTable;
use App\Models\Proveedor;
use App\Support\SucursalContext;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProveedorResource extends Resource
{

    protected static ?string $model = Proveedor::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-truck';

    protected static string|UnitEnum|null $navigationGroup = 'Compras';

    protected static ?string $navigationLabel = 'Proveedores';

    protected static ?string $recordTitleAttribute = 'nombre';

    protected static ?string $modelLabel = 'Proveedor';

    protected static ?string $pluralModelLabel = 'Proveedores';

    protected static ?string $slug = 'proveedores';

    public static function form(Schema $schema): Schema
    {
        return ProveedorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProveedoresTable::configure($table);
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
            'index' => ListProveedores::route('/'),
            'create' => CreateProveedor::route('/create'),
            'edit' => EditProveedor::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->where('empresa_id', auth()->user()->empresa_id);

        return app(SucursalContext::class)->applyNullableToQuery($query);
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return static::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
