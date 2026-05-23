<?php

namespace App\Filament\Clusters\Configuraciones\Resources\Usuarios\Schemas;

use App\Models\User;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class UsuarioInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label('Nombre'),
                TextEntry::make('email'),
                TextEntry::make('telefono')
                    ->placeholder('-'),
                IconEntry::make('activo')
                    ->boolean(),
                TextEntry::make('ultimo_acceso')
                    ->dateTime()
                    ->placeholder('-')
                    ->label('Último acceso'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-')
                    ->label('Creado'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (User $record): bool => $record->trashed())
                    ->label('Eliminado'),
                RepeatableEntry::make('sucursales')
                    ->label('Sucursales asignadas')
                    ->schema([
                        TextEntry::make('nombre_sucursal'),
                    ]),
                RepeatableEntry::make('roles')
                    ->label('Roles asignados')
                    ->schema([
                        TextEntry::make('name'),
                    ]),
            ]);
    }
}
