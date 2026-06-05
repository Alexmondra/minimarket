<?php

namespace App\Filament\Clusters\Configuraciones\Resources\Usuarios\Schemas;

use App\Models\User;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UsuarioInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Perfil')
                    ->icon('heroicon-o-user-circle')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nombre')
                            ->weight('bold'),
                        TextEntry::make('email')
                            ->icon('heroicon-o-envelope'),
                        TextEntry::make('telefono')
                            ->icon('heroicon-o-phone')
                            ->placeholder('-'),
                        IconEntry::make('activo')
                            ->boolean(),
                        TextEntry::make('ultimo_acceso')
                            ->dateTime()
                            ->placeholder('-')
                            ->label('Ultimo acceso'),
                        TextEntry::make('created_at')
                            ->dateTime()
                            ->placeholder('-')
                            ->label('Creado'),
                        TextEntry::make('deleted_at')
                            ->dateTime()
                            ->visible(fn (User $record): bool => $record->trashed())
                            ->label('Eliminado'),
                    ]),
                Section::make('Accesos')
                    ->icon('heroicon-o-shield-check')
                    ->columns(2)
                    ->schema([
                        RepeatableEntry::make('sucursales')
                            ->label('Sucursales asignadas')
                            ->schema([
                                TextEntry::make('nombre_sucursal')
                                    ->badge()
                                    ->color('info'),
                            ]),
                        RepeatableEntry::make('roles')
                            ->label('Roles asignados')
                            ->schema([
                                TextEntry::make('name')
                                    ->badge()
                                    ->color('primary'),
                            ]),
                    ]),
            ]);
    }
}
