<?php

namespace App\Filament\Clusters\Compras\Resources\Proveedores\Pages;

use App\Filament\Clusters\Compras\Resources\Proveedores\ProveedorResource;
use App\Support\SucursalContext;
use Filament\Resources\Pages\CreateRecord;

class CreateProveedor extends CreateRecord
{
    protected static string $resource = ProveedorResource::class;

    protected string $view = 'filament.clusters.compras.resources.proveedores.pages.create-proveedor';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['empresa_id'] = auth()->user()->empresa_id;
        $data['sucursal_id'] = app(SucursalContext::class)->resolveSucursalForWrite($data['sucursal_id'] ?? null);

        return $data;
    }
}
