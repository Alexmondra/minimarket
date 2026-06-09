<?php

namespace App\Filament\Clusters\Configuraciones\Resources\Usuarios\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Password;

class UsuarioForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Perfil del usuario')
                    ->description('Datos de acceso y contacto visibles para administracion.')
                    ->icon('heroicon-o-user-circle')
                    ->extraAttributes(['class' => 'mm-crud-card mm-crud-card-emerald'])
                    ->columns(3)
                    ->schema([
                        FileUpload::make('avatar')
                            ->label('Avatar')
                            ->image()
                            ->avatar()
                            ->imageEditor()
                            ->imageCropAspectRatio('1:1')
                            ->imageResizeMode('cover')
                            ->imageResizeTargetWidth('512')
                            ->imageResizeTargetHeight('512')
                            ->directory('users/avatars')
                            ->disk('public')
                            ->visibility('public')
                            ->maxSize(2048)
                            ->helperText('Max 2 MB, se redimensiona automaticamente.')
                            ->columnSpan(1),
                        Hidden::make('empresa_id')
                            ->default(fn (): ?int => auth()->user()?->empresa_id)
                            ->dehydrated(true),
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Nombre'),
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        TextInput::make('telefono')
                            ->tel()
                            ->maxLength(20)
                            ->default(null)
                            ->label('Telefono'),
                        Toggle::make('activo')
                            ->label('Usuario activo')
                            ->helperText('Controla si puede ingresar al sistema.')
                            ->default(true),
                    ]),
                Section::make('Seguridad y accesos')
                    ->description('Contrasena, roles y sucursales asignadas.')
                    ->icon('heroicon-o-shield-check')
                    ->extraAttributes(['class' => 'mm-crud-card mm-crud-card-violet'])
                    ->columns(2)
                    ->schema([
                        TextInput::make('password')
                            ->password()
                            ->required(fn ($livewire) => $livewire instanceof CreateRecord)
                            ->rule(Password::min(8)->mixedCase()->numbers())
                            ->same('password_confirmation')
                            ->dehydrated(fn ($state) => filled($state))
                            ->label('Contrasena'),
                        TextInput::make('password_confirmation')
                            ->password()
                            ->label('Confirmar contrasena')
                            ->dehydrated(false),
                        Select::make('sucursales')
                            ->label('Sucursales')
                            ->multiple()
                            ->relationship(
                                'sucursales',
                                'nombre_sucursal',
                                modifyQueryUsing: fn ($query) => $query->where('empresa_id', auth()->user()?->empresa_id)
                            )
                            ->searchable()
                            ->preload()
                            ->helperText('Solo se muestran sucursales de tu empresa.'),
                        Select::make('roles')
                            ->label('Roles')
                            ->multiple()
                            ->relationship('roles', 'name')
                            ->searchable()
                            ->preload(),
                    ]),
            ]);
    }
}
