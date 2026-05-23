<?php

namespace App\Filament\Clusters\Configuraciones\Resources\Empresas\Schemas;

use App\Models\Empresa;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class EmpresaInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('ruc'),
                TextEntry::make('logo')
                    ->placeholder('-'),
                IconEntry::make('incluido_tributo')
                    ->boolean(),
                TextEntry::make('razon_social'),
                TextEntry::make('direccion_fiscal')
                    ->placeholder('-'),
                IconEntry::make('entorno')
                      ->boolean(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Empresa $record): bool => $record->trashed()),
            ]);
    }
}
