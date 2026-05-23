<?php

namespace App\Filament\Clusters\Almacen\Resources\Presentaciones\Pages;

use App\Filament\Clusters\Almacen\Resources\Presentaciones\PresentacionResource;
use Filament\Resources\Pages\ListRecords;

class ListPresentaciones extends ListRecords
{
    protected static string $resource = PresentacionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
