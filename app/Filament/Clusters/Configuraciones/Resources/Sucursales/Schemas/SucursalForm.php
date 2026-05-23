<?php

namespace App\Filament\Clusters\Configuraciones\Resources\Sucursales\Schemas;

use App\Models\Ubigeo;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SucursalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('codigo')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(50),
                TextInput::make('nombre_sucursal')
                    ->required()
                    ->maxLength(255)
                    ->label('Nombre'),
                Select::make('ubigeo')
                    ->label('Ubigeo')
                    ->options(fn () => Ubigeo::query()
                        ->orderBy('departamento')
                        ->orderBy('provincia')
                        ->orderBy('distrito')
                        ->get()
                        ->mapWithKeys(fn ($item) => [
                            $item->id => "{$item->departamento} / {$item->provincia} / {$item->distrito}"
                        ])
                    )
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('direccion')
                    ->required()
                    ->maxLength(255),
                TextInput::make('telefono')
                    ->tel()
                    ->maxLength(20)
                    ->default(null),
                TextInput::make('email')
                    ->email()
                    ->maxLength(255)
                    ->default(null),
                FileUpload::make('imagen_sucursal')
                    ->image()
                    ->directory('sucursales')
                    ->default(null)
                    ->label('Imagen'),
                TextInput::make('impuesto_porcentaje')
                    ->numeric()
                    ->default(0)
                    ->step(0.01)
                    ->suffix('%')
                    ->label('Impuesto %'),
                Toggle::make('activo')
                    ->default(true),
            ]);
    }
}
