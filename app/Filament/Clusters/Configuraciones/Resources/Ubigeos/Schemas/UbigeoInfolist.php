<?php

namespace App\Filament\Clusters\Configuraciones\Resources\Ubigeos\Schemas;

use App\Models\Ubigeo;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UbigeoInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Ubicacion geografica')
                    ->icon('heroicon-o-map-pin')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('codigo')
                            ->badge()
                            ->color('primary'),
                        TextEntry::make('departamento')
                            ->weight('semibold'),
                        TextEntry::make('provincia'),
                        TextEntry::make('distrito')
                            ->weight('bold'),
                        TextEntry::make('capital')
                            ->placeholder('-'),
                        TextEntry::make('region_natural')
                            ->badge()
                            ->color('gray')
                            ->placeholder('-'),
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
                            ->visible(fn (Ubigeo $record): bool => $record->trashed()),
                    ]),
            ]);
    }
}
