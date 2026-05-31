<?php

namespace App\Filament\Clusters\Configuraciones\Resources\Sucursales\Schemas;

use App\Models\Ubigeo;
use App\Models\Sucursal;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;

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
                            ->default(function () {
                                $user = auth()->user();
                                if (!$user) {
                                    return '0000';
                                }
                                $existingCodes = Sucursal::withTrashed()
                                    ->where('empresa_id', $user->empresa_id)
                                    ->pluck('codigo')
                                    ->filter(fn($c) => preg_match('/^\d{4}$/', $c))
                                    ->map(fn($c) => (int)$c);

                                $nextCodeInt = $existingCodes->isEmpty() ? 0 : $existingCodes->max() + 1;
                                return sprintf('%04d', $nextCodeInt);
                            })
                            ->unique(
                                ignoreRecord: true,
                                modifyRuleUsing: fn (Unique $rule) => $rule
                                    ->where('empresa_id', auth()->user()?->empresa_id)
                                    ->whereNull('deleted_at'),
                            )
                            ->maxLength(50)
                            ->placeholder('0000'),
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
                                    $item->ubigeo => "{$item->departamento} / {$item->provincia} / {$item->distrito}",
                                ])
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $ubigeo = Ubigeo::where('ubigeo', $state)->first();
                                    if ($ubigeo) {
                                        $departamento = strtoupper(trim($ubigeo->departamento));
                                        $exempt = ['LORETO', 'MADRE DE DIOS', 'UCAYALI', 'SAN MARTIN', 'AMAZONAS'];
                                        if (in_array($departamento, $exempt)) {
                                            $set('impuesto_porcentaje', '0.00');
                                        } else {
                                            $set('impuesto_porcentaje', '18.00');
                                        }

                                        return;
                                    }
                                }
                                $set('impuesto_porcentaje', '18.00');
                            }),
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
                            ->label('Impuesto aplicado')
                            ->disabled()
                            ->dehydrated()
                            ->helperText('Se calcula automaticamente segun el ubigeo seleccionado.'),
                    ]),
            ]);
    }
}
