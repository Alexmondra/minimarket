<?php

namespace App\Filament\Clusters\Ventas\Resources\Clientes;

use App\Filament\Clusters\Ventas\Resources\Clientes\Pages\ListClientes;
use App\Models\Cliente;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
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
            ->columns([
                TextColumn::make('tipo_documento')
                    ->label('Tipo')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('documento')
                    ->label('Documento')
                    ->searchable(),
                TextColumn::make('razon_social')
                    ->label('Cliente')
                    ->searchable()
                    ->formatStateUsing(fn ($state, Cliente $record) => $state ?: trim(($record->nombre ?? '').' '.($record->apellido ?? ''))),
                TextColumn::make('telefono')
                    ->label('Telefono')
                    ->placeholder('-'),
                TextColumn::make('email')
                    ->label('Correo')
                    ->placeholder('-'),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function canViewAny(): bool
    {
        return Auth::user()?->can('clientes.ver') ?? false;
    }
}
