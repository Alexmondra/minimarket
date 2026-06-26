<?php

namespace App\Filament\Clusters\Ventas\Resources\Clientes;

use App\Filament\Clusters\Ventas\Resources\Clientes\Pages\ListClientes;
use App\Models\Cliente;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class ClienteResource extends Resource
{
    protected static ?string $model = Cliente::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static string|UnitEnum|null $navigationGroup = 'Ventas';

    protected static ?string $navigationLabel = 'Clientes';

    public static function getPages(): array
    {
        return [
            'index' => ListClientes::route('/'),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordAction(null)
            ->recordUrl(null)
            ->columns([
                TextColumn::make('tipo_documento')
                    ->label('Tipo')
                    ->icon('heroicon-o-identification')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'RUC' => 'info',
                        'DNI' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('documento')
                    ->label('Documento')
                    ->weight('semibold')
                    ->formatStateUsing(fn (?string $state): ?string => blank($state) || $state === '00000000' ? null : $state)
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('cliente_nombre')
                    ->label('Cliente')
                    ->icon('heroicon-o-user-circle')
                    ->weight('bold')
                    ->state(fn (Cliente $record): string => self::nombreCliente($record))
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query
                        ->where('razon_social', 'like', "%{$search}%")
                        ->orWhere('nombre', 'like', "%{$search}%")
                        ->orWhere('apellido', 'like', "%{$search}%")
                        ->orWhere('documento', 'like', "%{$search}%")
                    ),
                TextColumn::make('telefono')
                    ->label('Telefono')
                    ->icon('heroicon-o-phone')
                    ->placeholder('-'),
                TextColumn::make('email')
                    ->label('Correo')
                    ->icon('heroicon-o-envelope')
                    ->placeholder('-'),
            ])
            ->defaultSort('id', 'desc');
    }

    protected static function nombreCliente(Cliente $cliente): string
    {
        $nombre = trim((string) ($cliente->razon_social ?: trim(($cliente->nombre ?? '').' '.($cliente->apellido ?? ''))));

        return $nombre !== '' ? $nombre : 'Cliente sin nombre';
    }

    public static function canViewAny(): bool
    {
        return Auth::user()?->can('clientes.ver') ?? false;
    }
}
