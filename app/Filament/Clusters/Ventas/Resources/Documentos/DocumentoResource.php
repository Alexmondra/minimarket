<?php

namespace App\Filament\Clusters\Ventas\Resources\Documentos;

use App\Filament\Clusters\Ventas\Resources\Documentos\Pages\ListDocumentos;
use App\Filament\Clusters\Ventas\Resources\Documentos\Pages\RegistrarVenta;
use App\Filament\Clusters\Ventas\Resources\Documentos\Pages\ViewDocumento;
use App\Models\Documento;
use App\Support\SucursalContext;
use App\Support\Ventas\AnulacionService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class DocumentoResource extends Resource
{
    protected static ?string $model = Documento::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|UnitEnum|null $navigationGroup = 'Ventas';

    protected static ?string $navigationLabel = 'Ventas';

    protected static ?string $recordTitleAttribute = 'numero';

    public static function getPages(): array
    {
        return [
            'index' => ListDocumentos::route('/'),
            'registrar' => RegistrarVenta::route('/registrar'),
            'view' => ViewDocumento::route('/{record}'),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordAction(null)
            ->recordUrl(null)
            ->columns([
                TextColumn::make('tipo_comprobante')
                    ->badge()
                    ->icon(fn ($state) => match ($state) {
                        'FACTURA' => 'heroicon-o-document-check',
                        'BOLETA' => 'heroicon-o-receipt-refund',
                        'TICKET' => 'heroicon-o-ticket',
                        default => 'heroicon-o-document-text',
                    })
                    ->color(fn ($state) => match ($state) {
                        'FACTURA' => 'info',
                        'BOLETA' => 'success',
                        'TICKET' => 'gray',
                        default => 'warning',
                    })
                    ->label('Tipo'),
                TextColumn::make('serie')
                    ->label('Serie')
                    ->badge()
                    ->color('primary')
                    ->weight('bold'),
                TextColumn::make('numero')
                    ->label('Numero')
                    ->prefix('#')
                    ->weight('semibold')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('cliente.documento')
                    ->label('Documento cliente')
                    ->icon('heroicon-o-identification')
                    ->searchable()
                    ->formatStateUsing(fn (?string $state): ?string => blank($state) || $state === '00000000' ? null : $state)
                    ->placeholder('-'),
                TextColumn::make('cliente.razon_social')
                    ->label('Cliente')
                    ->icon('heroicon-o-user-circle')
                    ->searchable()
                    ->formatStateUsing(fn ($state, Documento $record) => $state ?: trim(($record->cliente?->nombre ?? '').' '.($record->cliente?->apellido ?? '')))
                    ->placeholder('Cliente varios'),
                TextColumn::make('medio_pago')
                    ->label('Pago')
                    ->icon('heroicon-o-credit-card')
                    ->badge()
                    ->color('warning'),
                TextColumn::make('total_neto')
                    ->label('Total')
                    ->money('PEN')
                    ->weight('bold')
                    ->color('success')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Emitido')
                    ->icon('heroicon-o-clock')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('tipo_comprobante')
                    ->options([
                        'TICKET' => 'Ticket',
                        'BOLETA' => 'Boleta',
                        'FACTURA' => 'Factura',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make()
                    ->label('Ver detalle')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->button()
                    ->size('sm')
                    ->extraAttributes(['class' => 'mm-table-action mm-table-action-info']),
                Action::make('emitirNotaCredito')
                    ->label('Nota credito')
                    ->icon('heroicon-o-document-minus')
                    ->color('warning')
                    ->button()
                    ->size('sm')
                    ->extraAttributes(['class' => 'mm-table-action mm-table-action-warning'])
                    ->visible(fn (Documento $record): bool => $record->estado === true
                        && in_array($record->tipo_comprobante, ['BOLETA', 'FACTURA'], true)
                        && (auth()->user()?->can('ventas.anular') ?? false))
                    ->form([
                        Select::make('motivo_codigo')
                            ->label('Motivo de la nota de credito')
                            ->options(AnulacionService::MOTIVOS)
                            ->default('01')
                            ->required()
                            ->native(false),
                    ])
                    ->requiresConfirmation()
                    ->modalHeading('Emitir nota de credito')
                    ->modalDescription('Se anulara el comprobante, se restaurara el stock y se generara la nota de credito para SUNAT.')
                    ->action(function (array $data, Documento $record): void {
                        $motivoCodigo = $data['motivo_codigo'];
                        $motivoDescripcion = AnulacionService::MOTIVOS[$motivoCodigo] ?? 'Anulacion de la operacion';

                        try {
                            $notaCredito = app(AnulacionService::class)->anular(
                                user: auth()->user(),
                                documento: $record,
                                motivoCodigo: $motivoCodigo,
                                motivoDescripcion: $motivoDescripcion,
                            );

                            Notification::make()
                                ->title('Nota de credito emitida')
                                ->body("Se genero {$notaCredito->serie}-{$notaCredito->numero} y se encolo el envio a SUNAT.")
                                ->success()
                                ->send();
                        } catch (\RuntimeException $e) {
                            Notification::make()
                                ->title('No se pudo emitir la nota de credito')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return app(SucursalContext::class)->applyToQuery(
            parent::getEloquentQuery()->with(['cliente', 'sucursal'])
        );
    }

    public static function canViewAny(): bool
    {
        return Auth::user()?->can('ventas.ver') ?? false;
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->can('ventas.crear') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return Auth::user()?->can('ventas.anular') ?? false;
    }
}
