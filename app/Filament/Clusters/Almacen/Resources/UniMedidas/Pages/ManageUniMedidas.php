<?php

namespace App\Filament\Clusters\Almacen\Resources\UniMedidas\Pages;

use App\Filament\Clusters\Almacen\Resources\UniMedidas\UniMedidaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageUniMedidas extends ManageRecords
{
    protected static string $resource = UniMedidaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
