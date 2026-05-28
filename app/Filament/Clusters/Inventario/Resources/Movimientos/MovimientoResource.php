<?php

namespace App\Filament\Clusters\Inventario\Resources\Movimientos;

use App\Filament\Clusters\Inventario\Resources\Movimientos\Pages\ListMovimientos;
use App\Models\MovimientoInventario;
use App\Support\SucursalContext;
use BackedEnum;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class MovimientoResource extends Resource
{
    protected static ?string $model = MovimientoInventario::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static string|UnitEnum|null $navigationGroup = 'Inventario';

    protected static ?string $navigationLabel = 'Movimientos';

    public static function getPages(): array
    {
        return [
            'index' => ListMovimientos::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return app(SucursalContext::class)->applyToQuery(parent::getEloquentQuery());
    }
}
