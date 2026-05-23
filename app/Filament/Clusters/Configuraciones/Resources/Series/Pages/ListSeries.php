<?php

namespace App\Filament\Clusters\Configuraciones\Resources\Series\Pages;

use App\Filament\Clusters\Configuraciones\Resources\Series\SerieResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSeries extends ListRecords
{
    protected static string $resource = SerieResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('verSucursalesCards')
                ->label('Ver sucursales en cards')
                ->icon('heroicon-o-building-storefront')
                ->color('info')
                ->url(SerieResource::getUrl('seleccionar')),
        ];
    }
}
