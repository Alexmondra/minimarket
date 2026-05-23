<?php

namespace App\Filament\Clusters\Ventas\Resources\Clientes;

use App\Filament\Clusters\Ventas\Resources\Clientes\Pages\ListClientes;
use App\Models\Cliente;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;

class ClienteResource extends Resource
{

    protected static ?string $model = Cliente::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static string|UnitEnum|null $navigationGroup = 'Ventas';

    protected static ?string $navigationLabel = 'Clientes';

    public static function getPages(): array
    {
        return [
            'index' => ListClientes::route('/'),
        ];
    }
}
