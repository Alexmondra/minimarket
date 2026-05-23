<?php

namespace App\Filament\Clusters\Configuraciones\Resources\Ubigeos\Pages;

use App\Filament\Clusters\Configuraciones\Resources\Ubigeos\UbigeoResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewUbigeo extends ViewRecord
{
    protected static string $resource = UbigeoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
