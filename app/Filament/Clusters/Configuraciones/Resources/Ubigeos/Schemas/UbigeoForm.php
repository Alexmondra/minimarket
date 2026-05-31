<?php

namespace App\Filament\Clusters\Configuraciones\Resources\Ubigeos\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UbigeoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('ubigeo')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(6),
                TextInput::make('Superficie')
                    ->maxLength(255)
                    ->default(null),
                TextInput::make('Y')
                    ->maxLength(255)
                    ->default(null),
                TextInput::make('x')
                    ->maxLength(255)
                    ->default(null),
                TextInput::make('departamento')
                    ->required()
                    ->maxLength(255),
                TextInput::make('provincia')
                    ->required()
                    ->maxLength(255),
                TextInput::make('distrito')
                    ->required()
                    ->maxLength(255),
                TextInput::make('capital')
                    ->maxLength(255)
                    ->default(null),
                TextInput::make('region_natural')
                    ->maxLength(255)
                    ->default(null),
            ]);
    }
}
