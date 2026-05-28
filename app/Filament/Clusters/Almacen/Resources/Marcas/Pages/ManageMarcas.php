<?php

namespace App\Filament\Clusters\Almacen\Resources\Marcas\Pages;

use App\Filament\Clusters\Almacen\Resources\Marcas\MarcaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageMarcas extends ManageRecords
{
    protected static string $resource = MarcaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->mutateFormDataUsing(function (array $data): array {
                    $data['empresa_id'] = auth()->user()->empresa_id;

                    return $data;
                }),
        ];
    }
}
