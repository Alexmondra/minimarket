<?php

namespace App\Filament\Clusters\Configuraciones\Resources\Sucursales\Schemas;

use App\Models\Ubigeo;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SucursalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identidad de la sucursal')
                    ->description('Nombre, codigo interno e imagen que identifican el punto de venta.')
                    ->icon('heroicon-o-building-storefront')
                    ->columns(3)
                    ->schema([
                        TextInput::make('codigo')
                            ->label('Codigo')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50)
                            ->placeholder('SUC-001'),
                        TextInput::make('nombre_sucursal')
                            ->required()
                            ->maxLength(255)
                            ->label('Nombre comercial')
                            ->placeholder('Sucursal Centro')
                            ->columnSpan(2),
                        FileUpload::make('imagen_sucursal')
                            ->image()
                            ->directory('sucursales')
                            ->imagePreviewHeight('120')
                            ->default(null)
                            ->label('Imagen de referencia')
                            ->columnSpan(2),
                        Toggle::make('activo')
                            ->label('Sucursal activa')
                            ->helperText('Desactivar oculta la sucursal para operaciones nuevas.')
                            ->default(true),
                    ]),

                Section::make('Ubicacion y contacto')
                    ->description('Datos visibles para ventas, comprobantes y atencion al cliente.')
                    ->icon('heroicon-o-map-pin')
                    ->columns(2)
                    ->schema([
                        Select::make('ubigeo')
                            ->label('Ubigeo')
                            ->options(fn () => Ubigeo::query()
                                ->orderBy('departamento')
                                ->orderBy('provincia')
                                ->orderBy('distrito')
                                ->get()
                                ->mapWithKeys(fn ($item) => [
                                    $item->id => "{$item->departamento} / {$item->provincia} / {$item->distrito}",
                                ])
                            )
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('direccion')
                            ->label('Direccion')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Av. Principal 123'),
                        TextInput::make('telefono')
                            ->label('Telefono')
                            ->tel()
                            ->maxLength(20)
                            ->default(null)
                            ->placeholder('999 999 999'),
                        TextInput::make('email')
                            ->label('Correo')
                            ->email()
                            ->maxLength(255)
                            ->default(null)
                            ->placeholder('sucursal@empresa.com'),
                    ]),

                Section::make('Parametros de venta')
                    ->description('Configuracion fiscal usada al emitir comprobantes desde esta sucursal.')
                    ->icon('heroicon-o-calculator')
                    ->columns(2)
                    ->schema([
                        TextInput::make('impuesto_porcentaje')
                            ->numeric()
                            ->default(18)
                            ->step(0.01)
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->label('Impuesto aplicado'),
                    ]),
            ]);
    }
}
