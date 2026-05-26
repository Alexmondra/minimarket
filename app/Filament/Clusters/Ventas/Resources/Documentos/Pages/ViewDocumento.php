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
        $xml = $documento->archivos->firstWhere('tipo_archivo', 'xml')
            ?: $documento->archivos->firstWhere('tipo_archivo', 'xml_firmado');
        $cdr = $documento->archivos->firstWhere('tipo_archivo', 'cdr_zip')
            ?: $documento->archivos->firstWhere('tipo_archivo', 'cdr_xml');

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
                    \Filament\Forms\Components\Select::make('tipo_comprobante')
                        ->label('Tipo de Comprobante')
                        ->options([
                            'BOLETA' => 'Boleta de Venta',
                            'FACTURA' => 'Factura',
                        ])
                        ->required()
                        ->live(),
                    \Filament\Forms\Components\Select::make('cliente_id')
                        ->label('Cliente')
                        ->options(\App\Models\Cliente::all()->mapWithKeys(function ($cliente) {
                            $nombreCompleto = trim($cliente->razon_social ?: ($cliente->nombre . ' ' . $cliente->apellido));
                            return [$cliente->id => "{$cliente->documento} - {$nombreCompleto}"];
                        }))
                        ->searchable()
                        ->required(fn (callable $get) => $get('tipo_comprobante') === 'FACTURA'),
                ])
                ->action(function (array $data) use ($documento): void {
                    \Illuminate\Support\Facades\DB::transaction(function () use ($documento, $data): void {
                        $tipoComprobante = $data['tipo_comprobante'];
                        $clienteId = $data['cliente_id'];

                        $serie = \App\Models\Serie::query()
                            ->where('sucursal_id', $documento->sucursal_id)
                            ->where('tipo_comprobante', $tipoComprobante)
                            ->lockForUpdate()
                            ->first();

                        if (!$serie) {
                            $serie = \App\Models\Serie::create([
                                'sucursal_id' => $documento->sucursal_id,
                                'tipo_comprobante' => $tipoComprobante,
                                'serie' => $tipoComprobante === 'FACTURA' ? 'F001' : 'B001',
                                'correlativo' => 1,
                            ]);
                        }

                        $numero = str_pad((string) $serie->correlativo, 8, '0', STR_PAD_LEFT);
                        $serie->increment('correlativo');

                        $documento->update([
                            'tipo_comprobante' => $tipoComprobante,
                            'serie' => $serie->serie,
                            'numero' => $numero,
                            'cliente_id' => $clienteId ?: $documento->cliente_id,
                        ]);
                    });

                    // Delete old ticket_html and pdf files
                    $oldArchivos = $documento->archivos()->whereIn('tipo_archivo', ['ticket_html', 'pdf'])->get();
                    foreach ($oldArchivos as $old) {
                        if ($old->ruta_archivo && \Illuminate\Support\Facades\Storage::disk('local')->exists($old->ruta_archivo)) {
                            \Illuminate\Support\Facades\Storage::disk('local')->delete($old->ruta_archivo);
                        }
                        $old->forceDelete();
                    }

                    // Load missing relations
                    $documento->load([
                        'empresa',
                        'sucursal',
                        'cliente',
                        'detalles.presentacion.unidadMedida',
                    ]);

                    // Render and save ticket HTML
                    $htmlTicket = view('ventas.ticket', ['documento' => $documento])->render();
                    $ventaFileService = app(\App\Support\Ventas\VentaFileService::class);
                    $ventaFileService->guardarTicketHtml($documento, $htmlTicket);

                    // Render and save PDF
                    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('ventas.pdf', ['documento' => $documento]);
                    $ventaFileService->guardarPdf($documento, $pdf->output());

                    // Send to SUNAT
                    app(\App\Support\Facturacion\FacturacionService::class)->procesar($documento);

                    Notification::make()
                        ->title('Comprobante convertido y enviado a SUNAT con éxito')
                        ->success()
                        ->send();

                    $this->redirect(static::$resource::getUrl('view', ['record' => $documento]));
                }),
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
