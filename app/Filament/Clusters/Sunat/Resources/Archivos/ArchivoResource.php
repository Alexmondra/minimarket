<?php

namespace App\Filament\Clusters\Sunat\Resources\Archivos;

use App\Filament\Clusters\Sunat\Resources\Archivos\Pages\ListArchivos;
use App\Models\Archivo;
use App\Support\Ventas\VentaFileService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class ArchivoResource extends Resource
{
    protected static ?string $model = Archivo::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-folder';

    protected static string|UnitEnum|null $navigationGroup = 'Sunat';

    protected static ?string $navigationLabel = 'Archivos';

    public static function getPages(): array
    {
        return [
            'index' => ListArchivos::route('/'),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('comprobante')
                    ->label('Comprobante')
                    ->state(fn (Archivo $record): string => $record->documento ? "{$record->documento->serie}-{$record->documento->numero}" : '')
                    ->badge()
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('documento', function (Builder $q) use ($search) {
                            $q->where('serie', 'like', "%{$search}%")
                              ->orWhere('numero', 'like', "%{$search}%");
                        });
                    }),
                TextColumn::make('bucket')
                    ->label('Bucket')
                    ->badge(),
            ])
            ->actions([
                Action::make('xml')
                    ->label('XML')
                    ->icon('heroicon-o-code-bracket')
                    ->url(fn (Archivo $record) => ($xml = $record->documento?->archivos->firstWhere(fn($a) => in_array($a->tipo_archivo, ['xml', 'xml_firmado']))) ? route('filament.archivos.view', $xml) : null, shouldOpenInNewTab: true)
                    ->visible(fn (Archivo $record) => $record->documento?->tipo_comprobante !== 'TICKET' && (bool) $record->documento?->archivos->firstWhere(fn($a) => in_array($a->tipo_archivo, ['xml', 'xml_firmado']))),
                Action::make('cdr')
                    ->label('CDR')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (Archivo $record) => ($cdr = $record->documento?->archivos->firstWhere('tipo_archivo', 'cdr_zip')) ? route('filament.archivos.download', $cdr) : null, shouldOpenInNewTab: true)
                    ->visible(fn (Archivo $record) => $record->documento?->tipo_comprobante !== 'TICKET' && (bool) $record->documento?->archivos->firstWhere('tipo_archivo', 'cdr_zip')),
                Action::make('ticket')
                    ->label('Ticket')
                    ->icon('heroicon-o-printer')
                    ->url(fn (Archivo $record) => route('filament.documentos.ticket', $record->documento_id), shouldOpenInNewTab: true),
                Action::make('pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->url(fn (Archivo $record) => route('filament.documentos.pdf', $record->documento_id), shouldOpenInNewTab: true),
                Action::make('convertirAComprobante')
                    ->label('Convertir')
                    ->icon('heroicon-o-arrows-right-left')
                    ->color('success')
                    ->visible(fn (Archivo $record) => $record->documento?->tipo_comprobante === 'TICKET')
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
                    ->action(function (Archivo $record, array $data): void {
                        $documento = $record->documento;
                        if (!$documento) return;

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
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('documento', fn (Builder $query) => $query->where('empresa_id', Auth::user()->empresa_id))
            ->where(function (Builder $query) {
                $query->where(function (Builder $q) {
                    $q->whereHas('documento', fn ($d) => $d->whereIn('tipo_comprobante', ['BOLETA', 'FACTURA']))
                      ->whereIn('tipo_archivo', ['xml', 'xml_firmado']);
                })->orWhere(function (Builder $q) {
                    $q->whereHas('documento', fn ($d) => $d->where('tipo_comprobante', 'TICKET'))
                      ->where('tipo_archivo', 'ticket_html');
                });
            })
            ->with(['documento', 'documento.archivos']);
    }

    public static function canViewAny(): bool
    {
        return Auth::user()?->can('sunat.archivos') ?? false;
    }
}
