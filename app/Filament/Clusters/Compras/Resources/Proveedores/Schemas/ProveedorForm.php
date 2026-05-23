<?php

namespace App\Filament\Clusters\Compras\Resources\Proveedores\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProveedorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nombre')
                    ->required()
                    ->maxLength(255)
                    ->label('Nombre comercial'),
                Select::make('tipo_documento')
                    ->label('Tipo de documento')
                    ->options([
                        'RUC' => 'RUC',
                        'DNI' => 'DNI',
                        'CE'  => 'Carné de Extranjería',
                        'OTRO' => 'Otro',
                    ])
                    ->default('RUC')
                    ->required(),
                TextInput::make('numero_documento')
                    ->label('N° de documento')
                    ->maxLength(20)
                    ->required(),
                TextInput::make('razon_social')
                    ->maxLength(255)
                    ->label('Razón social'),
                TextInput::make('direccion')
                    ->maxLength(255),
                TextInput::make('telefono')
                    ->tel()
                    ->maxLength(20),
                TextInput::make('email')
                    ->email()
                    ->maxLength(255),
                TextInput::make('contacto_principal')
                    ->maxLength(255)
                    ->label('Contacto principal'),
                TextInput::make('telefono_contacto')
                    ->tel()
                    ->maxLength(20)
                    ->label('Teléfono de contacto'),
                TextInput::make('rubro')
                    ->maxLength(255)
                    ->label('Rubro / Giro'),
                Textarea::make('observaciones')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Toggle::make('estado')
                    ->default(true),
            ]);
    }
}
