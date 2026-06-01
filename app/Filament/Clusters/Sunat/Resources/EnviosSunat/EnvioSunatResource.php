<?php

namespace App\Filament\Clusters\Sunat\Resources\EnviosSunat;

use App\Filament\Clusters\Sunat\Resources\EnviosSunat\Pages\ListEnviosSunat;
use App\Models\Sunat;
use App\Support\Facturacion\FacturacionService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use UnitEnum;

class EnvioSunatResource extends Resource
{
    protected static ?string $model = Sunat::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-globe-alt';

    protected static string|UnitEnum|null $navigationGroup = 'Sunat';

    protected static ?string $navigationLabel = 'Envíos SUNAT';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('documento.sucursal.nombre')
                    ->label('Sucursal')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->sortable()
                    ->visibleFrom('lg'),
                TextColumn::make('documento.tipo_comprobante')
                    ->label('Tipo')
                    ->state(fn (Sunat $record) => $record->documento?->tipo_comprobante)
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'FACTURA' => 'info',
                        'BOLETA' => 'success',
                        'NOTA_CREDITO' => 'warning',
                        default => 'gray',
                    })
                    ->icon(fn (?string $state): string => match ($state) {
                        'FACTURA' => 'heroicon-o-document-text',
                        'BOLETA' => 'heroicon-o-document',
                        'NOTA_CREDITO' => 'heroicon-o-document-duplicate',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->visibleFrom('md'),
                TextColumn::make('documento.serie')
                    ->label('Comprobante')
                    ->state(fn (Sunat $record) => $record->documento ? "{$record->documento->serie}-{$record->documento->numero}" : '')
                    ->weight('bold')
                    ->fontFamily('mono')
                    ->description(fn (Sunat $record) => ($record->documento?->tipo_comprobante ?? '') . ($record->documento?->sucursal ? " • {$record->documento->sucursal->nombre_sucursal}" : ''))
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('documento', function (Builder $q) use ($search) {
                            $q->where('serie', 'like', "%{$search}%")
                                ->orWhere('numero', 'like', "%{$search}%");
                        });
                    }),
                TextColumn::make('estado_sunat')
                    ->label('Estado SUNAT')
                    ->badge()
                    ->state(fn (Sunat $record): string => $record->estado_sunat ? 'ACEPTADO' : ($record->codigo_respuesta_sunat === 'ERROR' || $record->codigo_respuesta_sunat !== null ? 'CON ERROR' : 'PENDIENTE'))
                    ->color(fn (Sunat $record): string => $record->estado_sunat ? 'success' : ($record->codigo_respuesta_sunat === 'ERROR' || $record->codigo_respuesta_sunat !== null ? 'danger' : 'warning'))
                    ->icon(fn (Sunat $record): string => $record->estado_sunat ? 'heroicon-o-check-circle' : ($record->codigo_respuesta_sunat === 'ERROR' || $record->codigo_respuesta_sunat !== null ? 'heroicon-o-x-circle' : 'heroicon-o-clock'))
                    ->description(fn (Sunat $record): ?string => $record->codigo_respuesta_sunat !== null ? "Código: {$record->codigo_respuesta_sunat}" : null),
                TextColumn::make('mensaje_sunat')
                    ->label('Respuesta de SUNAT')
                    ->limit(100)
                    ->fontFamily('mono')
                    ->wrap()
                    ->tooltip(fn (Sunat $record): string => $record->mensaje_sunat ?? '')
                    ->visibleFrom('sm'),
                TextColumn::make('fecha_envio')
                    ->label('Fecha Envío')
                    ->dateTime('d/m/Y H:i:s')
                    ->description(fn (Sunat $record) => $record->fecha_envio?->diffForHumans())
                    ->sortable()
                    ->visibleFrom('lg'),
            ])
            ->actions([
                Action::make('reenviar')
                    ->label(fn (Sunat $record): string => ($record->estado_sunat && $record->codigo_respuesta_sunat !== '0') ? 'Enviar de nuevo' : 'Reenviar')
                    ->icon('heroicon-o-paper-airplane')
                    ->color(fn (Sunat $record): string => ($record->estado_sunat && $record->codigo_respuesta_sunat !== '0') ? 'info' : 'warning')
                    ->requiresConfirmation()
                    ->visible(fn (Sunat $record): bool => ! ($record->estado_sunat && $record->codigo_respuesta_sunat === '0'))
                    ->modalHeading(fn (Sunat $record): string => ($record->estado_sunat && $record->codigo_respuesta_sunat !== '0') ? 'Enviar de nuevo' : 'Reenviar Comprobante')
                    ->form([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\Placeholder::make('detalles_comprobante')
                                    ->label('Detalles del Comprobante')
                                    ->content(fn (Sunat $record) => new HtmlString("
                                        <div class='p-4 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/50 dark:border-slate-800/50 space-y-1.5 text-sm shadow-sm transition-all duration-300 hover:shadow-md'>
                                            <p><strong>Comprobante:</strong> <span class='font-mono font-bold bg-white dark:bg-slate-950 px-1.5 py-0.5 rounded border border-slate-200 dark:border-slate-800'>{$record->documento?->serie}-{$record->documento?->numero}</span></p>
                                            <p><strong>Tipo:</strong> <span class='text-primary-600 dark:text-primary-400 font-semibold'>{$record->documento?->tipo_comprobante}</span></p>
                                            <p><strong>Sucursal:</strong> {$record->documento?->sucursal?->nombre_sucursal}</p>
                                            <p><strong>Total:</strong> <span class='font-mono font-bold'>{$record->documento?->tipo_moneda} {$record->documento?->total_neto}</span></p>
                                        </div>
                                    ")),
                                Forms\Components\Placeholder::make('estado_actual')
                                    ->label('Estado Actual en Monitor')
                                    ->content(fn (Sunat $record) => new HtmlString("
                                        <div class='p-4 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/50 dark:border-slate-800/50 space-y-1.5 text-sm shadow-sm transition-all duration-300 hover:shadow-md'>
                                            <p><strong>Estado:</strong> " . ($record->estado_sunat ? "<span class='text-emerald-600 dark:text-emerald-400 font-black'>ACEPTADO (CON ADVERTENCIAS)</span>" : "<span class='text-rose-600 dark:text-rose-400 font-black'>CON ERROR / FALLIDO</span>") . "</p>
                                            <p><strong>Código Respuesta:</strong> <span class='font-mono font-semibold'> " . ($record->codigo_respuesta_sunat ?? 'Ninguno') . "</span></p>
                                            <p class='mt-1.5'><strong>Mensaje SUNAT:</strong></p>
                                            <code class='text-xs bg-white dark:bg-slate-950 p-2 rounded border border-slate-200 dark:border-slate-800 block overflow-x-auto whitespace-pre-wrap font-mono max-h-24 scrollbar-thin'>{$record->mensaje_sunat}</code>
                                        </div>
                                    ")),
                                Forms\Components\Toggle::make('confirmar_rectificacion')
                                    ->label('Confirmar envío / rectificación')
                                    ->helperText('Marque para autorizar la regeneración de XML, firma digital y transmisión del comprobante a la SUNAT.')
                                    ->required()
                                    ->default(true)
                                    ->columnSpan(2),
                            ])
                    ])
                    ->action(function (Sunat $record, FacturacionService $facturacionService, array $data) {
                        if (empty($data['confirmar_rectificacion'])) {
                            Notification::make()
                                ->title('Operación cancelada')
                                ->body('Debe marcar la casilla de confirmación para proceder.')
                                ->warning()
                                ->send();
                            return;
                        }

                        $documento = $record->documento;
                        if (! $documento) {
                            Notification::make()
                                ->title('Error')
                                ->body('No se encontró el documento asociado.')
                                ->danger()
                                ->send();

                            return;
                        }

                        try {
                            if ($documento->tipo_comprobante === 'NOTA_CREDITO') {
                                $documento->loadMissing('documentoReferencia.documentoReferenciado');
                                $documentoAfectado = $documento->documentoReferencia?->documentoReferenciado;
                                if (! $documentoAfectado) {
                                    throw new \RuntimeException('No se encontró el documento afectado por la nota de crédito.');
                                }
                                $sunat = $facturacionService->procesarNota($documento, $documentoAfectado);
                            } else {
                                $sunat = $facturacionService->procesar($documento);
                            }

                            if ($sunat && $sunat->estado_sunat && $sunat->codigo_respuesta_sunat === '0') {
                                Notification::make()
                                    ->title('Envío Exitoso')
                                    ->body("El comprobante {$documento->serie}-{$documento->numero} fue aceptado por SUNAT sin observaciones.")
                                    ->success()
                                    ->send();
                            } else {
                                $errMsg = $sunat ? $sunat->mensaje_sunat : 'Error desconocido.';
                                Notification::make()
                                    ->title($sunat && $sunat->estado_sunat ? 'Aceptado con observaciones' : 'Envío Fallido')
                                    ->body("Respuesta SUNAT: {$errMsg}")
                                    ->color($sunat && $sunat->estado_sunat ? 'warning' : 'danger')
                                    ->persistent()
                                    ->send();
                            }
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Error de Procesamiento')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEnviosSunat::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('empresa_id', Auth::user()?->empresa_id)
            ->with(['documento.sucursal', 'documento.documentoReferencia.documentoReferenciado']);
    }

    public static function canViewAny(): bool
    {
        return Auth::user()?->can('sunat.monitor') ?? false;
    }
}
