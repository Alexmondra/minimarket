<?php

namespace App\Filament\Clusters\Ventas\Resources\PuntosCliente;

use App\Filament\Clusters\Ventas\Resources\PuntosCliente\Pages\ListPuntosCliente;
use App\Models\Cliente;
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
                TextColumn::make('cliente_nombre')
                    ->label('Cliente')
                    ->state(fn (ClientePunto $record): string => $record->cliente ? self::nombreCliente($record->cliente) : 'Cliente sin nombre')
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query
                        ->whereHas('cliente', fn (Builder $q): Builder => $q
                            ->where('razon_social', 'like', "%{$search}%")
                            ->orWhere('nombre', 'like', "%{$search}%")
                            ->orWhere('apellido', 'like', "%{$search}%")
                            ->orWhere('documento', 'like', "%{$search}%")
                        )
                    ),
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

    protected static function nombreCliente(Cliente $cliente): string
    {
        $nombre = trim((string) ($cliente->razon_social ?: trim(($cliente->nombre ?? '').' '.($cliente->apellido ?? ''))));

        return $nombre !== '' ? $nombre : 'Cliente sin nombre';
    }

    public static function getEloquentQuery(): Builder
    {
        return app(SucursalContext::class)->applyToQuery(
            parent::getEloquentQuery()->where('empresa_id', Auth::user()->empresa_id)->whereHas('cliente', fn ($q) => $q->where('documento', '!=', '00000000'))->with(['cliente', 'sucursal'])
        );
    }

    public static function canViewAny(): bool
    {
        return Auth::user()?->can('ventas.ver') ?? false;
    }
}
