<?php

namespace App\Filament\Clusters\Sunat\Resources\EnviosSunat;

use App\Filament\Clusters\Sunat\Resources\EnviosSunat\Pages\ListEnviosSunat;
use App\Models\Sunat;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;

class EnvioSunatResource extends Resource
{

    protected static ?string $model = Sunat::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-globe-alt';

    protected static string|UnitEnum|null $navigationGroup = 'Sunat';

    protected static ?string $navigationLabel = 'Envíos SUNAT';

    public static function getPages(): array
    {
        return [
            'index' => ListEnviosSunat::route('/'),
        ];
    }
}
