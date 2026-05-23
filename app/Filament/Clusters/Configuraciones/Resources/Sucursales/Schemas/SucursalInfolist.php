<?php

namespace App\Filament\Clusters\Configuraciones\Resources\Sucursales\Schemas;

use App\Models\Sucursal;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SucursalInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('codigo'),
                TextEntry::make('nombre_sucursal')
                    ->label('Nombre'),
                TextEntry::make('ubigeoRel.distrito')
                    ->label('Distrito'),
                TextEntry::make('ubigeoRel.provincia')
                    ->label('Provincia'),
                TextEntry::make('ubigeoRel.departamento')
                    ->label('Departamento'),
                TextEntry::make('direccion'),
                TextEntry::make('telefono')
                    ->placeholder('-'),
                TextEntry::make('email')
                    ->placeholder('-'),
                TextEntry::make('impuesto_porcentaje')
                    ->suffix('%')
                    ->placeholder('0'),
                IconEntry::make('activo')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Sucursal $record): bool => $record->trashed()),
            ]);
    }
}
