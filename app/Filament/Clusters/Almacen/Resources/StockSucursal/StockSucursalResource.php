<?php

namespace App\Filament\Clusters\Almacen\Resources\StockSucursal;

use App\Filament\Clusters\Almacen\Resources\StockSucursal\Pages\ListStockSucursal;
use App\Models\ProductoSucursal;
use App\Support\SucursalContext;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class StockSucursalResource extends Resource
{

    protected static ?string $model = ProductoSucursal::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-storefront';

    protected static string|UnitEnum|null $navigationGroup = 'Almacén';

    protected static ?string $navigationLabel = 'Stock x Sucursal';

    public static function getPages(): array
    {
        return [
            'index' => ListStockSucursal::route('/'),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('producto.nombre')
                    ->label('Producto')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('lotePresentacion.productoPresentacion.tipo_presentacion')
                    ->label('Presentación')
                    ->searchable(),
                TextColumn::make('lotePresentacion.lote.codigo_lote')
                    ->label('Lote')
                    ->searchable(),
                TextColumn::make('sucursal.nombre_sucursal')
                    ->label('Sucursal')
                    ->sortable(),
                TextColumn::make('stock')
                    ->label('Stock Actual')
                    ->numeric()
                    ->color(fn (int $state, $record): string => 
                        $state <= $record->stock_minimo ? 'danger' : 'success'
                    ),
                TextColumn::make('stock_minimo')
                    ->label('Stock Mínimo')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('precio')
                    ->label('Precio')
                    ->money('PEN')
                    ->sortable(),
                TextColumn::make('precio_mayorista')
                    ->label('Precio Mayorista')
                    ->money('PEN')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('activo')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sucursal.nombre_sucursal')
            ->filters([
                SelectFilter::make('sucursal_id')
                    ->label('Sucursal')
                    ->options(fn (): array => app(SucursalContext::class)
                        ->sucursalesForWrite()
                        ->pluck('nombre_sucursal', 'id')
                        ->all()),
                SelectFilter::make('activo')
                    ->label('Estado')
                    ->options([
                        '1' => 'Activo',
                        '0' => 'Inactivo',
                    ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with(['producto', 'sucursal', 'lotePresentacion.lote', 'lotePresentacion.productoPresentacion.unidadMedida'])
            ->whereHas('producto', function (Builder $query) {
                $query->where('empresa_id', Auth::user()->empresa_id);
            });

        return app(SucursalContext::class)->applyToQuery($query);
    }
}
