<?php

namespace App\Filament\Clusters\Configuraciones\Resources\Sucursales\Pages;

use App\Filament\Clusters\Configuraciones\Resources\Sucursales\SucursalResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSucursal extends ViewRecord
{
    protected static string $resource = SucursalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
