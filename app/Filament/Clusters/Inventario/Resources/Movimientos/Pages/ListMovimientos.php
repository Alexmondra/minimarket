<?php

namespace App\Filament\Clusters\Inventario\Resources\Movimientos\Pages;

use App\Filament\Clusters\Inventario\Resources\Movimientos\MovimientoResource;
use Filament\Resources\Pages\ListRecords;

class ListMovimientos extends ListRecords
{
    protected static string $resource = MovimientoResource::class;
}
