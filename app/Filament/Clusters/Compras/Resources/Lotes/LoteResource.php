<?php

namespace App\Filament\Clusters\Compras\Resources\Lotes;

use App\Filament\Clusters\Compras\Resources\Lotes\Pages\ListLotes;
use App\Models\Lote;
use App\Support\SucursalContext;
use BackedEnum;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class LoteResource extends Resource
{
    protected static ?string $model = Lote::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    protected static string|UnitEnum|null $navigationGroup = 'Compras';

    protected static ?string $navigationLabel = 'Lotes';

    public static function getPages(): array
    {
        return [
            'index' => ListLotes::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return app(SucursalContext::class)->applyToQuery(parent::getEloquentQuery());
    }
}
