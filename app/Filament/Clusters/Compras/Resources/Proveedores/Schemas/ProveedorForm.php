<?php

namespace App\Filament\Clusters\Compras\Resources\Proveedores\Schemas;

use Filament\Forms\Components\Section;
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
                Section::make('Información General')
                    ->icon('heroicon-o-truck')
                    ->description('Datos básicos del proveedor')
                    ->columns(2)
                    ->schema([
                        TextInput::make('nombre')
                            ->required()
                            ->maxLength(255)
                            ->label('Nombre comercial'),
                        Select::make('tipo_documento')
                            ->label('Tipo de documento')
                            ->options([
                                'RUC' => 'RUC',
                                'DNI' => 'DNI',
                                'CE' => 'Carné de Extranjería',
                                'OTRO' => 'Otro',
                            ])
                            ->default('RUC')
                            ->required(),
                        TextInput::make('numero_documento')
                            ->label('N° de documento')
                            ->maxLength(20)
                            ->required(),
                    ]),

                Section::make('Datos Fiscales')
                    ->icon('heroicon-o-building-office')
                    ->description('Información tributaria del proveedor')
                    ->columns(2)
                    ->schema([
                        TextInput::make('razon_social')
                            ->maxLength(255)
                            ->label('Razón social'),
                        TextInput::make('rubro')
                            ->maxLength(255)
                            ->label('Rubro / Giro'),
                    ]),

                Section::make('Contacto')
                    ->icon('heroicon-o-phone')
                    ->description('Canales de comunicación')
                    ->columns(2)
                    ->schema([
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
                        TextInput::make('direccion')
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ]),

                Section::make('Notas y Estado')
                    ->icon('heroicon-o-document-text')
                    ->description('Información adicional y estado del registro')
                    ->columns(2)
                    ->schema([
                        Textarea::make('observaciones')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Toggle::make('estado')
                            ->default(true),
                    ]),
            ]);
    }
}
