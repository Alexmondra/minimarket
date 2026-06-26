<?php

namespace App\Filament\Clusters\Ventas\Resources\Documentos\Pages;

use App\Filament\Clusters\Ventas\Resources\Documentos\DocumentoResource;
use App\Models\Cliente;
use App\Support\Ventas\AnulacionService;
use App\Support\Ventas\ConvertirTicketService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
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
        $xml = $documento->archivos->firstWhere('tipo_archivo', 'xml')
            ?: $documento->archivos->firstWhere('tipo_archivo', 'xml_firmado');
        $cdr = $documento->archivos->firstWhere('tipo_archivo', 'cdr_zip');

        return [
            Action::make('verTicket')
                ->label('Ver ticket')
                ->icon('heroicon-o-printer')
                ->url(route('filament.documentos.ticket', $documento), shouldOpenInNewTab: true),
            Action::make('verPdf')
                ->label('PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->url(route('filament.documentos.pdf', $documento), shouldOpenInNewTab: true),
            Action::make('verXml')
                ->label('Ver XML')
                ->icon('heroicon-o-code-bracket')
                ->url($xml ? route('filament.archivos.view', $xml) : null, shouldOpenInNewTab: true)
                ->visible((bool) $xml),
            Action::make('descargarCdr')
                ->label('CDR')
                ->icon('heroicon-o-arrow-down-tray')
                ->url($cdr ? route('filament.archivos.download', $cdr) : null, shouldOpenInNewTab: true)
                ->visible((bool) $cdr),
            Action::make('convertirAComprobante')
                ->label('Convertir a Comprobante')
                ->icon('heroicon-o-arrows-right-left')
                ->color('success')
                ->visible(fn () => $documento->tipo_comprobante === 'TICKET')
                ->form([
                    Select::make('tipo_comprobante')
                        ->label('Tipo de Comprobante')
                        ->options([
                            'BOLETA' => 'Boleta de Venta',
                            'FACTURA' => 'Factura',
                        ])
                        ->required()
                        ->live(),
                    Select::make('cliente_id')
                        ->label('Cliente')
                        ->options(Cliente::all()->mapWithKeys(function ($cliente) {
                            $nombreCompleto = trim($cliente->razon_social ?: ($cliente->nombre.' '.$cliente->apellido));

                            return [$cliente->id => "{$cliente->documento} - {$nombreCompleto}"];
                        }))
                        ->searchable()
                        ->required(fn (callable $get) => $get('tipo_comprobante') === 'FACTURA'),
                ])
                ->action(function (array $data) use ($documento): void {
                    try {
                        $convertido = app(ConvertirTicketService::class)->convertir(
                            documento: $documento,
                            tipoComprobante: $data['tipo_comprobante'],
                            clienteId: $data['cliente_id'] ?? null,
                        );

                        Notification::make()
                            ->title('Comprobante convertido')
                            ->body('El XML y PDF fueron generados. El envío a SUNAT quedó en cola.')
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('No se pudo convertir el ticket')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();

                        return;
                    }

                    $this->redirect(static::$resource::getUrl('view', ['record' => $convertido]));
                }),
            Action::make('anular')
                ->label('Anular')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () => $documento->estado === true && (auth()->user()?->can('ventas.anular') ?? false))
                ->form([
                    Select::make('motivo_codigo')
                        ->label('Motivo de Anulación')
                        ->options(AnulacionService::MOTIVOS)
                        ->default('01')
                        ->required()
                        ->live(),
                ])
                ->action(function (array $data) use ($documento): void {
                    $motivoCodigo = $data['motivo_codigo'];
                    $motivoDescripcion = AnulacionService::MOTIVOS[$motivoCodigo] ?? 'Anulación de la operación';

                    try {
                        $notaCredito = app(AnulacionService::class)->anular(
                            user: auth()->user(),
                            documento: $documento,
                            motivoCodigo: $motivoCodigo,
                            motivoDescripcion: $motivoDescripcion,
                        );

                        if ($documento->tipo_comprobante === 'TICKET') {
                            Notification::make()
                                ->title('Documento anulado con éxito')
                                ->body("Se restauró el stock. El ticket {$documento->serie}-{$documento->numero} ha sido anulado.")
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Documento anulado con éxito')
                                ->body("Se generó la Nota de Crédito {$notaCredito->serie}-{$notaCredito->numero} y se encoló el envío a SUNAT.")
                                ->success()
                                ->send();
                        }
                    } catch (\RuntimeException $e) {
                        Notification::make()
                            ->title('Error al anular')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }

                    $this->redirect(static::$resource::getUrl('view', ['record' => $documento]));
                }),
        ];
    }
}
