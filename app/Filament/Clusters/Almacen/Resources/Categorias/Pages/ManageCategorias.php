<?php

namespace App\Filament\Clusters\Almacen\Resources\Categorias\Pages;

use App\Filament\Clusters\Almacen\Resources\Categorias\CategoriaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageCategorias extends ManageRecords
{
    protected static string $resource = CategoriaResource::class;

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
