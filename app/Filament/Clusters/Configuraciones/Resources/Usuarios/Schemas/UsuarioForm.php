<?php

namespace App\Filament\Clusters\Configuraciones\Resources\Usuarios\Schemas;

use App\Models\Empresa;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Password;

class UsuarioForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('empresa_id')
                    ->label('Empresa')
                    ->options(fn () => Empresa::query()->pluck('razon_social', 'id'))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->columnSpan(2),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Nombre'),
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('password')
                    ->password()
                    ->required(fn ($livewire) => $livewire instanceof CreateRecord)
                    ->rule(Password::min(8)->mixedCase()->numbers())
                    ->same('password_confirmation')
                    ->dehydrated(fn ($state) => filled($state))
                    ->label('Contraseña'),
                TextInput::make('password_confirmation')
                    ->password()
                    ->label('Confirmar contraseña')
                    ->dehydrated(false),
                TextInput::make('telefono')
                    ->tel()
                    ->maxLength(20)
                    ->default(null)
                    ->label('Teléfono'),
                Select::make('sucursales')
                    ->label('Sucursales')
                    ->multiple()
                    ->relationship('sucursales', 'nombre_sucursal')
                    ->searchable()
                    ->preload()
                    ->columnSpan(2),
                Select::make('roles')
                    ->label('Roles')
                    ->multiple()
                    ->relationship('roles', 'name')
                    ->searchable()
                    ->preload()
                    ->columnSpan(2),
                Toggle::make('activo')
                    ->default(true),
            ]);
    }
}
