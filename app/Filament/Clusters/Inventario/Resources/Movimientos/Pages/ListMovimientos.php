<?php

namespace App\Filament\Clusters\Inventario\Resources\Movimientos\Pages;

use App\Filament\Clusters\Inventario\Resources\Movimientos\MovimientoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMovimientos extends ListRecords
{
    protected static string $resource = MovimientoResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getHeaderWidgets(): array
    {
        return [];
    }
}
