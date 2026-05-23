<?php

namespace App\Filament\Clusters\Compras\Resources\Compras;

use App\Filament\Clusters\Compras\Resources\Compras\Pages\ListCompras;
use App\Filament\Clusters\Compras\Resources\Compras\Pages\ViewCompra;
use App\Models\Compra;
use App\Support\SucursalContext;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class CompraResource extends Resource
{

    protected static ?string $model = Compra::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-archive-box';

    protected static string|UnitEnum|null $navigationGroup = 'Compras';

    protected static ?string $navigationLabel = 'Compras';

    public static function getPages(): array
    {
        return [
            'index' => ListCompras::route('/'),
            'registrar' => \App\Filament\Clusters\Compras\Resources\Compras\Pages\RegistrarCompra::route('/registrar'),
            'view' => ViewCompra::route('/{record}'),
        ];
    }

    public static function canViewAny(): bool
    {
        return Auth::user()?->can('compras.ver') ?? false;
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->can('compras.crear') ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        return app(SucursalContext::class)->applyToQuery(parent::getEloquentQuery());
    }
}
