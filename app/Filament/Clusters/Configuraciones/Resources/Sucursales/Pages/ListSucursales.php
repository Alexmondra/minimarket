<?php

namespace App\Filament\Clusters\Configuraciones\Resources\Sucursales\Pages;

use App\Filament\Clusters\Configuraciones\Resources\Sucursales\SucursalResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSucursales extends ListRecords
{
    protected static string $resource = SucursalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
