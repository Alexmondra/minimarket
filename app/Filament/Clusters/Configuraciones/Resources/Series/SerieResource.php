<?php

namespace App\Filament\Clusters\Configuraciones\Resources\Series;

use App\Filament\Clusters\Configuraciones\Resources\Series\Pages\ListSeries;
use App\Filament\Clusters\Configuraciones\Resources\Series\Pages\SeleccionarSucursal;
use App\Models\Serie;
use App\Support\SucursalContext;
use BackedEnum;
use UnitEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class SerieResource extends Resource
{
    protected static ?string $model = Serie::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-receipt-percent';

    protected static string|UnitEnum|null $navigationGroup = 'Configuraciones';

    protected static ?string $navigationLabel = 'Series';

    protected static ?string $modelLabel = 'Serie';

    protected static ?string $pluralModelLabel = 'Series';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('sucursal_id')
                    ->options(fn (): array => app(SucursalContext::class)
                        ->sucursalesForWrite()
                        ->pluck('nombre_sucursal', 'id')
                        ->all())
                    ->default(fn (): ?int => app(SucursalContext::class)->resolveSucursalForWrite())
                    ->disabled(fn (): bool => app(SucursalContext::class)->activeSucursalId() !== null)
                    ->dehydrated()
                    ->required()
                    ->label('Sucursal')
                    ->native(false),
                Select::make('tipo_comprobante')
                    ->required()
                    ->options([
                        'BOLETA' => 'Boleta de Venta',
                        'FACTURA' => 'Factura',
                        'NOTA_CREDITO' => 'Nota de Crédito',
                        'NOTA_DEBITO' => 'Nota de Débito',
                        'TICKET' => 'Ticket',
                    ])
                    ->native(false),
                TextInput::make('serie')
                    ->required()
                    ->maxLength(10)
                    ->label('Serie'),
                TextInput::make('correlativo')
                    ->required()
                    ->numeric()
                    ->default(1)
                    ->label('Correlativo actual'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sucursal.nombre_sucursal')
                    ->label('Sucursal')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tipo_comprobante')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'BOLETA' => 'success',
                        'FACTURA' => 'info',
                        'NOTA_CREDITO' => 'warning',
                        'NOTA_DEBITO' => 'danger',
                        'TICKET' => 'gray',
                        default => 'gray',
                    })
                    ->label('Tipo'),
                TextColumn::make('serie')
                    ->searchable()
                    ->label('Serie'),
                TextColumn::make('correlativo')
                    ->numeric()
                    ->label('Correlativo'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Creado'),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('sucursal_id')
                    ->label('Sucursal')
                    ->options(fn (): array => app(SucursalContext::class)
                        ->sucursalesForWrite()
                        ->pluck('nombre_sucursal', 'id')
                        ->all()),
                TrashedFilter::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSeries::route('/'),
            'seleccionar' => SeleccionarSucursal::route('/seleccionar'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->whereHas('sucursal', function (Builder $query) {
                $query->where('empresa_id', Auth::user()->empresa_id);
            });

        return app(SucursalContext::class)->applyToQuery($query);
    }
}
