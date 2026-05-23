<?php

namespace App\Filament\Clusters\Configuraciones\Resources\Ubigeos\Pages;

use App\Filament\Clusters\Configuraciones\Resources\Ubigeos\UbigeoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUbigeos extends ListRecords
{
    protected static string $resource = UbigeoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
