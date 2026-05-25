<?php

namespace App\Filament\Clusters\Ventas\Resources\Documentos\Pages;

use App\Filament\Clusters\Ventas\Resources\Documentos\DocumentoResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewDocumento extends ViewRecord
{
    protected static string $resource = DocumentoResource::class;

    public string $view = 'filament.pages.view-documento';

    public function getTitle(): string
    {
        return sprintf('%s-%s', $this->record->serie, $this->record->numero);
    }

    public function getDocumento()
    {
        return $this->record->load([
            'cliente',
            'empresa',
            'sucursal',
            'user',
            'cajaSesion',
            'detalles.presentacion.unidadMedida',
            'archivos',
            'sunat',
        ]);
    }

    protected function getHeaderActions(): array
    {
        $documento = $this->getDocumento();
        $ticket = $documento->archivos->firstWhere('tipo_archivo', 'ticket_html');
        $xml = $documento->archivos->firstWhere('tipo_archivo', 'xml');

        return [
            Action::make('verTicket')
                ->label('Ver ticket')
                ->icon('heroicon-o-printer')
                ->url($ticket ? route('filament.archivos.view', $ticket) : null, shouldOpenInNewTab: true)
                ->visible((bool) $ticket),
            Action::make('verXml')
                ->label('Ver XML')
                ->icon('heroicon-o-code-bracket')
                ->url($xml ? route('filament.archivos.view', $xml) : null, shouldOpenInNewTab: true)
                ->visible((bool) $xml),
            Action::make('anular')
                ->label('Anular')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(auth()->user()?->can('ventas.anular') ?? false)
                ->action(function () use ($documento): void {
                    $documento->update([
                        'estado' => false,
                        'observaciones' => trim(($documento->observaciones ? $documento->observaciones.PHP_EOL : '').'Documento anulado manualmente.'),
                    ]);

                    Notification::make()
                        ->title('Documento anulado')
                        ->success()
                        ->send();
                }),
        ];
    }
}
