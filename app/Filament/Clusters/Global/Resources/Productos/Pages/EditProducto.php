<?php

namespace App\Filament\Clusters\Global\Resources\Productos\Pages;

use App\Filament\Clusters\Global\Resources\Productos\ProductoResource;
use Filament\Resources\Pages\EditRecord;

class EditProducto extends EditRecord
{
    protected static string $resource = ProductoResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
