<?php

namespace App\Filament\Clusters\Configuraciones\Resources\Ubigeos\Schemas;

use App\Models\Ubigeo;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class UbigeoInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('codigo'),
                TextEntry::make('departamento'),
                TextEntry::make('provincia'),
                TextEntry::make('distrito'),
                TextEntry::make('capital')
                    ->placeholder('-'),
                TextEntry::make('region_natural')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Ubigeo $record): bool => $record->trashed()),
            ]);
    }
}
