<?php

namespace App\Filament\Clusters\Ventas\Resources\PuntosCliente;

use App\Filament\Clusters\Ventas\Resources\PuntosCliente\Pages\ListPuntosCliente;
use App\Models\ClientePunto;
use App\Support\SucursalContext;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class PuntosClienteResource extends Resource
{
    protected static ?string $model = ClientePunto::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-star';

    protected static string|UnitEnum|null $navigationGroup = 'Ventas';

    protected static ?string $navigationLabel = 'Puntos Cliente';

    public static function getPages(): array
    {
        return [
            'index' => ListPuntosCliente::route('/'),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('cliente.documento')
                    ->label('Documento')
                    ->searchable(),
                TextColumn::make('cliente.razon_social')
                    ->label('Cliente')
                    ->formatStateUsing(fn ($state, ClientePunto $record) => $state ?: trim(($record->cliente?->nombre ?? '').' '.($record->cliente?->apellido ?? '')))
                    ->searchable(),
                TextColumn::make('sucursal.nombre_sucursal')
                    ->label('Sucursal'),
                TextColumn::make('puntos')
                    ->label('Puntos')
                    ->badge()
                    ->color('warning'),
                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        return app(SucursalContext::class)->applyToQuery(
            parent::getEloquentQuery()->where('empresa_id', Auth::user()->empresa_id)->with(['cliente', 'sucursal'])
        );
    }

    public static function canViewAny(): bool
    {
        return Auth::user()?->can('ventas.ver') ?? false;
    }
}
