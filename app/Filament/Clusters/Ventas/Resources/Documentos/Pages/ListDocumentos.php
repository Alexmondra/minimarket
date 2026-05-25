<?php

namespace App\Filament\Clusters\Ventas\Resources\Documentos\Pages;

use App\Filament\Clusters\Ventas\Resources\Documentos\DocumentoResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListDocumentos extends ListRecords
{
    protected static string $resource = DocumentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('registrarVenta')
                ->label('Registrar venta')
                ->icon('heroicon-o-bolt')
                ->url(DocumentoResource::getUrl('registrar'))
                ->visible(auth()->user()?->can('ventas.crear') ?? false),
        ];
    }
}
