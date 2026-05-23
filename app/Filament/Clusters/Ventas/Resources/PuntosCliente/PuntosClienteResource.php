<?php

namespace App\Filament\Clusters\Ventas\Resources\PuntosCliente;

use App\Filament\Clusters\Ventas\Resources\PuntosCliente\Pages\ListPuntosCliente;
use App\Models\ClientePunto;
use App\Support\SucursalContext;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;

class PuntosClienteResource extends Resource
{

    protected static ?string $model = ClientePunto::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-star';

    protected static string|UnitEnum|null $navigationGroup = 'Ventas';

    protected static ?string $navigationLabel = 'Puntos Cliente';

    public static function getPages(): array
    {
        return [
            'index' => ListPuntosCliente::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return app(SucursalContext::class)->applyToQuery(parent::getEloquentQuery());
    }
}
