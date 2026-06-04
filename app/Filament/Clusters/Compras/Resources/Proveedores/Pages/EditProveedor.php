<?php

namespace App\Filament\Clusters\Compras\Resources\Proveedores\Pages;

use App\Filament\Clusters\Compras\Resources\Proveedores\ProveedorResource;
use Filament\Resources\Pages\EditRecord;

class EditProveedor extends EditRecord
{
    protected static string $resource = ProveedorResource::class;

    protected string $view = 'filament.clusters.compras.resources.proveedores.pages.edit-proveedor';
}
