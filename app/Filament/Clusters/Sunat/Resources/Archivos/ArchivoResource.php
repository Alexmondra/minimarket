<?php

namespace App\Filament\Clusters\Sunat\Resources\Archivos;

use App\Filament\Clusters\Sunat\Resources\Archivos\Pages\ListArchivos;
use App\Models\Archivo;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;

class ArchivoResource extends Resource
{

    protected static ?string $model = Archivo::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-folder';

    protected static string|UnitEnum|null $navigationGroup = 'Sunat';

    protected static ?string $navigationLabel = 'Archivos';

    public static function getPages(): array
    {
        return [
            'index' => ListArchivos::route('/'),
        ];
    }
}
