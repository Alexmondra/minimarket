<?php

namespace App\Filament\Clusters\Ventas\Resources\Documentos\Pages;

use App\Filament\Clusters\Ventas\Resources\Documentos\DocumentoResource;
use App\Models\Documento;
use App\Support\SucursalContext;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Facades\Auth;

class ListDocumentos extends ListRecords
{
    protected static string $resource = DocumentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('registrarVenta')
                ->label('Nueva venta')
                ->icon('heroicon-o-plus-circle')
                ->color('success')
                ->url(DocumentoResource::getUrl('registrar'))
                ->size('lg')
                ->extraAttributes(['class' => 'shadow-lg shadow-emerald-500/20'])
                ->visible(auth()->user()?->can('ventas.crear') ?? false),
        ];
    }

    public function getTitle(): string
    {
        return 'Ventas registradas';
    }

    public function getHeading(): string
    {
        return 'Ventas registradas';
    }

    public function getSubheading(): ?string
    {
        return 'Consulta, filtra y administra todos los comprobantes emitidos.';
    }
}
