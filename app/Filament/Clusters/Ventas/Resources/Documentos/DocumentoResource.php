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
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
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
                TextColumn::make('numero')
                    ->label('Comprobante')
                    ->formatStateUsing(fn (Documento $record): string => "{$record->serie}-{$record->numero}")
                    ->weight('bold')
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query
                        ->where('numero', 'like', "%{$search}%")
                        ->orWhere('serie', 'like', "%{$search}%")
                    )
                    ->sortable(['numero']),
                TextColumn::make('tipo_comprobante')
                    ->label('Tipo')
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
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'FACTURA' => 'Factura',
                        'BOLETA' => 'Boleta',
                        'TICKET' => 'Ticket',
                        default => $state,
                    }),
                TextColumn::make('cliente.razon_social')
                    ->label('Cliente')
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query
                        ->whereHas('cliente', fn (Builder $q) => $q
                            ->where('razon_social', 'like', "%{$search}%")
                            ->orWhere('nombre', 'like', "%{$search}%")
                            ->orWhere('apellido', 'like', "%{$search}%")
                            ->orWhere('documento', 'like', "%{$search}%")
                        )
                    )
                    ->formatStateUsing(fn ($state, Documento $record) => $state
                        ?: trim(($record->cliente?->nombre ?? '') . ' ' . ($record->cliente?->apellido ?? ''))
                        ?: 'Público general'
                    )
                    ->placeholder('Público general'),
                TextColumn::make('medio_pago')
                    ->label('Pago')
                    ->badge()
                    ->color(fn ($state) => match (strtoupper((string) $state)) {
                        'EFECTIVO' => 'success',
                        'YAPE' => 'warning',
                        'PLIN' => 'info',
                        'TARJETA' => 'primary',
                        'TRANSFERENCIA' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => $state ?: '—'),
                TextColumn::make('total_neto')
                    ->label('Total')
                    ->money('PEN')
                    ->weight('bold')
                    ->color('success')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'danger')
                    ->formatStateUsing(fn ($state) => $state ? 'Activo' : 'Anulado')
                    ->sortable(),
                TextColumn::make('fecha_emision')
                    ->label('Emisión')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Registrado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('user.name')
                    ->label('Usuario')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('tipo_comprobante')
                    ->label('Tipo')
                    ->options([
                        'TICKET' => 'Ticket',
                        'BOLETA' => 'Boleta',
                        'FACTURA' => 'Factura',
                    ]),
                SelectFilter::make('medio_pago')
                    ->label('Medio de pago')
                    ->options([
                        'EFECTIVO' => 'Efectivo',
                        'YAPE' => 'Yape',
                        'PLIN' => 'Plin',
                        'TARJETA' => 'Tarjeta',
                        'TRANSFERENCIA' => 'Transferencia',
                        'OTRO' => 'Otro',
                    ]),
                TernaryFilter::make('estado')
                    ->label('Estado')
                    ->placeholder('Todos')
                    ->trueLabel('Activos')
                    ->falseLabel('Anulados')
                    ->default(true)
                    ->queries(
                        true: fn (Builder $query) => $query->where('estado', true),
                        false: fn (Builder $query) => $query->where('estado', false),
                        blank: fn (Builder $query) => $query,
                    ),
                Filter::make('fecha_emision')
                    ->label('Rango de fecha')
                    ->form([
                        DatePicker::make('desde')
                            ->label('Desde')
                            ->displayFormat('d/m/Y'),
                        DatePicker::make('hasta')
                            ->label('Hasta')
                            ->displayFormat('d/m/Y'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['desde'] ?? null, fn (Builder $q, $date) => $q->whereDate('fecha_emision', '>=', $date))
                        ->when($data['hasta'] ?? null, fn (Builder $q, $date) => $q->whereDate('fecha_emision', '<=', $date))
                    )
                    ->columns(2),
                Filter::make('total_neto')
                    ->label('Rango de monto')
                    ->form([
                        TextInput::make('desde')
                            ->label('Mínimo')
                            ->numeric()
                            ->prefix('S/'),
                        TextInput::make('hasta')
                            ->label('Máximo')
                            ->numeric()
                            ->prefix('S/'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['desde'] ?? null, fn (Builder $q, $monto) => $q->where('total_neto', '>=', $monto))
                        ->when($data['hasta'] ?? null, fn (Builder $q, $monto) => $q->where('total_neto', '<=', $monto))
                    )
                    ->columns(2),
            ])
            ->filtersFormColumns(3)
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
                    ->label('Nota crédito')
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
                            ->label('Motivo de la nota de crédito')
                            ->options(AnulacionService::MOTIVOS)
                            ->default('01')
                            ->required()
                            ->native(false),
                    ])
                    ->requiresConfirmation()
                    ->modalHeading('Emitir nota de crédito')
                    ->modalDescription('Se anulará el comprobante, se restaurará el stock y se generará la nota de crédito para SUNAT.')
                    ->action(function (array $data, Documento $record): void {
                        $motivoCodigo = $data['motivo_codigo'];
                        $motivoDescripcion = AnulacionService::MOTIVOS[$motivoCodigo] ?? 'Anulación de la operación';

                        try {
                            $notaCredito = app(AnulacionService::class)->anular(
                                user: auth()->user(),
                                documento: $record,
                                motivoCodigo: $motivoCodigo,
                                motivoDescripcion: $motivoDescripcion,
                            );

                            Notification::make()
                                ->title('Nota de crédito emitida')
                                ->body("Se generó {$notaCredito->serie}-{$notaCredito->numero} y se encoló el envío a SUNAT.")
                                ->success()
                                ->send();
                        } catch (\RuntimeException $e) {
                            Notification::make()
                                ->title('No se pudo emitir la nota de crédito')
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
            parent::getEloquentQuery()->with(['cliente', 'sucursal', 'user'])
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
