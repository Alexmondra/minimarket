<?php

namespace App\Filament\Clusters\Configuraciones\Resources\Sucursales\Pages;

use App\Filament\Clusters\Configuraciones\Resources\Sucursales\SucursalResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSucursal extends CreateRecord
{
    protected static string $resource = SucursalResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['empresa_id'] = auth()->user()->empresa_id;

        return $data;
    }
}
