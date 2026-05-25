<?php

namespace App\Filament\Clusters\Ventas\Resources\Documentos;

use App\Filament\Clusters\Ventas\Resources\Documentos\Pages\ListDocumentos;
use App\Filament\Clusters\Ventas\Resources\Documentos\Pages\RegistrarVenta;
use App\Filament\Clusters\Ventas\Resources\Documentos\Pages\ViewDocumento;
use App\Models\Documento;
use App\Support\SucursalContext;
use BackedEnum;
use Filament\Actions\ViewAction;
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
            ->columns([
                TextColumn::make('tipo_comprobante')
                    ->badge()
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
                    ->color('gray'),
                TextColumn::make('numero')
                    ->label('Numero')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('cliente.documento')
                    ->label('Documento cliente')
                    ->searchable()
                    ->placeholder('00000000'),
                TextColumn::make('cliente.razon_social')
                    ->label('Cliente')
                    ->searchable()
                    ->formatStateUsing(fn ($state, Documento $record) => $state ?: trim(($record->cliente?->nombre ?? '').' '.($record->cliente?->apellido ?? '')))
                    ->placeholder('Cliente varios'),
                TextColumn::make('medio_pago')
                    ->label('Pago')
                    ->badge()
                    ->color('warning'),
                TextColumn::make('total_neto')
                    ->label('Total')
                    ->money('PEN')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Emitido')
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
                ViewAction::make(),
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
