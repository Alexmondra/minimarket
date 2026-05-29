<?php

namespace App\Filament\Clusters\Inventario\Resources\Mermas\Pages;

use App\Filament\Clusters\Inventario\Resources\Mermas\MermaResource;
use Filament\Resources\Pages\ListRecords;

class ListMermas extends ListRecords
{
    protected static string $resource = MermaResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
