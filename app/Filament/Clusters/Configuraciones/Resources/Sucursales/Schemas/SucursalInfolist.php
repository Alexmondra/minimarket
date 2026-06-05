<?php

namespace App\Filament\Clusters\Configuraciones\Resources\Sucursales\Schemas;

use App\Models\Sucursal;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SucursalInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identidad')
                    ->icon('heroicon-o-building-storefront')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('codigo')
                            ->badge()
                            ->color('primary'),
                        TextEntry::make('nombre_sucursal')
                            ->label('Nombre')
                            ->weight('bold'),
                        IconEntry::make('activo')
                            ->boolean()
                            ->label('Activa'),
                    ]),
                Section::make('Ubicacion y contacto')
                    ->icon('heroicon-o-map-pin')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('ubigeoRel.distrito')
                            ->label('Distrito'),
                        TextEntry::make('ubigeoRel.provincia')
                            ->label('Provincia'),
                        TextEntry::make('ubigeoRel.departamento')
                            ->label('Departamento'),
                        TextEntry::make('direccion')
                            ->columnSpan(3),
                        TextEntry::make('telefono')
                            ->placeholder('-'),
                        TextEntry::make('email')
                            ->placeholder('-'),
                        TextEntry::make('impuesto_porcentaje')
                            ->suffix('%')
                            ->badge()
                            ->color('success')
                            ->placeholder('0'),
                    ]),
                Section::make('Auditoria')
                    ->icon('heroicon-o-clock')
                    ->columns(3)
                    ->collapsible()
                    ->schema([
                        TextEntry::make('created_at')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('deleted_at')
                            ->dateTime()
                            ->visible(fn (Sucursal $record): bool => $record->trashed()),
                    ]),
            ]);
    }
}
