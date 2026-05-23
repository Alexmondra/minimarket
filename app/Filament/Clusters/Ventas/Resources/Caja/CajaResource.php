<?php

namespace App\Filament\Clusters\Ventas\Resources\Caja;

use App\Filament\Clusters\Ventas\Resources\Caja\Pages\ListCaja;
use App\Models\SessioneCaja;
use App\Support\SucursalContext;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;

class CajaResource extends Resource
{

    protected static ?string $model = SessioneCaja::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calculator';

    protected static string|UnitEnum|null $navigationGroup = 'Ventas';

    protected static ?string $navigationLabel = 'Caja';

    public static function getPages(): array
    {
        return [
            'index' => ListCaja::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return app(SucursalContext::class)->applyToQuery(parent::getEloquentQuery());
    }
}
