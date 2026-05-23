<?php

namespace App\Filament\Clusters\Configuraciones\Resources\Empresas\Pages;

use App\Filament\Clusters\Configuraciones\Resources\Empresas\EmpresaResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewEmpresa extends ViewRecord
{
    protected static string $resource = EmpresaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
